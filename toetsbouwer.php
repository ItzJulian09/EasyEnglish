<?php
require 'includes/init.php';

if (!$user) {
    header('Location: login.php');
    exit;
}

$wordList = new WordList();
$fout = '';

// Standaardwaarden voor een nieuwe lijst
$id = 0;
$lijstTitel = '';
$omschrijving = '';
$categorie = '';
$richting = 'nl-en';
$openbaar = $user['default_public'];
$woorden = [];

// Bestaande lijst bewerken? (toetsbouwer.php?id=3)
if (isset($_GET['id'])) {
    $lijst = $wordList->getById($_GET['id']);

    // Alleen je eigen lijst mag je bewerken
    if (!$lijst || $lijst['user_id'] != $user['id']) {
        header('Location: index.php');
        exit;
    }

    $id = $lijst['id'];
    $lijstTitel = $lijst['title'];
    $omschrijving = $lijst['description'];
    $categorie = $lijst['category'];
    $richting = $lijst['direction'];
    $openbaar = $lijst['is_public'];
    $woorden = $wordList->getWords($id);
}

// Formulier verstuurd: opslaan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lijstTitel = trim($_POST['titel']);
    $omschrijving = trim($_POST['omschrijving']);
    $categorie = trim($_POST['categorie']);
    $richting = $_POST['richting'];
    $openbaar = isset($_POST['openbaar']) ? 1 : 0;

    // Welke knop is ingedrukt? "Publiceren" of "Concept Opslaan"
    $gepubliceerd = ($_POST['knop'] == 'publiceren') ? 1 : 0;

    // De woorden uit het formulier halen, lege rijen overslaan
    $woorden = [];
    $nederlands = [];
    $engels = [];
    if (isset($_POST['nederlands'])) {
        $nederlands = $_POST['nederlands'];
        $engels = $_POST['engels'];
    }

    for ($i = 0; $i < count($nederlands); $i++) {
        $nl = trim($nederlands[$i]);
        $en = trim($engels[$i]);

        if ($nl == '' && $en == '') {
            continue;
        }
        if ($nl == '' || $en == '') {
            $fout = 'Vul bij elke rij zowel het Nederlandse als het Engelse woord in.';
        }
        $woorden[] = ['dutch' => $nl, 'english' => $en];
    }

    if ($lijstTitel == '') {
        $fout = 'Geef je toets een titel.';
    } elseif ($gepubliceerd == 1 && count($woorden) == 0) {
        $fout = 'Voeg minimaal één woord toe om te publiceren.';
    }

    if ($fout == '') {
        if ($id == 0) {
            $id = $wordList->create($user['id'], $lijstTitel, $omschrijving, $categorie, $richting, $openbaar, $gepubliceerd);
        } else {
            $wordList->update($id, $lijstTitel, $omschrijving, $categorie, $richting, $openbaar, $gepubliceerd);
        }
        $wordList->saveWords($id, $woorden);

        if ($gepubliceerd == 1) {
            $_SESSION['melding'] = 'Je woordenlijst is gepubliceerd!';
            header('Location: index.php');
        } else {
            $_SESSION['melding'] = 'Concept opgeslagen.';
            header('Location: toetsbouwer.php?id=' . $id);
        }
        exit;
    }
}

$titel = 'Toets bouwer';
$actief = 'bouwer';
include 'includes/header.php';
?>

