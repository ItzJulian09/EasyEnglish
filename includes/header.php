<?php
// Voor je dit bestand include, zet je $titel en $actief (welk menu-item onderstreept is)

// Voorletters voor het rondje rechtsboven, bijv. "Sven de Vries" wordt "SV"
$voorletters = '';
if ($user) {
    $delen = explode(' ', $user['full_name']);
    $voorletters = strtoupper(substr($delen[0], 0, 1));
    if (count($delen) > 1) {
        $voorletters .= strtoupper(substr(end($delen), 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titel) ?> - Easy English</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<header class="topbar">
    <a class="brand" href="index.php">
        <img class="logo" src="assets/img/logo.png" alt="Easy English logo">
        <span class="brand-name">Easy English</span>
    </a>

    <?php if ($user) { ?>
        <nav class="main-nav">
            <a href="index.php" class="<?= $actief == 'lijsten' ? 'active' : '' ?>">Mijn lijsten</a>
            <a href="toetsbouwer.php" class="<?= $actief == 'bouwer' ? 'active' : '' ?>">Toets bouwer</a>
            <a href="voortgang.php" class="<?= $actief == 'voortgang' ? 'active' : '' ?>">Voortgang</a>
            <?php if ($isAdmin) { ?>
                <a href="beheer.php" class="<?= $actief == 'beheer' ? 'active' : '' ?>">Beheer</a>
            <?php } ?>
        </nav>

        <div class="header-right">
            <a class="avatar" href="instellingen.php" title="Instellingen"><?= htmlspecialchars($voorletters) ?></a>
        </div>
    <?php } ?>
</header>
