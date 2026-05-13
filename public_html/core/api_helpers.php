<?php
declare(strict_types=1);

require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/validation.php';

i18n_bootstrap();

function api_expects_json(): bool
{
	$accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
	$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
	$requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
	$format = strtolower((string) ($_GET['format'] ?? $_POST['format'] ?? ''));

	return str_contains($accept, 'application/json')
		|| str_contains($contentType, 'application/json')
		|| $requestedWith === 'xmlhttprequest'
		|| $format === 'json';
}

function api_response(string $status, string $message = '', array $data = [], array $error = [], int $httpCode = 200): never
{
	http_response_code($httpCode);
	header('Content-Type: application/json; charset=utf-8');

	echo json_encode([
		'status' => $status,
		'message' => $message,
		'data' => $data,
		'error' => $error,
	], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

	exit;
}

function api_success(string $message = 'OK', array $data = [], int $httpCode = 200): never
{
	api_response('success', $message, $data, [], $httpCode);
}

function api_error(string $message, array $error = [], int $httpCode = 400): never
{
	api_response('error', $message, [], $error, $httpCode);
}

function api_require_post(string $redirectPath): void
{
	if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST') {
		return;
	}

	if (api_expects_json()) {
		api_error(t('api.method_not_allowed'), ['code' => 'METHOD_NOT_ALLOWED'], 405);
	}

	redirect($redirectPath);
}

function api_fail_invalid_csrf(string $redirectPath = '/?page=home'): never
{
	if (api_expects_json()) {
		api_error(t('api.invalid_csrf'), ['code' => 'INVALID_CSRF'], 419);
	}

	set_flash('error', t('flash.invalid_request'));
	$refererPath = safe_referer_path((string) ($_SERVER['HTTP_REFERER'] ?? ''));
	if ($refererPath !== '') {
		redirect($refererPath);
	}

	redirect($redirectPath);
}

function api_guard_login(): void
{
	require_login();
}

function api_guard_admin_or_staff(): void
{
	require_admin_or_staff();
}

function api_guard_permission(string $permission): void
{
	require_permission($permission);
}

function api_run_action(string $actionName, callable $handler, string $fallbackRedirect = '/?page=home'): never
{
	try {
		$handler();
	} catch (Throwable $exception) {
		app_log('error', 'API action failed', [
			'action' => $actionName,
			'error' => $exception->getMessage(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine(),
			'request_method' => (string) ($_SERVER['REQUEST_METHOD'] ?? ''),
			'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
			'user_id' => (int) ((auth_user()['id'] ?? 0)),
		]);

		if (api_expects_json()) {
			api_error(t('api.internal_server_error'), ['code' => 'SERVER_ERROR'], 500);
		}

		set_flash('error', t('flash.internal_error'));
		redirect($fallbackRedirect);
	}

	if (api_expects_json()) {
		api_success('OK');
	}

	redirect($fallbackRedirect);
}

function api_encode_payload(array $payload): string
{
	$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if (!is_string($json)) {
		return '';
	}

	$signature = hash_hmac('sha256', $json, api_secret_key());
	$token = base64_encode($json . '|' . $signature);
	return rtrim(strtr($token, '+/', '-_'), '=');
}

function api_decode_payload(string $token): ?array
{
	if ($token === '') {
		return null;
	}

	$decoded = base64_decode(strtr($token, '-_', '+/'), true);
	if (!is_string($decoded)) {
		return null;
	}

	$separatorPos = strrpos($decoded, '|');
	if ($separatorPos === false) {
		return null;
	}

	$json = substr($decoded, 0, $separatorPos);
	$signature = substr($decoded, $separatorPos + 1);
	$expected = hash_hmac('sha256', $json, api_secret_key());

	if (!hash_equals($expected, $signature)) {
		return null;
	}

	$data = json_decode($json, true);
	return is_array($data) ? $data : null;
}

function api_secret_key(): string
{
	if (defined('APP_SECRET') && APP_SECRET !== '') {
		return APP_SECRET;
	}

	return hash('sha256', __FILE__ . php_uname());
}
