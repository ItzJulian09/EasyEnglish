<?php
require 'includes/init.php';


if ($user) {
    header('Location: index.php');
    exit;
}

$fout = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $wachtwoord = $_POST['wachtwoord'];

    $userClass = new User();
    $gevonden = $userClass->login($email, $wachtwoord);

    if ($gevonden) {
        $_SESSION['user_id'] = $gevonden['id'];
        header('Location: index.php');
        exit;
    } else {
        $fout = 'E-mailadres of wachtwoord is niet juist.';
    }
}

$titel = 'Inloggen';
$actief = '';
include 'includes/header.php';
?>

<main class="auth-page">
    <div class="auth-card">
        <div class="card-header-center">
            <h1>Inloggen bij EasyEnglish</h1>
            <p>Leer Engels in je eigen tempo</p>
        </div>

        <?php if ($fout != '') { ?>
            <div class="flash flash-error"><?= $fout ?></div>
        <?php } ?>

        <form method="post" class="form-stack">
            <label class="field">
                <span class="field-label">E-mailadres</span>
                <input class="input" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="naam.achternaam@gmail.com" required>
            </label>

            <label class="field">
                <span class="field-label">Wachtwoord</span>
                <input class="input" type="password" name="wachtwoord" required>
            </label>

            <div class="auth-actions">
                <button class="btn btn-primary" type="submit">Inloggen</button>
                <p class="auth-switch">Nog geen account? <a href="register.php">Registreren</a></p>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
