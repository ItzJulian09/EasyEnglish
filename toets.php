<?php
// =====================================================
// toets.php - De typ-toets
//
// Hoe werkt het?
// 1. toets.php?id=4&start=1  -> de woorden worden geschud en in de sessie gezet
// 2. toets.php?id=4          -> toont het huidige woord
// 3. Het formulier stuurt een POST met action = check, skip of next
// 4. Na het laatste woord wordt de score opgeslagen in de tabel "attempts"
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\AttemptRepository;
use App\WordListRepository;

$user = requireLogin();
$userId = (int) $user['id'];
$listRepository = new WordListRepository();

$listId = (int) ($_GET['id'] ?? 0);
$list = $listRepository->findById($listId);

// Mag deze gebruiker de lijst oefenen? (eigen lijst of openbare lijst)
$isOwner = $list && (int) $list['user_id'] === $userId;
$canPractice = $list && ($isOwner || ($list['is_public'] && $list['is_published']));

if (!$canPractice) {
    setFlash(t('Deze lijst bestaat niet of is niet openbaar.'), 'error');
    redirect('index.php');
}

/**
 * Maakt een antwoord "schoon" om te vergelijken:
 * hoofdletters, spaties en een punt aan het eind maken niet uit.
 */
function normalizeAnswer(string $answer): string
{
    $answer = mb_strtolower(trim($answer));
    $answer = preg_replace('/\s+/', ' ', $answer);   // dubbele spaties weg
    $answer = rtrim($answer, '.!?');                 // leesteken aan het eind weg

    return $answer;
}

/**
 * Is het antwoord goed?
 * Bij "To influence / To affect" zijn "to influence", "influence", "to affect" en "affect" allemaal goed.
 */
function isCorrectAnswer(string $given, string $expected): bool
{
    $given = normalizeAnswer($given);
    if ($given === '') {
        return false;
    }

    $options = explode('/', $expected);
    foreach ($options as $option) {
        $option = normalizeAnswer($option);
        $withoutTo = preg_replace('/^to /', '', $option);
        $givenWithoutTo = preg_replace('/^to /', '', $given);

        if ($given === $option || $givenWithoutTo === $withoutTo) {
            return true;
        }
    }

    return false;
}

// ---------- 1. Een nieuwe toets starten ----------
if (isset($_GET['start'])) {
    $words = $listRepository->getWords($listId);
    if (!$words) {
        setFlash(t('Deze lijst heeft nog geen woorden.'), 'error');
        redirect('index.php');
    }

    shuffle($words); // willekeurige volgorde

    $questions = [];
    foreach ($words as $word) {
        if ($list['direction'] === 'en-nl') {
            $questions[] = ['question' => $word['english'], 'answer' => $word['dutch']];
        } else {
            $questions[] = ['question' => $word['dutch'], 'answer' => $word['english']];
        }
    }

    $_SESSION['test'] = [
        'list_id'   => $listId,
        'questions' => $questions,
        'index'     => 0,      // welk woord zijn we nu?
        'correct'   => 0,      // aantal goed
        'mistakes'  => [],     // fouten om aan het eind te laten zien
        'feedback'  => null,   // feedback op het huidige woord
        'finished'  => false,
        'score'     => null,
    ];

    redirect('toets.php?id=' . $listId);
}

// Geen toets in de sessie (of een andere lijst)? Dan opnieuw starten.
if (!isset($_SESSION['test']) || $_SESSION['test']['list_id'] !== $listId) {
    redirect('toets.php?id=' . $listId . '&start=1');
}

$test = &$_SESSION['test']; // met & passen we de sessie direct aan

// ---------- 2. Antwoord verwerken ----------
if (isPost() && !$test['finished']) {
    checkCsrf();
    $action = $_POST['action'] ?? '';
    $current = $test['questions'][$test['index']];

    if ($action === 'check' && $test['feedback'] === null) {
        $given = trim($_POST['answer'] ?? '');
        $correct = isCorrectAnswer($given, $current['answer']);

        if ($correct) {
            $test['correct']++;
        } else {
            $test['mistakes'][] = ['question' => $current['question'], 'given' => $given, 'answer' => $current['answer']];
        }

        $test['feedback'] = ['type' => $correct ? 'correct' : 'wrong', 'given' => $given];
    }

    if ($action === 'skip' && $test['feedback'] === null) {
        $test['mistakes'][] = ['question' => $current['question'], 'given' => '', 'answer' => $current['answer']];
        $test['feedback'] = ['type' => 'skipped', 'given' => ''];
    }

    if ($action === 'next' && $test['feedback'] !== null) {
        $test['feedback'] = null;
        $test['index']++;

        // Was dit het laatste woord? Dan is de toets klaar.
        if ($test['index'] >= count($test['questions'])) {
            $attemptRepository = new AttemptRepository();
            $test['score'] = $attemptRepository->create($userId, $listId, $test['correct'], count($test['questions']));
            $test['finished'] = true;
        }
    }

    // Post/Redirect/Get: voorkomt dubbel versturen bij verversen
    redirect('toets.php?id=' . $listId);
}

