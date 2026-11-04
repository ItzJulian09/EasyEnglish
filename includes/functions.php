<?php
session_start();

define('ROOT_PATH', dirname(__DIR__));
define('STORAGE_PATH', ROOT_PATH . '/storage');

if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0777, true);
}

function readData(string $file, array $default = []): array
{
    $path = STORAGE_PATH . '/' . $file;
    if (!file_exists($path)) {
        file_put_contents($path, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }

    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : $default;
}

function saveData(string $file, array $data): void
{
    file_put_contents(STORAGE_PATH . '/' . $file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function redirect(string $url = '../index.php'): never
{
    header('Location: ' . $url);
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function currentUser(): ?array
{
    foreach (readData('users.json') as $user) {
        if ($user['id'] === ($_SESSION['user_id'] ?? '')) {
            return $user;
        }
    }

    return null;
}

function requireLogin(): array
{
    $user = currentUser();
    if (!$user) {
        redirect('login.php');
    }

    return $user;
}

function pageHeader(string $title, string $active = ''): void
{
    $user = requireLogin();
    ?>
    <!doctype html>
    <html lang="nl">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= e($title) ?> - Easy English</title>
      <link rel="stylesheet" href="assets/css/styles.css">
    </head>
    <body>
      <header class="topbar">
        <div class="topbar-inner">
          <a class="brand" href="index.php"><img class="logo" src="assets/img/logo.png" alt="Easy English logo"><span class="brand-name">Easy English</span></a>
          <nav class="main-nav">
            <a class="<?= $active === 'lists' ? 'active' : '' ?>" href="index.php">Mijn lijsten</a>
            <a class="<?= $active === 'library' ? 'active' : '' ?>" href="index.php#bibliotheek">Bibliotheek</a>
            <a class="<?= $active === 'progress' ? 'active' : '' ?>" href="index.php#voortgang">Voortgang</a>
          </nav>
          <div class="account">
            <span><?= e($user['name']) ?></span>
            <a class="logout" href="logout.php">Uitloggen</a>
          </div>
        </div>
      </header>
    <?php
}

function publicHeader(): void
{
    ?>
    <header class="topbar">
      <div class="topbar-inner">
        <a class="brand" href="index.php">
          <img class="logo" src="assets/img/logo.png" alt="Easy English logo">
          <span class="brand-name">Easy English</span>
        </a>
        <nav class="main-nav">
          <a href="index.php">Mijn lijsten</a>
          <a href="index.php#bibliotheek">Bibliotheek</a>
          <a href="index.php#voortgang">Voortgang</a>
        </nav>
        <div class="account">
          <a class="auth-nav-link" href="login.php">Inloggen</a>
          <a class="auth-nav-link" href="register.php">Registreren</a>
        </div>
      </div>
    </header>
    <?php
}

function pageFooter(): void
{
    ?>
    </body>
    </html>
    <?php
}
