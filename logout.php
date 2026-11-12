<?php
require_once __DIR__ . '/includes/functions.php';

// Sessie leegmaken en de sessie-cookie weghalen
$_SESSION = [];
setcookie(session_name(), '', time() - 3600, '/');
session_destroy();

redirect('login.php');
