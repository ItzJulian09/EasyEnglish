<?php
require 'includes/init.php';

// Niet ingelogd? Dan naar de inlogpagina
if (!$user) {
    header('Location: login.php');
    exit;
}

$wordList = new WordList();

// Op het prullenbakje geklikt: lijst verwijderen
if (isset($_POST['verwijder_id'])) {
    $wordList->delete($_POST['verwijder_id'], $user['id']);
    $_SESSION['melding'] = 'De woordenlijst is verwijderd.';
    header('Location: index.php');
    exit;
}

// Zoeken en sorteren in de bibliotheek
$zoek = '';
if (isset($_GET['zoek'])) {
    $zoek = trim($_GET['zoek']);
}
$sorteer = 'nieuwste';
if (isset($_GET['sorteer'])) {
    $sorteer = $_GET['sorteer'];
}

$mijnLijsten = $wordList->getMyLists($user['id']);
$openbareLijsten = $wordList->getPublicLists($zoek, $sorteer);

$titel = 'Mijn lijsten';
$actief = 'lijsten';
include 'includes/header.php';
?>

<main class="page page-home">
    <?php toonMelding(); ?>

    <!-- Mijn lijsten -->
    <section class="section-my-lists">
        <div class="section-header">
            <p class="intro-text">Oefen je opgeslagen woordenlijsten of maak een nieuwe aan om te starten.</p>
            <a class="btn btn-primary" href="toetsbouwer.php">
                <img src="assets/icons/plus-white.svg" alt=""> Nieuwe Lijst Toevoegen
            </a>
        </div>

        <?php if (count($mijnLijsten) == 0) { ?>
            <div class="empty-state">Je hebt nog geen woordenlijsten. Maak er een aan of sla een lijst op uit de bibliotheek.</div>
        <?php } ?>

        <div class="lists-grid">
            <?php foreach ($mijnLijsten as $lijst) { ?>
                <div class="list-card">
                    <div class="card-top">
                        <div class="card-title-group">
                            <h2><?= htmlspecialchars($lijst['title']) ?></h2>
                            <p><?= $lijst['word_count'] ?> Engelse woorden</p>
                        </div>
                        <div class="crud-actions">
                            <a class="icon-button" href="toetsbouwer.php?id=<?= $lijst['id'] ?>" title="Bewerken">
                                <img src="assets/icons/pencil.svg" alt="Bewerken">
                            </a>
                            <form method="post" onsubmit="return confirm('Weet je zeker dat je deze lijst wilt verwijderen?');">
                                <input type="hidden" name="verwijder_id" value="<?= $lijst['id'] ?>">
                                <button class="icon-button" type="submit" title="Verwijderen">
                                    <img src="assets/icons/trash.svg" alt="Verwijderen">
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card-middle">
                        <span class="badge">
                            <?php
                            if ($lijst['is_published'] == 0) {
                                echo 'Concept';
                            } elseif ($lijst['last_score'] !== null) {
                                echo 'Score: ' . $lijst['last_score'] . '%';
                            } else {
                                echo 'Score: Nog geen score';
                            }
                            ?>
                        </span>
                        <span class="meta-text">
                            Laatst geoefend:
                            <?= $lijst['last_date'] ? date('d-m-Y', strtotime($lijst['last_date'])) : 'Nieuw' ?>
                        </span>
                    </div>

                    <div>
                        <?php if ($lijst['word_count'] > 0) { ?>
                            <a class="btn btn-soft" href="toets.php?id=<?= $lijst['id'] ?>&start=1">Start Typ Toets</a>
                        <?php } else { ?>
                            <a class="btn btn-soft" href="toetsbouwer.php?id=<?= $lijst['id'] ?>">Woorden toevoegen</a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>

    <!-- Openbare bibliotheek -->
    <section class="section-public" id="bibliotheek">
        <div>
            <h2 class="section-title">Openbare Bibliotheek</h2>
            <p>Ontdek gratis lijsten gedeeld door taaldocenten en de EasyEnglish-community.</p>
        </div>

        <form method="get" action="index.php#bibliotheek" class="search-filters">
            <label class="search-input">
                <img src="assets/icons/search.svg" alt="">
                <input type="search" name="zoek" value="<?= htmlspecialchars($zoek) ?>" placeholder="Zoek op titel, categorie of maker...">
            </label>

            <label class="filter-pill">
                <select name="sorteer" onchange="this.form.submit()">
                    <option value="nieuwste" <?= $sorteer == 'nieuwste' ? 'selected' : '' ?>>Sorteer: Nieuwste</option>
                    <option value="naam" <?= $sorteer == 'naam' ? 'selected' : '' ?>>Sorteer: Naam (A-Z)</option>
                </select>
                <img src="assets/icons/chevron-down.svg" alt="">
            </label>
        </form>

        <?php if (count($openbareLijsten) == 0) { ?>
            <div class="empty-state">
                Geen lijsten gevonden. Let op: alleen lijsten die openbaar en gepubliceerd zijn staan in de bibliotheek.
            </div>
        <?php } ?>

        <div class="public-grid">
            <?php foreach ($openbareLijsten as $lijst) { ?>
                <a class="public-card" href="lijst.php?id=<?= $lijst['id'] ?>">
                    <div class="card-head">
                        <?php if ($lijst['category']) { ?>
                            <div><span class="tag"><?= htmlspecialchars($lijst['category']) ?></span></div>
                        <?php } ?>
                        <h3><?= htmlspecialchars($lijst['title']) ?></h3>
                        <p>Door: <?= $lijst['user_id'] == $user['id'] ? 'Jij' : htmlspecialchars($lijst['full_name']) ?></p>
                    </div>
                    <div class="card-foot"><?= $lijst['word_count'] ?> woorden</div>
                </a>
            <?php } ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
