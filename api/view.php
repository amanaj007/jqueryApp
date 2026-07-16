<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$profileId = filter_input(INPUT_GET, 'profile_id', FILTER_VALIDATE_INT);
if (!$profileId) {
    die('Missing profile_id');
}

$pdo = getPdo();
$statement = $pdo->prepare('SELECT first_name, last_name, email, headline, summary FROM Profile WHERE profile_id = :pid');
$statement->execute([':pid' => $profileId]);
$profile = $statement->fetch();
if (!$profile) {
    die('Bad value for profile_id');
}
$statement = $pdo->prepare('SELECT year, description FROM `Position` WHERE profile_id = :pid ORDER BY `rank`');
$statement->execute([':pid' => $profileId]);
$positions = $statement->fetchAll();

renderHeader('View Profile');
?>
<h1>Profile information</h1>
<p>First Name: <?= esc($profile['first_name']) ?></p>
<p>Last Name: <?= esc($profile['last_name']) ?></p>
<p>Email: <?= esc($profile['email']) ?></p>
<p>Headline:<br><?= esc($profile['headline']) ?></p>
<p>Summary:<br><?= esc($profile['summary']) ?></p>
<p>Position:</p>
<ul>
<?php foreach ($positions as $position): ?>
    <li><?= esc((string) $position['year']) ?>: <?= esc($position['description']) ?></li>
<?php endforeach; ?>
</ul>
<p><a href="index.php">Done</a></p>
<?php renderFooter();
