<?php

declare(strict_types=1);

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function post_string(string $name): string
{
    $value = $_POST[$name] ?? '';

    return is_string($value) ? $value : '';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** @return array{type: string, message: string}|null */
function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($message) ? $message : null;
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

/** @return array{id: int, email: string}|null */
function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;

    return is_array($user) ? $user : null;
}

/** @return array{id: int, email: string} */
function require_user(): array
{
    $user = current_user();
    if ($user === null) {
        flash('error', 'Entre para acessar seu cofre.');
        redirect('/login.php');
    }

    return $user;
}

function positive_int_query(string $name): int
{
    $value = filter_input(INPUT_GET, $name, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return is_int($value) ? $value : 0;
}