$total = count($test['questions']);
$current = $test['finished'] ? null : $test['questions'][$test['index']];
$feedback = $test['feedback'];
$targetLanguage = $list['direction'] === 'en-nl' ? t('Vertaal naar het Nederlands') : t('Vertaal naar het Engels');

pageHeader(t('Typ Toets') . ': ' . $list['title'], 'lists');
?>
<main class="focus-content">

  <?php if ($test['finished']): ?>
    <!-- ============ Resultaat ============ -->
    <section class="typing-card result-card">
      <div class="term-display">
        <p class="term-label"><?= t('Toets afgerond') ?> · <?= e($list['title']) ?></p>
        <p class="result-score"><?= (int) $test['score'] ?>%</p>
        <p class="result-text"><?= t('Je had %d van de %d woorden goed.', $test['correct'], $total) ?></p>
      </div>

      <?php if ($test['mistakes']): ?>
        <table class="data-table">
          <thead>
            <tr>
              <th><?= t('Woord') ?></th>
              <th><?= t('Jouw antwoord') ?></th>
              <th><?= t('Juiste antwoord') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($test['mistakes'] as $mistake): ?>
              <tr>
                <td class="strong"><?= e($mistake['question']) ?></td>
                <td><?= $mistake['given'] !== '' ? e($mistake['given']) : '<i>' . t('overgeslagen') . '</i>' ?></td>
                <td class="strong"><?= e($mistake['answer']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="result-text"><?= t('Alles goed, top gedaan!') ?></p>
      <?php endif; ?>

      <div class="control-row">
        <a class="btn btn-soft btn-bordered" href="index.php"><?= t('Naar mijn lijsten') ?></a>
        <a class="btn btn-primary" href="toets.php?id=<?= $listId ?>&amp;start=1"><?= t('Opnieuw oefenen') ?></a>
      </div>
    </section>

  <?php else: ?>
    <!-- ============ Huidig woord ============ -->
    <section class="typing-card">
      <div class="progress-row">
        <span><?= e($list['title']) ?></span>
        <span><?= t('Woord %d van %d', $test['index'] + 1, $total) ?></span>
      </div>
      <div class="progress-bar"><span style="width: <?= round($test['index'] / $total * 100) ?>%"></span></div>

      <div class="term-display">
        <p class="term-label"><?= $targetLanguage ?></p>
        <p class="term"><?= e($current['question']) ?></p>
      </div>

      <form method="post" class="typing-form">
        <?= csrfField() ?>
        <input class="answer-input" type="text" name="answer" autocomplete="off" autocapitalize="off" spellcheck="false"
               value="<?= e($feedback['given'] ?? '') ?>"
               placeholder="<?= t('Typ hier je antwoord...') ?>"
               <?= $feedback ? 'readonly' : 'autofocus required' ?>>

        <?php if (!$feedback): ?>
          <div class="control-row">
            <button class="btn btn-soft btn-bordered" type="submit" name="action" value="skip" formnovalidate><?= t('Overslaan') ?></button>
            <button class="btn btn-primary" type="submit" name="action" value="check"><?= t('Controleer Antwoord') ?></button>
          </div>
        <?php endif; ?>
      </form>
    </section>

    <?php if ($feedback): ?>
      <!-- ============ Feedback ============ -->
      <form method="post" class="feedback-toast feedback-<?= e($feedback['type']) ?>">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="next">

        <span class="icon-indicator">
          <?= $feedback['type'] === 'correct' ? icon('check', 14) : icon('alert-circle', 18) ?>
        </span>

        <div class="feedback-text">
          <?php if ($feedback['type'] === 'correct'): ?>
            <p class="feedback-title"><?= t('Goed zo!') ?></p>
            <p><?= t('Jouw antwoord') ?>: <b><?= e($feedback['given']) ?></b></p>
          <?php elseif ($feedback['type'] === 'wrong'): ?>
            <p class="feedback-title"><?= t('Oeps! Net niet helemaal goed.') ?></p>
            <p><?= t('Jouw antwoord') ?>: <b><?= $feedback['given'] !== '' ? e($feedback['given']) : '-' ?></b> - <?= t('Juiste spelling is') ?>: <b><?= e($current['answer']) ?></b></p>
          <?php else: ?>
            <p class="feedback-title"><?= t('Overgeslagen') ?></p>
            <p><?= t('Het juiste antwoord is') ?>: <b><?= e($current['answer']) ?></b></p>
          <?php endif; ?>
        </div>

        <button class="btn btn-primary" type="submit" autofocus>
          <?= $test['index'] + 1 === $total ? t('Bekijk resultaat') : t('Volgende') ?>
        </button>
      </form>
    <?php endif; ?>
  <?php endif; ?>

</main>
<?php pageFooter(); ?>
