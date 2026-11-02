<?php
require_once __DIR__ . '/includes/functions.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST' && ($_POST['action'] ?? '') === 'add-list') {
    $lists = readData('lists.json');
    $name = trim($_POST['name'] ?? '');

    if ($name !== '') {
        $lists[] = [
            'id' => uniqid('list_', true),
            'name' => $name,
            'words' => [],
            'score' => null,
        ];
        saveData('lists.json', $lists);
    }

    redirect('index.php');
}

$lists = array_values(array_filter(
    readData('lists.json'),
    fn($list) => isset($list['name'])
));

pageHeader('Mijn lijsten', 'lists');
?>
<main>
  <section class="workspace">
    <div class="intro-row">
      <p>Oefen je opgeslagen woordenlijsten of maak een nieuwe aan om te starten.</p>
      <button class="primary-button" onclick="document.getElementById('new-list').showModal()">
        + Nieuwe lijst toevoegen
      </button>
    </div>

    <div class="list-grid">
      <?php foreach ($lists as $list): ?>
        <article class="list-card">
          <div class="card-heading">
            <h2><?= e($list['name']) ?></h2>
            <span class="list-icon">•••</span>
          </div>
          <p class="word-count"><?= count($list['words']) ?> Engelse woorden</p>
          <div class="card-meta">
            <span class="score">
              <?= $list['score'] ? 'Score: ' . $list['score'] . '%' : 'Nog niet geoefend' ?>
            </span>
            <span>Eigen lijst</span>
          </div>
          <button class="outline-button">Start toets</button>
        </article>
      <?php endforeach; ?>

      <?php if (!$lists): ?>
        <p class="empty-state">Je hebt nog geen woordenlijsten.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<dialog id="new-list">
  <form method="post">
    <input type="hidden" name="action" value="add-list">
    <h2>Nieuwe woordenlijst</h2>
    <label>
      Naam
      <input name="name" required placeholder="Bijvoorbeeld: Vakantie">
    </label>
    <button class="primary-button">Opslaan</button>
  </form>
</dialog>
<?php pageFooter(); ?>
