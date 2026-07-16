<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['pass'] ?? '');
    if ($email === '' || $password === '') {
        setFlash('Email and password are required');
        header('Location: login.php');
        exit;
    }

    $statement = getPdo()->prepare('SELECT user_id, name FROM users WHERE email = :email AND password = :password LIMIT 1');
    $statement->execute([
        ':email' => $email,
        ':password' => md5($password),
    ]);
    $user = $statement->fetch();
    if (!$user) {
        setFlash('Incorrect password');
        header('Location: login.php');
        exit;
    }

    storeAuthenticatedUser((int) $user['user_id'], $user['name']);
    setSuccess('Logged in');
    header('Location: index.php');
    exit;
}

renderHeader('Login');
?>
<h1>Please Log In</h1>
<?php displayFlash(); ?>
<form method="post">
    <p>Email <input type="text" name="email" size="40"></p>
    <p>Password <input type="password" name="pass" size="40"></p>
    <input class="btn btn-primary" type="submit" value="Log In">
    <a class="btn btn-default" href="index.php">Cancel</a>
</form>
<?php renderFooter();
