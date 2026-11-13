<?php


session_start();

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/WordList.php';
require_once __DIR__ . '/../classes/Attempt.php';


$user = false;
if (isset($_SESSION['user_id'])) {
    $userClass = new User();
    $user = $userClass->getById($_SESSION['user_id']);
}


$isAdmin = $user && !empty($user['is_admin']);


function toonMelding()
{
    if (isset($_SESSION['melding'])) {
        echo '<div class="flash">' . htmlspecialchars($_SESSION['melding']) . '</div>';
        unset($_SESSION['melding']);
    }
}
