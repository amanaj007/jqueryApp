<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

requireLogin();
$profileId = filter_input(INPUT_GET, 'profile_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'profile_id', FILTER_VALIDATE_INT);
if (!$profileId) {
    die('Missing profile_id');
}

$pdo = getPdo();
$statement = $pdo->prepare('SELECT first_name, last_name, email, headline, summary FROM Profile WHERE profile_id = :pid AND user_id = :uid');
$statement->execute([':pid' => $profileId, ':uid' => $_SESSION['user_id']]);
$profile = $statement->fetch();
if (!$profile) {
    die('Bad value for profile_id');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = validateProfile($_POST) ?? validatePositions($_POST);
    if ($error !== null) {
        setFlash($error);
        header('Location: edit.php?profile_id=' . $profileId);
        exit;
    }

    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare('UPDATE Profile SET first_name = :first_name, last_name = :last_name, email = :email, headline = :headline, summary = :summary WHERE profile_id = :pid AND user_id = :uid');
        $statement->execute([
            ':first_name' => trim((string) $_POST['first_name']),
            ':last_name' => trim((string) $_POST['last_name']),
            ':email' => trim((string) $_POST['email']),
            ':headline' => trim((string) $_POST['headline']),
            ':summary' => trim((string) $_POST['summary']),
            ':pid' => $profileId,
            ':uid' => $_SESSION['user_id'],
        ]);
        $statement = $pdo->prepare('DELETE FROM `Position` WHERE profile_id = :pid');
        $statement->execute([':pid' => $profileId]);
        savePositions($pdo, $profileId, $_POST);
        $pdo->commit();
        setSuccess('Profile updated');
        header('Location: index.php');
        exit;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

$statement = $pdo->prepare('SELECT year, description FROM `Position` WHERE profile_id = :pid ORDER BY `rank`');
$statement->execute([':pid' => $profileId]);
$positions = $statement->fetchAll();

renderHeader('Edit Profile');
?>
<h1>Editing Profile for <?= esc($_SESSION['name'] ?? '') ?></h1>
<?php displayFlash(); ?>
<form method="post">
    <input type="hidden" name="profile_id" value="<?= (int) $profileId ?>">
    <p>First Name: <input type="text" name="first_name" size="60" value="<?= esc($profile['first_name']) ?>"></p>
    <p>Last Name: <input type="text" name="last_name" size="60" value="<?= esc($profile['last_name']) ?>"></p>
    <p>Email: <input type="text" name="email" size="30" value="<?= esc($profile['email']) ?>"></p>
    <p>Headline:<br><input type="text" name="headline" size="80" value="<?= esc($profile['headline']) ?>"></p>
    <p>Summary:<br><textarea name="summary" rows="8" cols="80"><?= esc($profile['summary']) ?></textarea></p>
    <p>Position: <input class="btn btn-default" type="button" id="addPos" value="+"></p>
    <div id="position_fields">
    <?php $positionNumber = 0; foreach ($positions as $position): $positionNumber++; ?>
        <div id="position<?= $positionNumber ?>">
            <p>Year: <input type="text" name="year<?= $positionNumber ?>" value="<?= esc((string) $position['year']) ?>"> <input class="btn btn-default" type="button" value="-" onclick="$('#position<?= $positionNumber ?>').remove(); return false;"></p>
            <textarea name="desc<?= $positionNumber ?>" rows="8" cols="80"><?= esc($position['description']) ?></textarea>
        </div>
    <?php endforeach; ?>
    </div>
    <p><input class="btn btn-primary" type="submit" value="Save"> <a class="btn btn-default" href="index.php">Cancel</a></p>
</form>
<script>
let countPos = <?= $positionNumber ?>;
$('#addPos').click(function () {
    if (countPos >= 9) {
        alert('Maximum of nine position entries exceeded');
        return;
    }
    countPos++;
    $('#position_fields').append('<div id="position' + countPos + '"><p>Year: <input type="text" name="year' + countPos + '" value=""> <input class="btn btn-default" type="button" value="-" onclick="$(\'#position' + countPos + '\').remove(); return false;"></p><textarea name="desc' + countPos + '" rows="8" cols="80"></textarea></div>');
});
</script>
<?php renderFooter();
