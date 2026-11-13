<?php
require 'includes/init.php';

// Alleen voor de beheerder (is_admin = 1)
if (!$isAdmin) {
    header('Location: index.php');
    exit;
}

$wordList = new WordList();

// Op het prullenbakje geklikt
if (isset($_POST['verwijder_id'])) {
    $wordList->deleteAsAdmin($_POST['verwijder_id']);
    $_SESSION['melding'] = 'De toets is verwijderd.';
    header('Location: beheer.php');
    exit;
}

$zoek = '';
if (isset($_GET['zoek'])) {
    $zoek = trim($_GET['zoek']);
}
$lijsten = $wordList->getAllLists($zoek);

$titel = 'Beheer';
$actief = 'beheer';
include 'includes/header.php';
?>

<main class="page">
    <div class="title-area">
        <h1>Beheer</h1>
        <p>Alle toetsen van alle gebruikers. Als beheerder kun je elke toets verwijderen.</p>
    </div>

    <?php toonMelding(); ?>

    <form method="get" class="search-input">
        <img src="assets/icons/search.svg" alt="">
        <input type="search" name="zoek" value="<?= htmlspecialchars($zoek) ?>" placeholder="Zoek op toetsnaam of maker...">
    </form>

    <div class="card">
        <table class="data-table">
            <tr>
                <th>Naam Woordenlijst</th>
                <th>Gemaakt door</th>
                <th>Woorden</th>
                <th>Status</th>
                <th></th>
            </tr>
            <?php foreach ($lijsten as $lijst) { ?>
                <tr>
                    <td class="strong"><a href="lijst.php?id=<?= $lijst['id'] ?>"><?= htmlspecialchars($lijst['title']) ?></a></td>
                    <td><?= htmlspecialchars($lijst['full_name']) ?><br><small><?= htmlspecialchars($lijst['email']) ?></small></td>
                    <td><?= $lijst['word_count'] ?></td>
                    <td>
                        <span class="badge">
                            <?php
                            if ($lijst['is_published'] == 0) {
                                echo 'Concept';
                            } elseif ($lijst['is_public'] == 1) {
                                echo 'Openbaar';
                            } else {
                                echo 'Privé';
                            }
                            ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm('Weet je zeker dat je deze toets wilt verwijderen?');">
                            <input type="hidden" name="verwijder_id" value="<?= $lijst['id'] ?>">
                            <button class="icon-button" type="submit" title="Verwijderen">
                                <img src="assets/icons/trash.svg" alt="Verwijderen">
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
