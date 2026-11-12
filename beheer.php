<?php
// =====================================================
// beheer.php - Beheerpagina (alleen voor admins)
// Hier zie je álle toetsen van alle gebruikers en kun je ze verwijderen.
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\WordListRepository;

requireAdmin();
$listRepository = new WordListRepository();

// ---------- Een toets verwijderen ----------
if (isPost() && ($_POST['action'] ?? '') === 'delete-list') {
    checkCsrf();
    $listRepository->deleteAsAdmin((int) $_POST['list_id']);
    setFlash(t('De toets is verwijderd.'));
    redirect('beheer.php');
}

$search = trim($_GET['zoek'] ?? '');
$lists = $listRepository->getAllLists($search);

pageHeader(t('Beheer'), 'admin');
?>
<main class="page">
  <div class="title-area">
    <h1><?= t('Beheer') ?></h1>
    <p><?= t('Alle toetsen van alle gebruikers. Als beheerder kun je elke toets verwijderen.') ?></p>
  </div>

  <?php showFlash(); ?>

  <section class="history-section">
    <form method="get" class="search-input search-input-small">
      <?= icon('search', 18) ?>
      <input type="search" name="zoek" value="<?= e($search) ?>" placeholder="<?= t('Zoek op toetsnaam of maker...') ?>">
    </form>

    <div class="card table-card">
      <?php if ($lists): ?>
        <table class="data-table history-table admin-table">
          <thead>
            <tr>
              <th><?= t('Naam Woordenlijst') ?></th>
              <th><?= t('Gemaakt door') ?></th>
              <th class="col-small"><?= t('Woorden') ?></th>
              <th class="col-small"><?= t('Status') ?></th>
              <th class="col-actions"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lists as $list): ?>
              <tr>
                <td class="strong">
                  <a class="table-link" href="lijst.php?id=<?= (int) $list['id'] ?>"><?= e($list['title']) ?></a>
                </td>
                <td>
                  <?= e($list['author_name']) ?>
                  <span class="muted"><?= e($list['author_email']) ?></span>
                </td>
                <td class="col-small"><?= (int) $list['word_count'] ?></td>
                <td class="col-small">
                  <?php if (!$list['is_published']): ?>
                    <span class="badge"><?= t('Concept') ?></span>
                  <?php elseif ($list['is_public']): ?>
                    <span class="badge"><?= t('Openbaar') ?></span>
                  <?php else: ?>
                    <span class="badge"><?= t('Privé') ?></span>
                  <?php endif; ?>
                </td>
                <td class="col-actions">
                  <form method="post" class="confirm-delete"
                        data-confirm="<?= e(t('Weet je zeker dat je "%s" wilt verwijderen? Dit kan niet ongedaan worden.', $list['title'])) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete-list">
                    <input type="hidden" name="list_id" value="<?= (int) $list['id'] ?>">
                    <button class="icon-button" type="submit" title="<?= t('Verwijderen') ?>"><?= icon('trash') ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="empty-table"><?= t('Geen toetsen gevonden.') ?></p>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php pageFooter(); ?>
