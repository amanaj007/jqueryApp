<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

clearAuthenticatedUser();
$_SESSION = [];
session_destroy();
session_start();
$_SESSION['success'] = 'Logged out';
header('Location: index.php');
exit;
