<?php
declare(strict_types=1);

function upload_storage_dir(): string
{
	$configuredPath = defined('UPLOAD_STORAGE_PATH') ? (string) UPLOAD_STORAGE_PATH : '';
	if ($configuredPath !== '') {
		return $configuredPath;
	}

	return BASE_PATH . '/assets/uploads';
}

function upload_public_base_path(): string
{
	$configuredPath = defined('UPLOAD_PUBLIC_BASE_PATH') ? (string) UPLOAD_PUBLIC_BASE_PATH : '';
	if ($configuredPath !== '') {
		return rtrim($configuredPath, '/');
	}

	return '/assets/uploads';
}

function store_uploaded_file(array $file, string $prefix): ?string
{
	if (empty($file['name']) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
		return null;
	}

	if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
		return null;
	}

	$uploadDir = upload_storage_dir();
	if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
		return null;
	}

	$originalName = basename((string) $file['name']);
	$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'upload.bin';
	$storedName = sprintf('%s-%d-%s', $prefix, time(), $safeName);
	$targetPath = $uploadDir . '/' . $storedName;

	if (!is_uploaded_file((string) ($file['tmp_name'] ?? '')) || !move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
		return null;
	}

	return upload_public_base_path() . '/' . $storedName;
}
