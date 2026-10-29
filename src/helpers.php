<?php

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $msg): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['_flash'][$type] = $msg;
}

/**
 * @return array<string, string>
 */
function getFlash(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return is_array($messages) ? $messages : [];
}

function csrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function verifyCsrfToken(mixed $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return is_string($token)
        && isset($_SESSION['_csrf_token'])
        && is_string($_SESSION['_csrf_token'])
        && hash_equals($_SESSION['_csrf_token'], $token);
}
