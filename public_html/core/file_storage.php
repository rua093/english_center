<?php
declare(strict_types=1);

function upload_storage_dir(?string $subdir = null): string
{
	$configuredPath = defined('UPLOAD_STORAGE_PATH') ? (string) UPLOAD_STORAGE_PATH : '';
	if ($subdir !== null && trim($subdir) !== '') {
		$subdir = trim(str_replace('\\', '/', $subdir), '/');
	}

	if ($configuredPath !== '') {
		return $subdir !== null && $subdir !== '' ? rtrim($configuredPath, '/') . '/' . $subdir : $configuredPath;
	}

	return BASE_PATH . '/assets/uploads' . ($subdir !== null && $subdir !== '' ? '/' . $subdir : '');
}

function upload_public_base_path(): string
{
	$configuredPath = defined('UPLOAD_PUBLIC_BASE_PATH') ? (string) UPLOAD_PUBLIC_BASE_PATH : '';
	if ($configuredPath !== '') {
		return rtrim($configuredPath, '/');
	}

	return '/assets/uploads';
}

function normalize_public_file_url(?string $path): string
{
	$normalized = trim((string) $path);
	if ($normalized === '') {
		return '';
	}

	$normalized = str_replace('\\', '/', $normalized);
	$lower = strtolower($normalized);

	if (
		str_starts_with($lower, 'http://') ||
		str_starts_with($lower, 'https://') ||
		str_starts_with($lower, '//') ||
		str_starts_with($lower, 'data:') ||
		str_starts_with($lower, 'blob:')
	) {
		return $normalized;
	}

	if (str_starts_with($normalized, '/')) {
		return $normalized;
	}

	return upload_public_base_path() . '/' . ltrim($normalized, '/');
}

function store_uploaded_file(array $file, string $prefix, ?string $subdir = null): ?string
{
	if (empty($file['name']) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
		return null;
	}

	if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
		return null;
	}

	$uploadDir = upload_storage_dir($subdir);
	if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
		return null;
	}

	$originalName = basename((string) $file['name']);
	$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'upload.bin';
	$storedName = sprintf('%s-%d-%s', $prefix, time(), $safeName);
	$targetPath = $uploadDir . '/' . $storedName;
	$tmpPath = (string) ($file['tmp_name'] ?? '');

	if ($tmpPath === '' || !is_file($tmpPath)) {
		return null;
	}

	$moveSucceeded = false;
	if (is_uploaded_file($tmpPath)) {
		$moveSucceeded = move_uploaded_file($tmpPath, $targetPath);
	}

	if (!$moveSucceeded) {
		$moveSucceeded = @rename($tmpPath, $targetPath);
	}

	if (!$moveSucceeded && !copy($tmpPath, $targetPath)) {
		return null;
	}

	$publicBase = upload_public_base_path();
	if ($subdir !== null && trim($subdir) !== '') {
		$normalizedSubdir = trim(str_replace('\\', '/', $subdir), '/');
		return $publicBase . '/' . $normalizedSubdir . '/' . $storedName;
	}

	return $publicBase . '/' . $storedName;
}
