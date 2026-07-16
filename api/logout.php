<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

clearAuthenticatedUser();
$_SESSION = [];
session_destroy();
session_start();
setSuccess('Logged out');
header('Location: index.php');
exit;
