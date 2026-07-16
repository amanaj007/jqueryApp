<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

requireLogin();
$profileId = filter_input(INPUT_GET, 'profile_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'profile_id', FILTER_VALIDATE_INT);
if (!$profileId) {
    die('Missing profile_id');
}

$pdo = getPdo();
$statement = $pdo->prepare('SELECT first_name, last_name FROM Profile WHERE profile_id = :pid AND user_id = :uid');
$statement->execute([':pid' => $profileId, ':uid' => $_SESSION['user_id']]);
$profile = $statement->fetch();
if (!$profile) {
    die('Bad value for profile_id');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $pdo->prepare('DELETE FROM Profile WHERE profile_id = :pid AND user_id = :uid');
    $statement->execute([':pid' => $profileId, ':uid' => $_SESSION['user_id']]);
    $_SESSION['success'] = 'Profile deleted';
    header('Location: index.php');
    exit;
}

renderHeader('Delete Profile');
?>
<h1>Delete Profile</h1>
<p>Are you sure you want to delete <?= esc($profile['first_name'] . ' ' . $profile['last_name']) ?>?</p>
<form method="post">
    <input type="hidden" name="profile_id" value="<?= (int) $profileId ?>">
    <input class="btn btn-danger" type="submit" value="Delete">
    <a class="btn btn-default" href="index.php">Cancel</a>
</form>
<?php renderFooter();
