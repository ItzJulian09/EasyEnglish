<?php
// =====================================================
// auth.php
// Gedeelde code voor login.php en register.php.
// $isRegister wordt in die bestanden op true/false gezet.
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\UserRepository;

if (currentUser()) {
    redirect('index.php');
}

$userRepository = new UserRepository();
$error = '';

// Wat de gebruiker al had ingevuld, zodat dat blijft staan bij een fout
$fullName = trim($_POST['full_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));

if (isPost()) {
    checkCsrf();
    $password = $_POST['password'] ?? '';

    if ($isRegister) {
        // ---------- Registreren ----------
        if ($fullName === '') {
            $error = t('Vul je volledige naam in.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = t('Vul een geldig e-mailadres in.');
        } elseif (strlen($password) < 8) {
            $error = t('Je wachtwoord moet minimaal 8 tekens bevatten.');
        } elseif (!isset($_POST['terms'])) {
            $error = t('Je moet akkoord gaan met de voorwaarden.');
        } elseif ($userRepository->findByEmail($email)) {
            $error = t('Dit e-mailadres is al in gebruik.');
        } else {
            $newUserId = $userRepository->create($fullName, $email, $password, currentLanguage());
            loginUser($userRepository->findById($newUserId));
            setFlash(t('Welkom bij EasyEnglish! Maak je eerste woordenlijst aan.'));
            redirect('index.php');
        }
    } else {
        // ---------- Inloggen ----------
        $user = $userRepository->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);

            // "Onthoud mij": de sessie-cookie blijft 30 dagen bestaan
            if (isset($_POST['remember'])) {
                $thirtyDays = 60 * 60 * 24 * 30;
                setcookie(session_name(), session_id(), time() + $thirtyDays, '/');
            }

            redirect('index.php');
        }

        $error = t('E-mailadres of wachtwoord is niet juist.');
    }
}

pageHeader($isRegister ? t('Registreren') : t('Inloggen'));
?>
<main class="auth-page">
  <div class="auth-card <?= $isRegister ? 'auth-card-wide' : '' ?>">
    <div class="card-header-center">
      <h1><?= $isRegister ? t('Account aanmaken') : t('Inloggen bij EasyEnglish') ?></h1>
      <p><?= $isRegister ? t('Begin direct met het bouwen van jouw Engelse woordenschat') : t('Leer Engels in je eigen tempo') ?></p>
    </div>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-stack">
      <?= csrfField() ?>

      <?php if ($isRegister): ?>
        <label class="field">
          <span class="field-label"><?= t('Volledige naam') ?></span>
          <input class="input" type="text" name="full_name" required value="<?= e($fullName) ?>" placeholder="<?= t('bijv. Sven de Vries') ?>">
        </label>
      <?php endif; ?>

      <label class="field">
        <span class="field-label"><?= t('E-mailadres') ?></span>
        <input class="input" type="email" name="email" required value="<?= e($email) ?>"
               placeholder="<?= $isRegister ? t('bijv. sven@voorbeeld.nl') : 'naam.achternaam@gmail.com' ?>">
      </label>

      <label class="field">
        <span class="field-label"><?= t('Wachtwoord') ?></span>
        <span class="input input-with-icon">
          <input type="password" name="password" required <?= $isRegister ? 'minlength="8"' : '' ?>
                 placeholder="<?= $isRegister ? t('Minimaal 8 tekens') : '••••••••••••••' ?>">
          <button type="button" class="toggle-password" title="<?= t('Wachtwoord tonen') ?>"><?= icon('eye-off', 18) ?></button>
        </span>
      </label>

      <?php if ($isRegister): ?>
        <label class="checkbox-row checkbox-small">
          <input type="checkbox" name="terms" required>
          <span class="checkbox-box"><?= icon('check', 10) ?></span>
          <span><?= t('Ik ga akkoord met de <b>Algemene Voorwaarden</b> en het <b>Privacybeleid</b> van EasyEnglish.') ?></span>
        </label>
      <?php else: ?>
        <label class="checkbox-row">
          <input type="checkbox" name="remember" checked>
          <span class="checkbox-box"><?= icon('check', 10) ?></span>
          <span><?= t('Onthoud mij') ?></span>
        </label>
      <?php endif; ?>

      <div class="auth-actions">
        <button class="btn btn-primary" type="submit">
          <?= $isRegister ? t('Account Aanmaken') : t('Inloggen') ?>
        </button>
        <p class="auth-switch">
          <?php if ($isRegister): ?>
            <?= t('Heb je al een account?') ?> <a href="login.php"><?= t('Inloggen') ?></a>
          <?php else: ?>
            <?= t('Nog geen account?') ?> <a href="register.php"><?= t('Registreren') ?></a>
          <?php endif; ?>
        </p>
      </div>
    </form>
  </div>
</main>
<?php pageFooter(); ?>
