<?php
// =====================================================
// lijst.php - Details van een openbare woordenlijst
// Voorbeeld: lijst.php?id=5
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\WordListRepository;

$user = requireLogin();
$userId = (int) $user['id'];
$listRepository = new WordListRepository();

$listId = (int) ($_GET['id'] ?? 0);
$list = $listRepository->findById($listId);

// Alleen openbare lijsten, of je eigen lijst, mag je bekijken. Een beheerder mag alles zien.
$isOwner = $list && (int) $list['user_id'] === $userId;
$isVisible = $list && ($isOwner || isAdmin() || ($list['is_public'] && $list['is_published']));

if (!$isVisible) {
    setFlash(t('Deze lijst bestaat niet of is niet openbaar.'), 'error');
    redirect('index.php');
}

// ---------- Knoppen: liken of opslaan ----------
if (isPost()) {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'like') {
        $listRepository->toggleLike($listId, $userId);
        redirect('lijst.php?id=' . $listId);
    }

    if ($action === 'admin-delete' && isAdmin()) {
        $listRepository->deleteAsAdmin($listId);
        setFlash(t('De toets is verwijderd.'));
        redirect('beheer.php');
    }

    if ($action === 'save') {
        $listRepository->copyToUser($listId, $userId);
        setFlash(t('"%s" staat nu in jouw lijsten.', $list['title']));
        redirect('index.php');
    }
}

$previewLimit = 10;
$previewWords = $listRepository->getWords($listId, $previewLimit);
$isLiked = $listRepository->isLikedBy($listId, $userId);

pageHeader($list['title'], 'lists');
?>
<main class="page">
  <?php showFlash(); ?>

  <a class="breadcrumb" href="index.php#bibliotheek">
    <span class="back-btn"><?= icon('arrow-left') ?></span>
    <?= t('Terug naar Bibliotheek') ?>
  </a>

  <div class="detail-split">
    <!-- ============ Linkerkolom: info over de lijst ============ -->
    <div class="meta-column">
      <section class="card card-dark-border detail-card">
        <div class="title-group">
          <h1><?= e($list['title']) ?></h1>
          <div class="author-row">
            <span class="author-avatar"><?= e(initials($list['author_name'])) ?></span>
            <span><?= t('Gemaakt door') ?> <b><?= e($list['author_name']) ?></b></span>
          </div>
        </div>

        <?php if ($list['description']): ?>
          <p class="detail-description"><?= e($list['description']) ?></p>
        <?php endif; ?>

        <div class="stats-row">
          <div class="stat">
            <strong><?= (int) $list['word_count'] ?></strong>
            <span><?= t('Woorden') ?></span>
          </div>
          <form method="post" class="stat stat-right">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="like">
            <button type="submit" class="like-button" title="<?= $isLiked ? t('Stem weghalen') : t('Stem geven') ?>">
              <?= $isLiked ? icon('heart-filled', 16) : icon('heart', 16) ?>
              <strong><?= (int) $list['like_count'] ?></strong>
            </button>
            <span><?= t('Stemmen') ?></span>
          </form>
        </div>

        <div class="detail-actions">
          <a class="btn btn-dark btn-block" href="toets.php?id=<?= $listId ?>&amp;start=1"><?= t('Start Toets (Typen)') ?></a>
          <?php if (!$isOwner): ?>
            <form method="post">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="save">
              <button type="submit" class="btn btn-outline-dark btn-block">
                <?= icon('plus-small', 16) ?> <?= t('Opslaan in mijn Bibliotheek') ?>
              </button>
            </form>
          <?php endif; ?>

          <?php if (isAdmin()): ?>
            <form method="post" class="confirm-delete"
                  data-confirm="<?= e(t('Weet je zeker dat je "%s" wilt verwijderen? Dit kan niet ongedaan worden.', $list['title'])) ?>">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="admin-delete">
              <button type="submit" class="btn btn-soft btn-block">
                <?= icon('trash') ?> <?= t('Toets verwijderen (beheerder)') ?>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <!-- ============ Rechterkolom: voorbeeldwoorden ============ -->
    <div class="words-column">
      <section class="card card-dark-border">
        <h2 class="card-title">
          <?= t('Voorbeeldwoorden') ?> (<?= count($previewWords) ?> <?= t('van') ?> <?= (int) $list['word_count'] ?>)
        </h2>

        <table class="data-table preview-table">
          <thead>
            <tr>
              <th><?= t('Nederlands Woord / Uitdrukking') ?></th>
              <th><?= t('Engelse Vertaling') ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($previewWords as $word): ?>
              <tr>
                <td class="strong"><?= e($word['dutch']) ?></td>
                <td><?= e($word['english']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</main>
<?php pageFooter(); ?>
