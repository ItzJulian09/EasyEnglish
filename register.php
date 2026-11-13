<?php
require 'includes/init.php';

if ($user) {
    header('Location: index.php');
    exit;
}

$fout = '';
$naam = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $wachtwoord = $_POST['wachtwoord'];

    $userClass = new User();

    // Controleren of alles goed is ingevuld
    if ($naam == '' || $email == '') {
        $fout = 'Vul je naam en e-mailadres in.';
    } elseif (strlen($wachtwoord) < 8) {
        $fout = 'Je wachtwoord moet minimaal 8 tekens hebben.';
    } elseif (!isset($_POST['akkoord'])) {
        $fout = 'Je moet akkoord gaan met de voorwaarden.';
    } elseif ($userClass->getByEmail($email)) {
        $fout = 'Dit e-mailadres is al in gebruik.';
    } else {
        // Account aanmaken en meteen inloggen
        $_SESSION['user_id'] = $userClass->register($naam, $email, $wachtwoord);
        $_SESSION['melding'] = 'Welkom bij EasyEnglish! Maak je eerste woordenlijst aan.';
        header('Location: index.php');
        exit;
    }
}

$titel = 'Registreren';
$actief = '';
include 'includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card auth-card-wide">
        <div class="card-header-center">
            <h1>Account aanmaken</h1>
            <p>Begin direct met het bouwen van jouw Engelse woordenschat</p>
        </div>

        <?php if ($fout != '') { ?>
            <div class="flash flash-error"><?= $fout ?></div>
        <?php } ?>

        <form method="post" class="form-stack">
            <label class="field">
                <span class="field-label">Volledige naam</span>
                <input class="input" type="text" name="naam" value="<?= htmlspecialchars($naam) ?>" placeholder="bijv. Sven de Vries" required>
            </label>

            <label class="field">
                <span class="field-label">E-mailadres</span>
                <input class="input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="bijv. sven@voorbeeld.nl" required>
            </label>

            <label class="field">
                <span class="field-label">Wachtwoord</span>
                <input class="input" type="password" name="wachtwoord" placeholder="Minimaal 8 tekens" minlength="8" required>
            </label>

            <label class="checkbox-row">
                <input type="checkbox" name="akkoord" required>
                <span>Ik ga akkoord met de <b>Algemene Voorwaarden</b> en het <b>Privacybeleid</b> van EasyEnglish.</span>
            </label>

            <div class="auth-actions">
                <button class="btn btn-primary" type="submit">Account Aanmaken</button>
                <p class="auth-switch">Heb je al een account? <a href="login.php">Inloggen</a></p>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
