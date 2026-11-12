<?php
// =====================================================
// toetsbouwer.php - Een woordenlijst maken of bewerken
// Nieuw:     toetsbouwer.php
// Bewerken:  toetsbouwer.php?id=4
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\WordListRepository;

$user = requireLogin();
$userId = (int) $user['id'];
$listRepository = new WordListRepository();

$listId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$errors = [];

// ---------- Startwaarden van het formulier ----------
if ($listId !== null) {
    // Bestaande lijst: alleen de eigenaar mag hem bewerken
    $list = $listRepository->findById($listId);
    if (!$list || (int) $list['user_id'] !== $userId) {
        setFlash(t('Deze lijst bestaat niet of is niet van jou.'), 'error');
        redirect('index.php');
    }

    $form = [
        'title'       => $list['title'],
        'description' => $list['description'] ?? '',
        'category'    => $list['category'] ?? '',
        'level'       => $list['level'] ?? '',
        'direction'   => $list['direction'],
        'is_public'   => (int) $list['is_public'],
    ];
    $words = $listRepository->getWords($listId);
} else {
    // Nieuwe lijst: gebruik de standaardinstellingen van de gebruiker
    $form = [
        'title'       => '',
        'description' => '',
        'category'    => '',
        'level'       => '',
        'direction'   => $user['default_direction'],
        'is_public'   => (int) $user['default_public'],
    ];
    $words = [];
}

// ---------- Opslaan ----------
if (isPost()) {
    checkCsrf();

    $form = [
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category'    => trim($_POST['category'] ?? ''),
        'level'       => $form['level'], // niveau kies je niet in de toetsbouwer, de oude waarde blijft staan
        'direction'   => ($_POST['direction'] ?? '') === 'en-nl' ? 'en-nl' : 'nl-en',
        'is_public'   => isset($_POST['is_public']) ? 1 : 0,
    ];

    $isPublish = ($_POST['action'] ?? '') === 'publish';

    // Woordparen uit het formulier halen. Lege rijen slaan we over.
    $words = [];
    $dutchWords = $_POST['dutch'] ?? [];
    $englishWords = $_POST['english'] ?? [];

    foreach ($dutchWords as $index => $dutch) {
        $dutch = trim($dutch);
        $english = trim($englishWords[$index] ?? '');

        if ($dutch === '' && $english === '') {
            continue; // helemaal lege rij
        }
        if ($dutch === '' || $english === '') {
            $errors[] = t('Vul bij elke rij zowel het Nederlandse woord als de Engelse vertaling in.');
        }

        $words[] = ['dutch' => $dutch, 'english' => $english];
    }

    if ($form['title'] === '') {
        $errors[] = t('Geef je toets een titel.');
    }
    if ($isPublish && count($words) === 0) {
        $errors[] = t('Voeg minimaal één woord toe om te publiceren.');
    }

    $errors = array_unique($errors);

    if (!$errors) {
        $data = $form;
        $data['description'] = $form['description'] !== '' ? $form['description'] : null;
        $data['category'] = $form['category'] !== '' ? $form['category'] : null;
        $data['level'] = $form['level'] !== '' ? $form['level'] : null;
        $data['is_published'] = $isPublish ? 1 : 0;

        if ($listId !== null) {
            $listRepository->update($listId, $data, $words);
        } else {
            $listId = $listRepository->create($userId, $data, $words);
        }

        if ($isPublish) {
            setFlash(t('Je woordenlijst is gepubliceerd!'));
            redirect('index.php');
        }

        setFlash(t('Concept opgeslagen.'));
        redirect('toetsbouwer.php?id=' . $listId);
    }
}

