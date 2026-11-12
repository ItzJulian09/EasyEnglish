<?php
// =====================================================
// functions.php
// Dit bestand wordt bovenaan elke pagina geladen.
// Het start de sessie, laadt de klassen en bevat hulpfuncties.
// =====================================================

session_start();

define('ROOT_PATH', dirname(__DIR__));

// -----------------------------------------------------
// Autoloader (PSR-4)
// "App\WordListRepository" wordt automatisch geladen uit "src/WordListRepository.php"
// -----------------------------------------------------
spl_autoload_register(function (string $className): void {
    $prefix = 'App\\';
    if (strpos($className, $prefix) !== 0) {
        return;
    }

    $file = ROOT_PATH . '/src/' . str_replace('\\', '/', substr($className, strlen($prefix))) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/lang.php';

use App\UserRepository;

// -----------------------------------------------------
// Algemene hulpfuncties
// -----------------------------------------------------

/**
 * Maakt tekst veilig om in HTML te tonen (tegen XSS).
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Is het formulier verstuurd met POST?
 */
function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

// -----------------------------------------------------
// Inloggen
// -----------------------------------------------------

/**
 * De ingelogde gebruiker, of null als niemand is ingelogd.
 */
function currentUser(): ?array
{
    static $user = null; // onthouden zodat we maar één keer de database vragen

    if ($user === null && isset($_SESSION['user_id'])) {
        $repository = new UserRepository();
        $user = $repository->findById((int) $_SESSION['user_id']);
    }

    return $user;
}

/**
 * Stuurt bezoekers die niet zijn ingelogd naar de inlogpagina.
 */
function requireLogin(): array
{
    $user = currentUser();
    if (!$user) {
        redirect('login.php');
    }

    return $user;
}

function loginUser(array $user): void
{
    session_regenerate_id(true); // nieuwe sessie-id tegen session fixation
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['language'] = $user['site_language'];
}

// -----------------------------------------------------
// Meldingen ("flash messages") die één keer getoond worden
// -----------------------------------------------------

function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function showFlash(): void
{
    if (!isset($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    echo '<div class="flash flash-' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}

// -----------------------------------------------------
// Beveiliging van formulieren (CSRF-token)
// -----------------------------------------------------

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Zet dit in elk <form method="post">.
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Controleer dit bovenaan bij elke POST.
 */
function checkCsrf(): void
{
    $sentToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $sentToken)) {
        http_response_code(400);
        exit('Ongeldig formulier. Ga terug en probeer het opnieuw.');
    }
}

// -----------------------------------------------------
// Weergave-hulpjes
// -----------------------------------------------------

/**
 * Een icoon uit assets/icons tonen.
 */
function icon(string $name, int $size = 16): string
{
    return '<img class="icon" src="assets/icons/' . e($name) . '.svg" width="' . $size . '" height="' . $size . '" alt="">';
}

/**
 * Voorletters van een naam, bijvoorbeeld "Sven de Vries" wordt "SV".
 */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

    return mb_strtoupper($first . $last);
}

/**
 * Een datum als "Vandaag", "Gisteren" of "3 dagen geleden".
 */
function relativeDay(?string $dateTime): string
{
    if ($dateTime === null) {
        return t('Nieuw');
    }

    $today = new DateTime('today');
    $date = new DateTime($dateTime);
    $date->setTime(0, 0);
    $days = (int) $date->diff($today)->days;

    if ($days === 0) {
        return t('Vandaag');
    }
    if ($days === 1) {
        return t('Gisteren');
    }
    if ($days < 7) {
        return t('%d dagen geleden', $days);
    }
    if ($days < 14) {
        return t('1 week geleden');
    }

    return t('%d weken geleden', (int) floor($days / 7));
}

/**
 * Zoals relativeDay(), maar bij vandaag en gisteren ook met tijd: "Vandaag, 14:22".
 */
function relativeDateTime(string $dateTime): string
{
    $day = relativeDay($dateTime);
    $time = (new DateTime($dateTime))->format('H:i');

    if ($day === t('Vandaag') || $day === t('Gisteren')) {
        return $day . ', ' . $time;
    }

    return $day;
}

// -----------------------------------------------------
// Header en footer van elke pagina
// -----------------------------------------------------

function pageHeader(string $title, string $active = ''): void
{
    $user = currentUser();
    $language = currentLanguage();
    $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
    ?>
    <!doctype html>
    <html lang="<?= e($language) ?>">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= e($title) ?> - Easy English</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="assets/css/styles.css">
      <script src="assets/js/app.js" defer></script>
    </head>
    <body>
      <header class="topbar">
        <a class="brand" href="index.php">
          <img class="logo" src="assets/img/logo.png" alt="Easy English logo">
          <span class="brand-name">Easy English</span>
        </a>

        <nav class="main-nav">
          <a class="<?= $active === 'lists' ? 'active' : '' ?>" href="index.php"><?= t('Mijn lijsten') ?></a>
          <a class="<?= $active === 'builder' ? 'active' : '' ?>" href="toetsbouwer.php"><?= t('Toets bouwer') ?></a>
          <a class="<?= $active === 'progress' ? 'active' : '' ?>" href="voortgang.php"><?= t('Voortgang') ?></a>
        </nav>

        <div class="header-right">
          <div class="lang-toggle">
            <a class="<?= $language === 'nl' ? 'active' : '' ?>" href="taal.php?taal=nl&amp;terug=<?= $returnUrl ?>">NL</a>
            <a class="<?= $language === 'en' ? 'active' : '' ?>" href="taal.php?taal=en&amp;terug=<?= $returnUrl ?>">EN</a>
          </div>

          <?php if ($user): ?>
            <a class="avatar" href="instellingen.php" title="<?= t('Instellingen') ?>"><?= e(initials($user['full_name'])) ?></a>
          <?php else: ?>
            <a class="header-login" href="login.php"><?= t('Inloggen') ?></a>
          <?php endif; ?>
        </div>
      </header>
    <?php
}

function pageFooter(): void
{
    ?>
      <footer class="site-footer">
        <div class="footer-top">
          <div class="footer-brand">
            <p class="footer-logo">EasyEnglish</p>
            <p><?= t('Oefen woordenlijsten, bouw toetsen en volg je voortgang in een rustige, overzichtelijke leeromgeving.') ?></p>
          </div>

          <div class="footer-links">
            <div class="footer-col">
              <h3>Product</h3>
              <a href="index.php"><?= t('Mijn lijsten') ?></a>
              <a href="toetsbouwer.php"><?= t('Toets bouwer') ?></a>
              <a href="voortgang.php"><?= t('Voortgang') ?></a>
            </div>
            <div class="footer-col">
              <h3><?= t('Openbare bibliotheek') ?></h3>
              <a href="index.php#bibliotheek"><?= t('Zoek lijsten') ?></a>
              <a href="index.php?sorteer=naam#bibliotheek"><?= t('Niveaus') ?></a>
              <a href="index.php?sorteer=populair#bibliotheek"><?= t('Populair') ?></a>
            </div>
            <div class="footer-col">
              <h3>Contact</h3>
              <a href="#"><?= t('Hulp & feedback') ?></a>
              <a href="#">Privacy</a>
              <a href="#"><?= t('Voor docenten') ?></a>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p>© 2026 EasyEnglish. <?= t('Alle rechten voorbehouden.') ?></p>
          <div class="footer-legal">
            <a href="#">Privacy</a>
            <a href="#"><?= t('Voorwaarden') ?></a>
          </div>
        </div>
      </footer>
    </body>
    </html>
    <?php
}
