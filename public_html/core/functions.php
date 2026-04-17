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
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db_helper.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/api_helpers.php';
require_once __DIR__ . '/file_storage.php';
require_once __DIR__ . '/page_routes.php';
