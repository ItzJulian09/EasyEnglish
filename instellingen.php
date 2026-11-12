<?php
// =====================================================
// instellingen.php - Account, taal en privacy
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\UserRepository;

$user = requireLogin();
$userId = (int) $user['id'];
$userRepository = new UserRepository();
$errors = [];

$form = [
    'full_name'         => $user['full_name'],
    'email'             => $user['email'],
    'site_language'     => $user['site_language'],
    'default_direction' => $user['default_direction'],
    'default_public'    => (int) $user['default_public'],
];

if (isPost()) {
    checkCsrf();

    $form = [
        'full_name'         => trim($_POST['full_name'] ?? ''),
        'email'             => strtolower(trim($_POST['email'] ?? '')),
        'site_language'     => ($_POST['site_language'] ?? '') === 'en' ? 'en' : 'nl',
        'default_direction' => ($_POST['default_direction'] ?? '') === 'en-nl' ? 'en-nl' : 'nl-en',
        'default_public'    => isset($_POST['default_public']) ? 1 : 0,
    ];

    if ($form['full_name'] === '') {
        $errors[] = t('Vul je volledige naam in.');
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('Vul een geldig e-mailadres in.');
    } elseif ($userRepository->emailUsedByOtherUser($form['email'], $userId)) {
        $errors[] = t('Dit e-mailadres is al in gebruik.');
    }

    if (!$errors) {
        $userRepository->updateSettings(
            $userId,
            $form['full_name'],
            $form['email'],
            $form['site_language'],
            $form['default_direction'],
            (bool) $form['default_public']
        );
        $_SESSION['language'] = $form['site_language'];

        setFlash(t('Je instellingen zijn opgeslagen.'));
        redirect('instellingen.php');
    }
}

pageHeader(t('Instellingen'));
?>
<main class="page">
  <div class="title-area">
    <h1><?= t('Instellingen') ?></h1>
    <p><?= t('Beheer je accountgegevens, taal en privacyvoorkeuren.') ?></p>
  </div>

  <?php showFlash(); ?>

  <?php if ($errors): ?>
    <div class="flash flash-error">
      <?php foreach ($errors as $error): ?>
        <div><?= e($error) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="forms-column">
    <?= csrfField() ?>

    <section class="card">
      <h2 class="card-title"><?= t('Accountgegevens') ?></h2>
      <div class="field-row">
        <label class="field">
          <span class="field-label"><?= t('Volledige Naam') ?></span>
          <input class="input input-small" type="text" name="full_name" required value="<?= e($form['full_name']) ?>">
        </label>
        <label class="field">
          <span class="field-label"><?= t('E-mailadres') ?></span>
          <input class="input input-small" type="email" name="email" required value="<?= e($form['email']) ?>">
        </label>
      </div>
    </section>

    <section class="card">
      <h2 class="card-title"><?= t('Voorkeuren') ?></h2>

      <div class="field">
        <span class="field-label"><?= t('Taal van de Website') ?></span>
        <div class="toggle-option">
          <div class="segmented segmented-bordered">
            <label>
              <input type="radio" name="site_language" value="nl" <?= $form['site_language'] === 'nl' ? 'checked' : '' ?>>
              <span>Nederlands</span>
            </label>
            <label>
              <input type="radio" name="site_language" value="en" <?= $form['site_language'] === 'en' ? 'checked' : '' ?>>
              <span>English</span>
            </label>
          </div>
          <span class="help-text"><?= t("Dit verandert de taal van knoppen, menu's en uitleg.") ?></span>
        </div>
      </div>

      <div class="field">
        <span class="field-label"><?= t('Standaard Taalrichting') ?></span>
        <div class="toggle-option">
          <div class="segmented segmented-bordered">
            <label>
              <input type="radio" name="default_direction" value="nl-en" <?= $form['default_direction'] === 'nl-en' ? 'checked' : '' ?>>
              <span>NL → EN</span>
            </label>
            <label>
              <input type="radio" name="default_direction" value="en-nl" <?= $form['default_direction'] === 'en-nl' ? 'checked' : '' ?>>
              <span>EN → NL</span>
            </label>
          </div>
          <span class="help-text"><?= t('Wordt gebruikt bij elke nieuwe toets die je maakt.') ?></span>
        </div>
      </div>

      <div class="field">
        <span class="field-label"><?= t('Zichtbaarheid Nieuwe Toetsen') ?></span>
        <label class="switch-row">
          <span class="switch-label"><?= icon('globe', 18) ?> <?= t('Nieuwe woordenlijsten standaard openbaar maken') ?></span>
          <input type="checkbox" name="default_public" class="switch-input" <?= $form['default_public'] ? 'checked' : '' ?>>
          <span class="switch"></span>
        </label>
      </div>
    </section>

    <div class="footer-row">
      <a class="btn btn-outline" href="logout.php"><?= icon('trash-logout') ?> <?= t('Uitloggen') ?></a>
      <button class="btn btn-primary btn-wide" type="submit"><?= t('Instellingen Opslaan') ?></button>
    </div>
  </form>
</main>
<?php pageFooter(); ?>
