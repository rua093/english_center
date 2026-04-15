<?php
declare(strict_types=1);

require_once __DIR__ . '/response.php';

function csrf_token(): string
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	return (string) $_SESSION['csrf_token'];
}

function csrf_input(): string
{
	return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function validate_csrf_token(string $token): bool
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
	if ($sessionToken === '' || $token === '') {
		return false;
	}

	return hash_equals($sessionToken, $token);
}

function rotate_csrf_token(): void
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function request_csrf_token(): string
{
	$token = (string) ($_POST['_csrf'] ?? '');
	if ($token !== '') {
		return $token;
	}

	$headerToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
	if ($headerToken !== '') {
		return $headerToken;
	}

	return (string) ($_SERVER['HTTP_X_CSRF'] ?? '');
}

function safe_referer_path(string $referer): string
{
	if ($referer === '') {
		return '';
	}

	$parts = parse_url($referer);
	if ($parts === false) {
		return '';
	}

	$path = (string) ($parts['path'] ?? '');
	if ($path === '' || $path[0] !== '/') {
		return '';
	}

	$query = (string) ($parts['query'] ?? '');
	return $query !== '' ? ($path . '?' . $query) : $path;
}
