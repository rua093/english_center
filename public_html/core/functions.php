<?php
declare(strict_types=1);

function e(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
	header('Location: ' . $path);
	exit;
}

function set_flash(string $key, string $message): void
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	$_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	if (!isset($_SESSION['flash'][$key])) {
		return null;
	}

	$message = (string) $_SESSION['flash'][$key];
	unset($_SESSION['flash'][$key]);
	return $message;
}

function format_money(float $amount): string
{
	return number_format($amount, 0, ',', '.') . ' đ';
}

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

function ui_btn_primary_classes(string $size = 'md'): string
{
	$sizeClass = match ($size) {
		'sm' => 'px-3 py-1.5 text-xs',
		'md' => 'px-4 py-2.5 text-sm',
		default => 'px-4 py-2.5 text-sm',
	};

	return 'inline-flex items-center justify-center rounded-xl bg-blue-700 ' . $sizeClass . ' font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800';
}

function ui_btn_secondary_classes(string $size = 'md'): string
{
	$sizeClass = match ($size) {
		'sm' => 'px-3 py-1.5 text-xs',
		'md' => 'px-4 py-2.5 text-sm',
		default => 'px-4 py-2.5 text-sm',
	};

	return 'inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white ' . $sizeClass . ' font-bold text-blue-700 transition hover:bg-slate-100';
}

function ui_btn_danger_classes(string $size = 'sm'): string
{
	$sizeClass = match ($size) {
		'sm' => 'px-3 py-1.5 text-xs',
		'md' => 'px-4 py-2.5 text-sm',
		default => 'px-3 py-1.5 text-xs',
	};

	return 'inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white ' . $sizeClass . ' font-bold text-rose-700 transition hover:bg-rose-50';
}

function ui_quick_action_link_classes(): string
{
	return 'inline-flex min-w-[120px] flex-col rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 leading-tight text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
}
