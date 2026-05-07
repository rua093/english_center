<?php
declare(strict_types=1);

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/security.php';

function ui_pagination_per_page_options(): array
{
	return [10, 20, 50, 100];
}

function ui_pagination_resolve_per_page(string $queryKey, int $defaultPerPage = 10): int
{
	$allowed = ui_pagination_per_page_options();
	$requested = (int) ($_GET[$queryKey] ?? $defaultPerPage);

	if (in_array($requested, $allowed, true)) {
		return $requested;
	}

	return $defaultPerPage;
}

function ui_format_date(?string $value, string $fallback = '—'): string
{
	$raw = trim((string) $value);
	if ($raw === '') {
		return $fallback;
	}

	try {
		$dt = new DateTimeImmutable($raw);
		return $dt->format('d/m/Y');
	} catch (Throwable) {
		return $raw;
	}
}

function ui_format_datetime(?string $value, string $fallback = '—'): string
{
	$raw = trim((string) $value);
	if ($raw === '') {
		return $fallback;
	}

	try {
		$dt = new DateTimeImmutable($raw);
		return $dt->format('d/m/Y H:i');
	} catch (Throwable) {
		return $raw;
	}
}

function ui_format_date_range(?string $startDate, ?string $endDate, string $fallback = 'Không giới hạn'): string
{
	$start = trim((string) $startDate);
	$end = trim((string) $endDate);

	if ($start !== '' && $end !== '') {
		return ui_format_date($start, $start) . ' - ' . ui_format_date($end, $end);
	}

	if ($start !== '') {
		return 'Từ ' . ui_format_date($start, $start);
	}

	if ($end !== '') {
		return 'Đến ' . ui_format_date($end, $end);
	}

	return $fallback;
}
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db_helper.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/api_helpers.php';
require_once __DIR__ . '/file_storage.php';
require_once __DIR__ . '/page_routes.php';
