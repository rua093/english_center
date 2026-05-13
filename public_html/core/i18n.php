<?php
declare(strict_types=1);

const APP_SUPPORTED_LOCALES = ['vi', 'en'];
const APP_DEFAULT_LOCALE = 'vi';

function normalize_locale(string $locale): string
{
	$normalized = strtolower(trim($locale));
	return in_array($normalized, APP_SUPPORTED_LOCALES, true) ? $normalized : APP_DEFAULT_LOCALE;
}

function current_locale(): string
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	return normalize_locale((string) ($_SESSION['locale'] ?? APP_DEFAULT_LOCALE));
}

function set_locale(string $locale): void
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$_SESSION['locale'] = normalize_locale($locale);
}

function i18n_bootstrap(): void
{
	$queryLocale = (string) ($_GET['lang'] ?? '');
	if ($queryLocale !== '') {
		set_locale($queryLocale);
		return;
	}

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	if (!isset($_SESSION['locale'])) {
		$_SESSION['locale'] = APP_DEFAULT_LOCALE;
	}
}

function i18n_dictionary(string $locale): array
{
	static $cache = [];
	$normalized = normalize_locale($locale);
	if (isset($cache[$normalized])) {
		return $cache[$normalized];
	}

	$file = __DIR__ . '/../lang/' . $normalized . '.php';
	$dictionary = is_file($file) ? require $file : [];
	$cache[$normalized] = is_array($dictionary) ? $dictionary : [];

	return $cache[$normalized];
}

function t(string $key, array $replace = []): string
{
	$dictionary = i18n_dictionary(current_locale());
	$fallback = current_locale() === APP_DEFAULT_LOCALE ? [] : i18n_dictionary(APP_DEFAULT_LOCALE);
	$value = (string) ($dictionary[$key] ?? $fallback[$key] ?? $key);

	foreach ($replace as $name => $replacement) {
		$value = str_replace(':' . (string) $name, (string) $replacement, $value);
	}

	return $value;
}

function localized_current_url(string $locale): string
{
	$parts = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'));
	$path = (string) ($parts['path'] ?? '/');
	parse_str((string) ($parts['query'] ?? ''), $query);
	$query['lang'] = normalize_locale($locale);

	return $path . '?' . http_build_query($query);
}

