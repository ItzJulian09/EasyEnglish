<?php
// =====================================================
// voortgang.php - Mijn prestaties & geschiedenis
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\AttemptRepository;

$user = requireLogin();
$userId = (int) $user['id'];
$attemptRepository = new AttemptRepository();

$search = trim($_GET['zoek'] ?? '');
$bestAttempt = $attemptRepository->getBestForUser($userId);
$attempts = $attemptRepository->getForUser($userId, $search);

pageHeader(t('Voortgang'), 'progress');
?>
<main class="page">
  <div class="title-area">
    <h1><?= t('Mijn Prestaties & Geschiedenis') ?></h1>
    <p><?= t('Bekijk je beste score en elke poging in één rustig overzicht.') ?></p>
  </div>

  <section class="card kpi-card">
    <p class="kpi-label"><?= t('Beste Score') ?></p>
    <?php if ($bestAttempt): ?>
      <div class="metric">
        <span class="metric-value"><?= (int) $bestAttempt['score'] ?>%</span>
        <span class="metric-note"><?= t('op') ?> "<?= e($bestAttempt['title']) ?>"</span>
      </div>
    <?php else: ?>
      <div class="metric">
        <span class="metric-value">-</span>
        <span class="metric-note"><?= t('Maak je eerste toets om hier je beste score te zien.') ?></span>
      </div>
    <?php endif; ?>
  </section>

  <section class="history-section">
    <form method="get" class="search-input search-input-small">
      <?= icon('search', 18) ?>
      <input type="search" name="zoek" value="<?= e($search) ?>" placeholder="<?= t('Zoek op toetsnaam...') ?>">
    </form>

    <div class="card table-card">
      <?php if ($attempts): ?>
        <table class="data-table history-table">
          <thead>
            <tr>
              <th class="col-date"><?= t('Datum & Tijd') ?></th>
              <th><?= t('Naam Woordenlijst') ?></th>
              <th class="col-score"><?= t('Score') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($attempts as $attempt): ?>
              <tr>
                <td class="col-date"><?= e(relativeDateTime($attempt['created_at'])) ?></td>
                <td class="strong"><?= e($attempt['title']) ?></td>
                <td class="col-score">
                  <span class="badge" title="<?= (int) $attempt['correct_count'] ?>/<?= (int) $attempt['total_count'] ?>">
                    <?= t('Score') ?>: <?= (int) $attempt['score'] ?>%
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="empty-table"><?= $search !== '' ? t('Geen pogingen gevonden voor deze zoekterm.') : t('Je hebt nog geen toetsen gemaakt.') ?></p>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php pageFooter(); ?>
