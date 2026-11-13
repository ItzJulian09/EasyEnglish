<?php

require_once __DIR__ . '/includes/functions.php';

use App\UserRepository;

$language = $_GET['taal'] ?? 'nl';
if (!in_array($language, ['nl', 'en'], true)) {
    $language = 'nl';
}

$_SESSION['language'] = $language;

// Ingelogd? Dan slaan we de keuze ook op in de database.
$user = currentUser();
if ($user) {
    $userRepository = new UserRepository();
    $userRepository->updateLanguage((int) $user['id'], $language);
}

// Alleen terugsturen naar een pagina op onze eigen site
$returnUrl = $_GET['terug'] ?? 'index.php';
$isOwnSite = str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//');
redirect($isOwnSite ? $returnUrl : 'index.php');
