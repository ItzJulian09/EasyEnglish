<?php
require 'includes/init.php';

if (!$user) {
    header('Location: login.php');
    exit;
}

$zoek = '';
if (isset($_GET['zoek'])) {
    $zoek = trim($_GET['zoek']);
}

$attempt = new Attempt();
$beste = $attempt->getBest($user['id']);
$pogingen = $attempt->getForUser($user['id'], $zoek);

$titel = 'Voortgang';
$actief = 'voortgang';
include 'includes/header.php';
?>

<main class="page">
    <div class="title-area">
        <h1>Mijn Prestaties &amp; Geschiedenis</h1>
        <p>Bekijk je beste score en elke poging in één overzicht.</p>
    </div>

    <div class="card kpi-card">
        <p class="kpi-label">Beste Score</p>
        <?php if ($beste) { ?>
            <p><span class="metric-value"><?= $beste['score'] ?>%</span> op "<?= htmlspecialchars($beste['title']) ?>"</p>
        <?php } else { ?>
            <p>Maak je eerste toets om hier je beste score te zien.</p>
        <?php } ?>
    </div>

    <form method="get" class="search-input">
        <img src="assets/icons/search.svg" alt="">
        <input type="search" name="zoek" value="<?= htmlspecialchars($zoek) ?>" placeholder="Zoek op toetsnaam...">
    </form>

    <div class="card">
        <?php if (count($pogingen) == 0) { ?>
            <p>Nog geen pogingen gevonden.</p>
        <?php } else { ?>
            <table class="data-table">
                <tr>
                    <th>Datum &amp; Tijd</th>
                    <th>Naam Woordenlijst</th>
                    <th>Score</th>
                </tr>
                <?php foreach ($pogingen as $poging) { ?>
                    <tr>
                        <td><?= date('d-m-Y H:i', strtotime($poging['created_at'])) ?></td>
                        <td class="strong"><?= htmlspecialchars($poging['title']) ?></td>
                        <td><span class="badge">Score: <?= $poging['score'] ?>%</span></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
