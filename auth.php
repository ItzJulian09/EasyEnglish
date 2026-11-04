<?php
require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    redirect('index.php');
}

$error = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $users = readData('users.json');

    if ($isRegister) {
        $emailExists = array_filter($users, fn($user) => $user['email'] === $email);

        if ($name === '') {
            $error = 'Vul je volledige naam in.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Vul een geldig e-mailadres in.';
        } elseif (strlen($password) < 6) {
            $error = 'Je wachtwoord moet minimaal 6 tekens bevatten.';
        } elseif (!isset($_POST['terms'])) {
            $error = 'Je moet akkoord gaan met de voorwaarden.';
        } elseif ($emailExists) {
            $error = 'Dit e-mailadres bestaat al.';
        } else {
            $user = [
                'id' => uniqid('user_', true),
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ];
            $users[] = $user;
            saveData('users.json', $users);
            $_SESSION['user_id'] = $user['id'];
            redirect('index.php');
        }
    } else {
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                redirect('index.php');
            }
        }

        $error = 'E-mailadres of wachtwoord is niet juist.';
    }
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isRegister ? 'Registreren' : 'Inloggen' ?> - Easy English</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php publicHeader(); ?>
  <main class="auth-page">
    <div class="auth-card">
      <h1><?= $isRegister ? 'Account aanmaken' : 'Welkom terug' ?></h1>
      <p><?= $isRegister ? 'Begin direct met het bouwen van jouw Engelse woordenschat.' : 'Log in om verder te leren.' ?></p>

      <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <?php if ($isRegister): ?>
          <label>
            Volledige naam
            <input type="text" name="name" required placeholder="bijv. Sven de Vries">
          </label>
        <?php endif; ?>
        <label>
          E-mailadres
          <input type="email" name="email" required placeholder="bijv. sven@voorbeeld.nl">
        </label>
        <label>
          Wachtwoord
          <input type="password" name="password" minlength="6" required placeholder="Minimaal 6 tekens">
        </label>
        <?php if ($isRegister): ?>
          <label class="terms">
            <input type="checkbox" name="terms" required>
            <span>Ik ga akkoord met de Algemene Voorwaarden en het Privacybeleid van Lexilo.</span>
          </label>
        <?php endif; ?>
        <button class="primary-button" type="submit">
          <?= $isRegister ? 'Account maken' : 'Inloggen' ?>
        </button>
      </form>

      <a class="auth-link" href="<?= $isRegister ? 'login.php' : 'register.php' ?>">
        <?= $isRegister ? 'Al een account? Inloggen' : 'Nog geen account? Registreren' ?>
      </a>
    </div>
  </main>
  <footer>
    <div class="footer-inner">
      <div class="footer-about">
        <h2>EasyEnglish</h2>
        <p>Oefen woordenlijsten, bouw toetsen en volg je<br>voortgang in een rustige, overzichtelijke<br>leeromgeving.</p>
        <small>© 2026 EasyEnglish. Alle rechten voorbehouden.</small>
      </div>
      <div><h3>Product</h3><a href="index.php">Mijn lijsten</a><a href="index.php#toetsen">Toets bouwer</a><a href="index.php#voortgang">Voortgang</a></div>
      <div><h3>Openbare bibliotheek</h3><a href="index.php#bibliotheek">Zoek lijsten</a><a href="#">Niveaus</a><a href="#">Populair</a></div>
      <div><h3>Contact</h3><a href="#">Hulp & feedback</a><a href="#">Privacy</a><a href="#">Voor docenten</a></div>
    </div>
    <div class="footer-links"><a href="#">Privacy</a><a href="#">Voorwaarden</a></div>
  </footer>
</body>
</html>
