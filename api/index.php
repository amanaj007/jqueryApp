<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$pdo = getPdo();
$statement = $pdo->query('SELECT profile_id, first_name, last_name, headline FROM Profile ORDER BY first_name, last_name');
$profiles = $statement->fetchAll();

renderHeader('Resume Database');
?>
<h1>Resume Database</h1>
<?php if (isset($_SESSION['name'])): ?>
<p>Welcome, <?= esc($_SESSION['name']) ?>. <a href="logout.php">Logout</a></p>
<p><a class="btn btn-primary" href="add.php">Add New Entry</a></p>
<?php else: ?>
<p><a href="login.php">Please log in</a></p>
<?php endif; ?>
<?php displayFlash(); ?>
<table class="table table-striped">
    <thead><tr><th>Name</th><th>Headline</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($profiles as $profile): ?>
        <tr>
            <td><?= esc($profile['first_name'] . ' ' . $profile['last_name']) ?></td>
            <td><?= esc($profile['headline']) ?></td>
            <td><a href="view.php?profile_id=<?= (int) $profile['profile_id'] ?>">View</a><?php if (isset($_SESSION['user_id'])): ?> | <a href="edit.php?profile_id=<?= (int) $profile['profile_id'] ?>">Edit</a> | <a href="delete.php?profile_id=<?= (int) $profile['profile_id'] ?>">Delete</a><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php renderFooter();
