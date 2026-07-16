<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

session_start();

function authenticationKey(): string
{
    return (string) (getenv('DB_PASSWORD') ?: 'local-development-key');
}

function storeAuthenticatedUser(int $userId, string $name): void
{
    $payload = rtrim(strtr(base64_encode(json_encode(['id' => $userId, 'name' => $name], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', $payload, authenticationKey());
    setcookie('resume_auth', $payload . '.' . $signature, [
        'expires' => time() + 86400,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_SESSION['user_id'] = $userId;
    $_SESSION['name'] = $name;
}

function restoreAuthenticatedUser(): void
{
    if (isset($_SESSION['user_id']) || !isset($_COOKIE['resume_auth'])) {
        return;
    }

    [$payload, $signature] = array_pad(explode('.', (string) $_COOKIE['resume_auth'], 2), 2, '');
    if ($payload === '' || $signature === '' || !hash_equals(hash_hmac('sha256', $payload, authenticationKey()), $signature)) {
        return;
    }

    $decoded = base64_decode(strtr($payload, '-_', '+/') . str_repeat('=', (4 - strlen($payload) % 4) % 4), true);
    if ($decoded === false) {
        return;
    }

    try {
        $user = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return;
    }

    if (!is_array($user) || !isset($user['id'], $user['name']) || !is_int($user['id']) || !is_string($user['name'])) {
        return;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
}

function clearAuthenticatedUser(): void
{
    setcookie('resume_auth', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_SESSION['user_id'], $_SESSION['name']);
}

restoreAuthenticatedUser();

function esc(?string $value): string
{
    return htmlentities($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function setFlash(string $message): void
{
    $_SESSION['error'] = $message;
}

function displayFlash(): void
{
    if (isset($_SESSION['error'])) {
        echo '<p class="alert alert-danger">' . esc($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<p class="alert alert-success">' . esc($_SESSION['success']) . '</p>';
        unset($_SESSION['success']);
    }
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        die('ACCESS DENIED');
    }
}

function validateProfile(array $input): ?string
{
    $fields = ['first_name', 'last_name', 'email', 'headline', 'summary'];
    foreach ($fields as $field) {
        if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
            return 'All fields are required';
        }
    }
    if (strpos((string) $input['email'], '@') === false) {
        return 'Email address must contain @';
    }
    return null;
}

function validatePositions(array $input): ?string
{
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($input['year' . $i]) && !isset($input['desc' . $i])) {
            continue;
        }
        $year = trim((string) ($input['year' . $i] ?? ''));
        $description = trim((string) ($input['desc' . $i] ?? ''));
        if ($year === '' || $description === '') {
            return 'All fields are required';
        }
        if (!is_numeric($year)) {
            return 'Position year must be numeric';
        }
    }
    return null;
}

function savePositions(PDO $pdo, int $profileId, array $input): void
{
    $statement = $pdo->prepare('INSERT INTO `Position` (profile_id, `rank`, year, description) VALUES (:pid, :rank, :year, :description)');
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($input['year' . $i]) || !isset($input['desc' . $i])) {
            continue;
        }
        $statement->execute([
            ':pid' => $profileId,
            ':rank' => $rank,
            ':year' => trim((string) $input['year' . $i]),
            ':description' => trim((string) $input['desc' . $i]),
        ]);
        $rank++;
    }
}

function renderHeader(string $title): void
{
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Aman Kumar Jaiswal - 4c414f1e - ' . esc($title) . '</title><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous"><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous"><script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script></head><body><main class="container">';
}

function renderFooter(): void
{
    echo '</main></body></html>';
}