<main class="page">
    <?php toonMelding(); ?>

    <?php if ($fout != '') { ?>
        <div class="flash flash-error"><?= $fout ?></div>
    <?php } ?>

    <form method="post" class="builder-layout">
        <!-- Links: gegevens van de toets -->
        <div class="meta-column">
            <div class="card">
                <h2 class="card-title">Toetsgegevens</h2>

                <label class="field">
                    <span class="field-label">Titel van de Toets</span>
                    <input class="input" type="text" name="titel" value="<?= htmlspecialchars($lijstTitel) ?>" placeholder="bijv. Academische Kernwoorden" required>
                </label>

                <label class="field">
                    <span class="field-label">Categorie</span>
                    <input class="input" type="text" name="categorie" value="<?= htmlspecialchars($categorie) ?>" placeholder="bijv. Alledaags">
                </label>

                <label class="field">
                    <span class="field-label">Omschrijving</span>
                    <textarea class="input" name="omschrijving" rows="3" placeholder="Waar gaat deze lijst over?"><?= htmlspecialchars($omschrijving) ?></textarea>
                </label>

                <div class="field">
                    <span class="field-label">Taalrichting</span>
                    <div class="segmented">
                        <label>
                            <input type="radio" name="richting" value="nl-en" <?= $richting == 'nl-en' ? 'checked' : '' ?>>
                            <span>NL → EN</span>
                        </label>
                        <label>
                            <input type="radio" name="richting" value="en-nl" <?= $richting == 'en-nl' ? 'checked' : '' ?>>
                            <span>EN → NL</span>
                        </label>
                    </div>
                </div>

                <div class="field">
                    <span class="field-label">Zichtbaarheid</span>
                    <label class="switch-row">
                        <span class="switch-label"><img src="assets/icons/globe.svg" alt=""> Openbaar maken</span>
                        <input type="checkbox" name="openbaar" class="switch-input" <?= $openbaar ? 'checked' : '' ?>>
                        <span class="switch"></span>
                    </label>
                    <p class="help-text">Indien openbaar, kunnen anderen je lijst zoeken en oefenen.</p>
                </div>
            </div>
        </div>

        <!-- Rechts: de woorden -->
        <div class="words-column">
            <div class="card">
                <div class="words-header">
                    <h2 class="card-title">Woordenlijst</h2>
                    <button type="button" class="btn btn-soft" onclick="rijToevoegen()">
                        <img src="assets/icons/plus.svg" alt=""> Rij Toevoegen
                    </button>
                </div>

                <div class="word-row word-row-head">
                    <span>Nederlands Woord / Zin</span>
                    <span>Engelse Vertaling</span>
                    <span></span>
                </div>

                <div id="rijen" class="word-rows">
                    <?php foreach ($woorden as $woord) { ?>
                        <div class="word-row">
                            <input class="input input-small" type="text" name="nederlands[]" value="<?= htmlspecialchars($woord['dutch']) ?>">
                            <input class="input input-small" type="text" name="engels[]" value="<?= htmlspecialchars($woord['english']) ?>">
                            <button type="button" class="icon-button icon-button-row" onclick="rijVerwijderen(this)">
                                <img src="assets/icons/trash.svg" alt="Verwijderen">
                            </button>
                        </div>
                    <?php } ?>

                    <!-- Altijd één lege rij om een nieuw woord te typen -->
                    <div class="word-row">
                        <input class="input input-small" type="text" name="nederlands[]" placeholder="Nieuw Nederlands woord...">
                        <input class="input input-small" type="text" name="engels[]" placeholder="Engelse vertaling...">
                        <button type="button" class="icon-button icon-button-row" onclick="rijVerwijderen(this)">
                            <img src="assets/icons/trash.svg" alt="Verwijderen">
                        </button>
                    </div>
                </div>
            </div>

            <div class="builder-footer">
                <button class="btn btn-outline" type="submit" name="knop" value="concept">Concept Opslaan</button>
                <button class="btn btn-primary" type="submit" name="knop" value="publiceren">Publiceren</button>
            </div>
        </div>
    </form>
</main>

<script>
    // Nieuwe lege rij onderaan toevoegen
    function rijToevoegen() {
        let rij = document.createElement('div');
        rij.className = 'word-row';
        rij.innerHTML =
            '<input class="input input-small" type="text" name="nederlands[]" placeholder="Nieuw Nederlands woord...">' +
            '<input class="input input-small" type="text" name="engels[]" placeholder="Engelse vertaling...">' +
            '<button type="button" class="icon-button icon-button-row" onclick="rijVerwijderen(this)">' +
            '<img src="assets/icons/trash.svg" alt="Verwijderen"></button>';
        document.getElementById('rijen').appendChild(rij);
    }

    // De rij weghalen waar het prullenbakje in staat
    function rijVerwijderen(knop) {
        knop.parentElement.remove();
    }
</script>

<?php include 'includes/footer.php'; ?>
