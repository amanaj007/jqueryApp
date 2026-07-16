<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = validateProfile($_POST) ?? validatePositions($_POST);
    if ($error !== null) {
        setFlash($error);
        header('Location: add.php');
        exit;
    }

    $pdo = getPdo();
    $pdo->beginTransaction();
    try {
        $statement = $pdo->prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary) VALUES (:uid, :first_name, :last_name, :email, :headline, :summary)');
        $statement->execute([
            ':uid' => $_SESSION['user_id'],
            ':first_name' => trim((string) $_POST['first_name']),
            ':last_name' => trim((string) $_POST['last_name']),
            ':email' => trim((string) $_POST['email']),
            ':headline' => trim((string) $_POST['headline']),
            ':summary' => trim((string) $_POST['summary']),
        ]);
        savePositions($pdo, (int) $pdo->lastInsertId(), $_POST);
        $pdo->commit();
        $_SESSION['success'] = 'Profile added';
        header('Location: index.php');
        exit;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

renderHeader('Add Profile');
?>
<h1>Adding Profile for <?= esc($_SESSION['name'] ?? '') ?></h1>
<?php displayFlash(); ?>
<form method="post">
    <p>First Name: <input type="text" name="first_name" size="60"></p>
    <p>Last Name: <input type="text" name="last_name" size="60"></p>
    <p>Email: <input type="text" name="email" size="30"></p>
    <p>Headline:<br><input type="text" name="headline" size="80"></p>
    <p>Summary:<br><textarea name="summary" rows="8" cols="80"></textarea></p>
    <p>Position: <input class="btn btn-default" type="button" id="addPos" value="+"></p>
    <div id="position_fields"></div>
    <p><input class="btn btn-primary" type="submit" value="Add"> <a class="btn btn-default" href="index.php">Cancel</a></p>
</form>
<script>
let countPos = 0;
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
