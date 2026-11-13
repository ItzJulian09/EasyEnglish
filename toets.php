<?php
require 'includes/init.php';

if (!$user) {
    header('Location: login.php');
    exit;
}

$wordList = new WordList();
$lijst = $wordList->getById($_GET['id']);

// Je mag een openbare lijst of je eigen lijst oefenen
if (!$lijst || ($lijst['is_public'] == 0 && $lijst['user_id'] != $user['id'])) {
    header('Location: index.php');
    exit;
}

// 1. Nieuwe toets starten: woorden husselen en in de sessie zetten
if (isset($_GET['start'])) {
    $woorden = $wordList->getWords($lijst['id']);
    if (count($woorden) == 0) {
        $_SESSION['melding'] = 'Deze lijst heeft nog geen woorden.';
        header('Location: index.php');
        exit;
    }
    shuffle($woorden);

    $vragen = [];
    foreach ($woorden as $woord) {
        if ($lijst['direction'] == 'en-nl') {
            $vragen[] = ['vraag' => $woord['english'], 'antwoord' => $woord['dutch']];
        } else {
            $vragen[] = ['vraag' => $woord['dutch'], 'antwoord' => $woord['english']];
        }
    }

    $_SESSION['toets'] = [
        'lijst_id' => $lijst['id'],
        'vragen'   => $vragen,
        'nummer'   => 0,      // bij welke vraag zijn we
        'goed'     => 0,      // aantal goede antwoorden
        'fouten'   => [],     // om aan het eind te laten zien
        'feedback' => null,   // goed/fout van de huidige vraag
        'score'    => null,   // wordt ingevuld als de toets klaar is
    ];

    header('Location: toets.php?id=' . $lijst['id']);
    exit;
}

// Geen toets bezig voor deze lijst? Dan opnieuw starten.
if (!isset($_SESSION['toets']) || $_SESSION['toets']['lijst_id'] != $lijst['id']) {
    header('Location: toets.php?id=' . $lijst['id'] . '&start=1');
    exit;
}

$toets = $_SESSION['toets'];

// 2. Een knop is ingedrukt
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $huidige = $toets['vragen'][$toets['nummer']];

    // "Controleer Antwoord": hoofdletters en spaties maken niet uit
    if (isset($_POST['controleer'])) {
        $gegeven = trim($_POST['antwoord']);

        if (strtolower($gegeven) == strtolower(trim($huidige['antwoord']))) {
            $toets['goed']++;
            $toets['feedback'] = ['soort' => 'goed', 'gegeven' => $gegeven];
        } else {
            $toets['fouten'][] = ['vraag' => $huidige['vraag'], 'gegeven' => $gegeven, 'antwoord' => $huidige['antwoord']];
            $toets['feedback'] = ['soort' => 'fout', 'gegeven' => $gegeven];
        }
    }

    // "Overslaan": telt als fout
    if (isset($_POST['overslaan'])) {
        $toets['fouten'][] = ['vraag' => $huidige['vraag'], 'gegeven' => '-', 'antwoord' => $huidige['antwoord']];
        $toets['feedback'] = ['soort' => 'overgeslagen', 'gegeven' => ''];
    }

    // "Volgende": naar de volgende vraag
    if (isset($_POST['volgende'])) {
        $toets['nummer']++;
        $toets['feedback'] = null;

        // Laatste vraag gehad? Dan de score opslaan.
        if ($toets['nummer'] == count($toets['vragen'])) {
            $attempt = new Attempt();
            $toets['score'] = $attempt->save($user['id'], $lijst['id'], $toets['goed'], count($toets['vragen']));
        }
    }

    $_SESSION['toets'] = $toets;
    header('Location: toets.php?id=' . $lijst['id']);
    exit;
}

$aantal = count($toets['vragen']);
$klaar = $toets['score'] !== null;

$titel = 'Typ Toets';
$actief = 'lijsten';
include 'includes/header.php';
?>

<main class="focus-content">

