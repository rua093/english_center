<?php
declare(strict_types=1);

if (!defined('ASSET_VERSION')) {
	require_once __DIR__ . '/../config.php';
}

function getVersion(string $type, string $fileName): string
{
	$normalizedType = strtolower(trim($type));
	$normalizedFile = ltrim(trim($fileName), '/');

	if ($normalizedType === '' || $normalizedFile === '') {
		return '';
	}

	if (str_contains($normalizedFile, '..')) {
		return '';
	}

	$allowedTypes = ['css', 'js', 'img', 'images', 'fonts', 'uploads'];
	if (!in_array($normalizedType, $allowedTypes, true)) {
		return '';
	}

	if (!preg_match('/^[A-Za-z0-9._\/-]+$/', $normalizedFile)) {
		return '';
	}

	return sprintf('/assets/%s/%s?v=%s', $normalizedType, $normalizedFile, rawurlencode((string) ASSET_VERSION));
}
