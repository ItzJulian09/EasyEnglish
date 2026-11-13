<?php
require 'includes/init.php';

if (!$user) {
    header('Location: login.php');
    exit;
}

$wordList = new WordList();
$lijst = $wordList->getById($_GET['id']);

// Mag je deze lijst zien? Hij moet openbaar zijn, van jou zijn, of je bent beheerder.
$magZien = false;
if ($lijst) {
    if ($lijst['is_public'] == 1 && $lijst['is_published'] == 1) {
        $magZien = true;
    }
    if ($lijst['user_id'] == $user['id'] || $isAdmin) {
        $magZien = true;
    }
}

if (!$magZien) {
    header('Location: index.php');
    exit;
}

// Knop "Opslaan in mijn Bibliotheek"
if (isset($_POST['opslaan'])) {
    $wordList->copy($lijst['id'], $user['id']);
    $_SESSION['melding'] = 'De lijst staat nu bij jouw lijsten.';
    header('Location: index.php');
    exit;
}

// Knop "Toets verwijderen" (alleen beheerder)
if (isset($_POST['verwijderen']) && $isAdmin) {
    $wordList->deleteAsAdmin($lijst['id']);
    $_SESSION['melding'] = 'De toets is verwijderd.';
    header('Location: beheer.php');
    exit;
}

$woorden = $wordList->getWords($lijst['id']);

$titel = $lijst['title'];
$actief = 'lijsten';
include 'includes/header.php';
?>

<main class="page">
    <a class="breadcrumb" href="index.php#bibliotheek">
        <span class="back-btn"><img src="assets/icons/arrow-left.svg" alt=""></span>
        Terug naar Bibliotheek
    </a>

    <div class="detail-split">
        <!-- Links: informatie over de lijst -->
        <div class="meta-column">
            <div class="card card-dark-border">
                <div>
                    <h1 class="detail-title"><?= htmlspecialchars($lijst['title']) ?></h1>
                    <p>Gemaakt door <b><?= htmlspecialchars($lijst['full_name']) ?></b></p>
                </div>

                <?php if ($lijst['description']) { ?>
                    <p class="detail-description"><?= htmlspecialchars($lijst['description']) ?></p>
                <?php } ?>

                <div class="stats-row">
                    <strong><?= $lijst['word_count'] ?></strong> woorden
                </div>

                <a class="btn btn-dark" href="toets.php?id=<?= $lijst['id'] ?>&start=1">Start Toets (Typen)</a>

                <?php if ($lijst['user_id'] != $user['id']) { ?>
                    <form method="post">
                        <button class="btn btn-outline-dark" type="submit" name="opslaan">
                            <img src="assets/icons/plus-small.svg" alt=""> Opslaan in mijn Bibliotheek
                        </button>
                    </form>
                <?php } ?>

                <?php if ($isAdmin) { ?>
                    <form method="post" onsubmit="return confirm('Weet je zeker dat je deze toets wilt verwijderen?');">
                        <button class="btn btn-soft btn-block" type="submit" name="verwijderen">
                            <img src="assets/icons/trash.svg" alt=""> Toets verwijderen (beheerder)
                        </button>
                    </form>
                <?php } ?>
            </div>
        </div>

        <!-- Rechts: de woorden -->
        <div class="words-column">
            <div class="card card-dark-border">
                <h2 class="card-title">Woorden (<?= count($woorden) ?>)</h2>

                <table class="data-table">
                    <tr>
                        <th>Nederlands Woord / Uitdrukking</th>
                        <th>Engelse Vertaling</th>
                    </tr>
                    <?php foreach ($woorden as $woord) { ?>
                        <tr>
                            <td class="strong"><?= htmlspecialchars($woord['dutch']) ?></td>
                            <td><?= htmlspecialchars($woord['english']) ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
