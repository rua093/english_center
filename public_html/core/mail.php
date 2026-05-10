<?php
declare(strict_types=1);

require_once __DIR__ . '/api_helpers.php';
require_once __DIR__ . '/response.php';

function mail_is_enabled(): bool
{
    return defined('MAIL_ENABLED') && MAIL_ENABLED === true;
}

function mail_is_configured(): bool
{
    return mail_is_enabled()
        && trim((string) MAIL_HOST) !== ''
        && (int) MAIL_PORT > 0
        && trim((string) MAIL_FROM_ADDRESS) !== '';
}

function mail_from_address(): string
{
    return trim((string) MAIL_FROM_ADDRESS);
}

function mail_from_name(): string
{
    $name = trim((string) MAIL_FROM_NAME);
    return $name !== '' ? $name : (defined('APP_NAME') ? (string) APP_NAME : 'Mailer');
}

function mail_reply_to_address(): string
{
    $replyTo = trim((string) MAIL_REPLY_TO_ADDRESS);
    return $replyTo !== '' ? $replyTo : mail_from_address();
}

function mail_reply_to_name(): string
{
    $replyToName = trim((string) MAIL_REPLY_TO_NAME);
    return $replyToName !== '' ? $replyToName : mail_from_name();
}

function mail_internal_notification_recipients(): array
{
    $configured = trim((string) MAIL_INTERNAL_NOTIFICATION_RECIPIENTS);
    if ($configured === '') {
        return [];
    }

    $recipients = [];
    foreach (explode(',', $configured) as $entry) {
        $email = strtolower(trim($entry));
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            continue;
        }

        $recipients[] = $email;
    }

    return array_values(array_unique($recipients));
}

function mail_abs_url(string $path = ''): string
{
    $baseUrl = trim((string) APP_BASE_URL);
    if ($baseUrl !== '') {
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        $host = 'localhost';
    }

    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

function mail_login_url(): string
{
    return mail_abs_url(ltrim(page_url('login'), '/'));
}

function mail_forgot_password_url(): string
{
    return mail_abs_url(ltrim(page_url('forgot-password'), '/'));
}

function mail_html_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function mail_text_lines(array $lines): string
{
    $normalized = [];
    foreach ($lines as $line) {
        $trimmed = trim((string) $line);
        if ($trimmed === '') {
            continue;
        }

        $normalized[] = $trimmed;
    }

    return implode("\n", $normalized);
}

function mail_signed_token(array $payload): string
{
    return api_encode_payload($payload);
}

function mail_read_signed_token(string $token): ?array
{
    return api_decode_payload($token);
}