<?php if ($klaar) { ?>
    <!-- Toets is klaar: score laten zien -->
    <div class="typing-card">
        <div class="term-display">
            <p class="term-label">Toets afgerond - <?= htmlspecialchars($lijst['title']) ?></p>
            <p class="result-score"><?= $toets['score'] ?>%</p>
            <p>Je had <?= $toets['goed'] ?> van de <?= $aantal ?> woorden goed.</p>
        </div>

        <?php if (count($toets['fouten']) > 0) { ?>
            <table class="data-table">
                <tr>
                    <th>Woord</th>
                    <th>Jouw antwoord</th>
                    <th>Goede antwoord</th>
                </tr>
                <?php foreach ($toets['fouten'] as $fout) { ?>
                    <tr>
                        <td class="strong"><?= htmlspecialchars($fout['vraag']) ?></td>
                        <td><?= htmlspecialchars($fout['gegeven']) ?></td>
                        <td class="strong"><?= htmlspecialchars($fout['antwoord']) ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>

        <div class="control-row">
            <a class="btn btn-soft" href="index.php">Naar mijn lijsten</a>
            <a class="btn btn-primary" href="toets.php?id=<?= $lijst['id'] ?>&start=1">Opnieuw oefenen</a>
        </div>
    </div>

<?php } else { ?>
    <?php $huidige = $toets['vragen'][$toets['nummer']]; ?>

    <!-- De huidige vraag -->
    <div class="typing-card">
        <p class="progress-text">Woord <?= $toets['nummer'] + 1 ?> van <?= $aantal ?></p>

        <div class="term-display">
            <p class="term-label">
                <?= $lijst['direction'] == 'en-nl' ? 'Vertaal naar het Nederlands' : 'Vertaal naar het Engels' ?>
            </p>
            <p class="term"><?= htmlspecialchars($huidige['vraag']) ?></p>
        </div>

        <?php if ($toets['feedback'] == null) { ?>
            <form method="post" class="typing-form">
                <input class="answer-input" type="text" name="antwoord" autocomplete="off" placeholder="Typ hier je antwoord..." autofocus>
                <div class="control-row">
                    <button class="btn btn-soft" type="submit" name="overslaan">Overslaan</button>
                    <button class="btn btn-primary" type="submit" name="controleer">Controleer Antwoord</button>
                </div>
            </form>
        <?php } else { ?>
            <input class="answer-input" type="text" value="<?= htmlspecialchars($toets['feedback']['gegeven']) ?>" readonly>
        <?php } ?>
    </div>

    <?php if ($toets['feedback'] != null) { ?>
        <!-- Was het goed of fout? -->
        <form method="post" class="feedback-toast">
            <?php if ($toets['feedback']['soort'] == 'goed') { ?>
                <span class="icon-indicator icon-goed"><img src="assets/icons/check.svg" alt=""></span>
                <div class="feedback-text">
                    <p class="feedback-title">Goed zo!</p>
                    <p>Jouw antwoord: <b><?= htmlspecialchars($toets['feedback']['gegeven']) ?></b></p>
                </div>
            <?php } elseif ($toets['feedback']['soort'] == 'fout') { ?>
                <span class="icon-indicator"><img src="assets/icons/alert-circle.svg" alt=""></span>
                <div class="feedback-text">
                    <p class="feedback-title">Oeps! Net niet helemaal goed.</p>
                    <p>Jouw antwoord: <b><?= htmlspecialchars($toets['feedback']['gegeven']) ?></b> - Juiste spelling is: <b><?= htmlspecialchars($huidige['antwoord']) ?></b></p>
                </div>
            <?php } else { ?>
                <span class="icon-indicator"><img src="assets/icons/alert-circle.svg" alt=""></span>
                <div class="feedback-text">
                    <p class="feedback-title">Overgeslagen</p>
                    <p>Het goede antwoord is: <b><?= htmlspecialchars($huidige['antwoord']) ?></b></p>
                </div>
            <?php } ?>

            <button class="btn btn-primary" type="submit" name="volgende" autofocus>Volgende</button>
        </form>
    <?php } ?>
<?php } ?>

</main>

<?php include 'includes/footer.php'; ?>