pageHeader(t('Toets bouwer'), 'builder');
?>
<main class="page">
  <?php showFlash(); ?>

  <?php if ($errors): ?>
    <div class="flash flash-error">
      <?php foreach ($errors as $error): ?>
        <div><?= e($error) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="builder-layout" id="builder-form">
    <?= csrfField() ?>

    <!-- ============ Linkerkolom: toetsgegevens ============ -->
    <div class="meta-column">
      <section class="card card-meta">
        <h2 class="card-title"><?= t('Toetsgegevens') ?></h2>

        <label class="field">
          <span class="field-label"><?= t('Titel van de Toets') ?></span>
          <input class="input" type="text" name="title" required maxlength="150"
                 value="<?= e($form['title']) ?>" placeholder="<?= t('bijv. Academische Kernwoorden') ?>">
        </label>

        <label class="field">
          <span class="field-label"><?= t('Categorie') ?></span>
          <input class="input" type="text" name="category" maxlength="50"
                 value="<?= e($form['category']) ?>" placeholder="<?= t('bijv. Alledaags') ?>">
        </label>

        <label class="field">
          <span class="field-label"><?= t('Omschrijving') ?></span>
          <textarea class="input" name="description" rows="3" maxlength="500"
                    placeholder="<?= t('Waar gaat deze lijst over?') ?>"><?= e($form['description']) ?></textarea>
        </label>

        <div class="field">
          <span class="field-label"><?= t('Taalrichting') ?></span>
          <div class="segmented">
            <label>
              <input type="radio" name="direction" value="nl-en" <?= $form['direction'] === 'nl-en' ? 'checked' : '' ?>>
              <span>NL → EN</span>
            </label>
            <label>
              <input type="radio" name="direction" value="en-nl" <?= $form['direction'] === 'en-nl' ? 'checked' : '' ?>>
              <span>EN → NL</span>
            </label>
          </div>
        </div>

        <div class="field">
          <span class="field-label"><?= t('Zichtbaarheid') ?></span>
          <label class="switch-row">
            <span class="switch-label"><?= icon('globe', 18) ?> <?= t('Openbaar maken') ?></span>
            <input type="checkbox" name="is_public" class="switch-input" <?= $form['is_public'] ? 'checked' : '' ?>>
            <span class="switch"></span>
          </label>
          <p class="help-text"><?= t('Indien openbaar, kunnen anderen je lijst zoeken en oefenen.') ?></p>
        </div>
      </section>
    </div>

    <!-- ============ Rechterkolom: woordenlijst ============ -->
    <div class="words-column">
      <section class="card">
        <div class="words-header">
          <h2 class="card-title">
            <?= t('Woordenlijst') ?> (<span id="word-count"><?= count($words) ?></span> <?= t('woorden') ?>)
          </h2>
          <button type="button" class="btn btn-soft btn-bordered" id="add-row">
            <?= icon('plus', 18) ?> <?= t('Rij Toevoegen') ?>
          </button>
        </div>

        <div class="word-rows" id="word-rows">
          <div class="word-row word-row-head">
            <span><?= t('Nederlands Woord / Zin') ?></span>
            <span><?= t('Engelse Vertaling') ?></span>
            <span></span>
          </div>

          <?php foreach ($words as $word): ?>
            <div class="word-row">
              <input class="input input-small" type="text" name="dutch[]" value="<?= e($word['dutch']) ?>" placeholder="<?= t('Nieuw Nederlands woord...') ?>">
              <input class="input input-small" type="text" name="english[]" value="<?= e($word['english']) ?>" placeholder="<?= t('Engelse vertaling...') ?>">
              <button type="button" class="icon-button icon-button-row remove-row" title="<?= t('Verwijderen') ?>"><?= icon('trash') ?></button>
            </div>
          <?php endforeach; ?>

          <!-- Lege rij (gestippeld) om snel een nieuw woord te typen -->
          <div class="word-row new-row">
            <input class="input input-small" type="text" name="dutch[]" placeholder="<?= t('Nieuw Nederlands woord...') ?>">
            <input class="input input-small" type="text" name="english[]" placeholder="<?= t('Engelse vertaling...') ?>">
            <button type="button" class="icon-button icon-button-row remove-row" disabled title="<?= t('Verwijderen') ?>"><?= icon('trash') ?></button>
          </div>
        </div>
      </section>

      <div class="builder-footer">
        <button class="btn btn-outline" type="submit" name="action" value="draft"><?= t('Concept Opslaan') ?></button>
        <button class="btn btn-primary" type="submit" name="action" value="publish"><?= t('Publiceren') ?></button>
      </div>
    </div>
  </form>
</main>
<?php pageFooter(); ?>
