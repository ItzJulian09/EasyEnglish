<?php
require 'includes/init.php';

if (!$user) {
    header('Location: login.php');
    exit;
}

$fout = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $openbaar = isset($_POST['openbaar']) ? 1 : 0;

    $userClass = new User();
    $andereUser = $userClass->getByEmail($email);

    if ($naam == '' || $email == '') {
        $fout = 'Vul je naam en e-mailadres in.';
    } elseif ($andereUser && $andereUser['id'] != $user['id']) {
        $fout = 'Dit e-mailadres is al in gebruik.';
    } else {
        $userClass->update($user['id'], $naam, $email, $openbaar);
        $_SESSION['melding'] = 'Je instellingen zijn opgeslagen.';
        header('Location: instellingen.php');
        exit;
    }
}

$titel = 'Instellingen';
$actief = '';
include 'includes/header.php';
?>

<main class="page">
    <div class="title-area">
        <h1>Instellingen</h1>
        <p>Beheer je accountgegevens en privacyvoorkeuren.</p>
    </div>

    <?php toonMelding(); ?>

    <?php if ($fout != '') { ?>
        <div class="flash flash-error"><?= $fout ?></div>
    <?php } ?>

    <form method="post" class="forms-column">
        <div class="card">
            <h2 class="card-title">Accountgegevens</h2>
            <div class="field-row">
                <label class="field">
                    <span class="field-label">Volledige Naam</span>
                    <input class="input input-small" type="text" name="naam" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </label>
                <label class="field">
                    <span class="field-label">E-mailadres</span>
                    <input class="input input-small" type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </label>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Voorkeuren</h2>
            <div class="field">
                <span class="field-label">Zichtbaarheid Nieuwe Toetsen</span>
                <label class="switch-row">
                    <span class="switch-label"><img src="assets/icons/globe.svg" alt=""> Nieuwe woordenlijsten standaard openbaar maken</span>
                    <input type="checkbox" name="openbaar" class="switch-input" <?= $user['default_public'] ? 'checked' : '' ?>>
                    <span class="switch"></span>
                </label>
            </div>
        </div>

        <div class="builder-footer">
            <a class="btn btn-outline" href="logout.php"><img src="assets/icons/trash-logout.svg" alt=""> Uitloggen</a>
            <button class="btn btn-primary" type="submit">Instellingen Opslaan</button>
        </div>
    </form>
</main>

<?php include 'includes/footer.php'; ?>
