<?php
// =====================================================
// index.php - Mijn lijsten + Openbare bibliotheek
// =====================================================

require_once __DIR__ . '/includes/functions.php';

use App\WordListRepository;

$user = requireLogin();
$userId = (int) $user['id'];
$listRepository = new WordListRepository();

// ---------- Een eigen lijst verwijderen ----------
if (isPost() && ($_POST['action'] ?? '') === 'delete-list') {
    checkCsrf();
    $listRepository->delete((int) $_POST['list_id'], $userId);
    setFlash(t('De woordenlijst is verwijderd.'));
    redirect('index.php');
}

// ---------- Zoeken, filteren en sorteren in de bibliotheek ----------
$search = trim($_GET['zoek'] ?? '');
$sort = $_GET['sorteer'] ?? 'populair';

$myLists = $listRepository->getListsForUser($userId);
$publicLists = $listRepository->getPublicLists($userId, $search, $sort);

pageHeader(t('Mijn lijsten'), 'lists');
?>
<main class="page">
  <?php showFlash(); ?>

  <!-- ============ Mijn lijsten ============ -->
  <section class="section-my-lists">
    <div class="section-header">
      <p class="intro-text"><?= t('Oefen je opgeslagen woordenlijsten of maak een nieuwe aan om te starten.') ?></p>
      <a class="btn btn-primary" href="toetsbouwer.php">
        <?= icon('plus-white', 18) ?> <?= t('Nieuwe Lijst Toevoegen') ?>
      </a>
    </div>

    <?php if (!$myLists): ?>
      <div class="empty-state">
        <p><?= t('Je hebt nog geen woordenlijsten. Maak er een aan of sla een lijst op uit de bibliotheek.') ?></p>
      </div>
    <?php endif; ?>

    <div class="lists-grid">
      <?php foreach ($myLists as $list): ?>
        <article class="list-card">
          <div class="card-top">
            <div class="card-title-group">
              <h2><?= e($list['title']) ?></h2>
              <p><?= t('%d Engelse woorden', (int) $list['word_count']) ?></p>
            </div>
            <div class="crud-actions">
              <a class="icon-button" href="toetsbouwer.php?id=<?= (int) $list['id'] ?>" title="<?= t('Bewerken') ?>"><?= icon('pencil') ?></a>
              <form method="post" class="confirm-delete" data-confirm="<?= e(t('Weet je zeker dat je deze lijst wilt verwijderen?')) ?>">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="delete-list">
                <input type="hidden" name="list_id" value="<?= (int) $list['id'] ?>">
                <button class="icon-button" type="submit" title="<?= t('Verwijderen') ?>"><?= icon('trash') ?></button>
              </form>
            </div>
          </div>

          <div class="card-middle">
            <span class="badge">
              <?php if (!$list['is_published']): ?>
                <?= t('Concept') ?>
              <?php elseif ($list['last_score'] !== null): ?>
                <?= t('Score') ?>: <?= (int) $list['last_score'] ?>%
              <?php else: ?>
                <?= t('Score') ?>: <?= t('Nog geen score') ?>
              <?php endif; ?>
            </span>
            <span class="meta-text"><?= t('Laatst geoefend') ?>: <?= e(relativeDay($list['last_practiced'])) ?></span>
          </div>

          <div class="card-bottom">
            <?php if ((int) $list['word_count'] > 0): ?>
              <a class="btn btn-soft" href="toets.php?id=<?= (int) $list['id'] ?>&amp;start=1"><?= t('Start Typ Toets') ?></a>
            <?php else: ?>
              <a class="btn btn-soft" href="toetsbouwer.php?id=<?= (int) $list['id'] ?>"><?= t('Woorden toevoegen') ?></a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============ Openbare bibliotheek ============ -->
  <section class="section-public" id="bibliotheek">
    <div class="public-title-group">
      <h2><?= t('Openbare Bibliotheek') ?></h2>
      <p><?= t('Ontdek duizenden gratis lijsten gedeeld door taaldocenten en de EasyEnglish-community.') ?></p>
    </div>

    <form method="get" action="index.php#bibliotheek" class="search-filters">
      <label class="search-input">
        <?= icon('search', 18) ?>
        <input type="search" name="zoek" value="<?= e($search) ?>" placeholder="<?= t('Zoek op onderwerp of auteur...') ?>">
      </label>

      <label class="filter-pill">
        <select name="sorteer" class="auto-submit">
          <option value="populair" <?= $sort === 'populair' ? 'selected' : '' ?>><?= t('Sorteer') ?>: <?= t('Meest Populair') ?></option>
          <option value="nieuwste" <?= $sort === 'nieuwste' ? 'selected' : '' ?>><?= t('Sorteer') ?>: <?= t('Nieuwste') ?></option>
          <option value="naam" <?= $sort === 'naam' ? 'selected' : '' ?>><?= t('Sorteer') ?>: <?= t('Naam (A-Z)') ?></option>
        </select>
        <?= icon('chevron-down', 14) ?>
      </label>
    </form>

    <?php if (!$publicLists): ?>
      <div class="empty-state">
        <p><?= t('Geen lijsten gevonden. Probeer een andere zoekterm.') ?></p>
      </div>
    <?php endif; ?>

    <div class="public-grid">
      <?php foreach ($publicLists as $list): ?>
        <a class="public-card" href="lijst.php?id=<?= (int) $list['id'] ?>">
          <div class="card-head">
            <div class="tags">
              <?php if ($list['category']): ?><span class="tag"><?= e($list['category']) ?></span><?php endif; ?>
            </div>
            <h3><?= e($list['title']) ?></h3>
            <p><?= t('Door') ?>: <?= e($list['author_name']) ?></p>
          </div>
          <div class="card-foot">
            <span><?= t('%d woorden', (int) $list['word_count']) ?></span>
            <span class="likes"><?= icon('heart', 14) ?> <b><?= (int) $list['like_count'] ?></b></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php pageFooter(); ?>
