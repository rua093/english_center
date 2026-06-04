<?php
declare(strict_types=1);

function app_file_storage_set_last_error(string $message, array $context = []): void
{
	$GLOBALS['app_file_storage_last_error'] = [
		'message' => $message,
		'context' => $context,
	];
}

function app_file_storage_last_error_message(): string
{
	$lastError = $GLOBALS['app_file_storage_last_error'] ?? null;
	if (!is_array($lastError)) {
		return '';
	}

	return trim((string) ($lastError['message'] ?? ''));
}

function app_file_storage_last_error_context(): array
{
	$lastError = $GLOBALS['app_file_storage_last_error'] ?? null;
	if (!is_array($lastError)) {
		return [];
	}

	$context = $lastError['context'] ?? [];
	return is_array($context) ? $context : [];
}

function app_file_storage_clear_last_error(): void
{
	unset($GLOBALS['app_file_storage_last_error']);
}

function app_file_storage_driver(): string
{
	$driver = defined('FILE_STORAGE_DRIVER') ? strtolower(trim((string) FILE_STORAGE_DRIVER)) : 'local';
	return $driver !== '' ? $driver : 'local';
}

function app_file_storage_uses_s3(): bool
{
	return app_file_storage_driver() === 's3';
}

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
	if (app_file_storage_uses_s3()) {
		return s3_public_base_url();
	}

	return local_upload_public_base_path();
}

function local_upload_public_base_path(): string
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

function is_trusted_uploaded_file_url(?string $path): bool
{
	$normalized = normalize_public_file_url($path);
	if ($normalized === '') {
		return false;
	}

	$publicBase = rtrim(upload_public_base_path(), '/');
	if ($publicBase !== '' && str_starts_with($normalized, $publicBase . '/')) {
		return true;
	}

	$localBase = rtrim(local_upload_public_base_path(), '/');
	return $localBase !== '' && str_starts_with($normalized, $localBase . '/');
}

function s3_endpoint(): string
{
	return rtrim(trim((string) (defined('S3_ENDPOINT') ? S3_ENDPOINT : '')), '/');
}

function s3_bucket(): string
{
	return trim((string) (defined('S3_BUCKET') ? S3_BUCKET : ''));
}

function s3_region(): string
{
	$region = trim((string) (defined('S3_REGION') ? S3_REGION : 'auto'));
	return $region !== '' ? $region : 'auto';
}

function s3_access_key(): string
{
	return trim((string) (defined('S3_ACCESS_KEY') ? S3_ACCESS_KEY : ''));
}

function s3_secret_key(): string
{
	return trim((string) (defined('S3_SECRET_KEY') ? S3_SECRET_KEY : ''));
}

function s3_use_path_style(): bool
{
	return defined('S3_USE_PATH_STYLE') ? (bool) S3_USE_PATH_STYLE : true;
}

function s3_object_acl(): string
{
	return trim((string) (defined('S3_OBJECT_ACL') ? S3_OBJECT_ACL : ''));
}

function s3_public_base_url(): string
{
	$configuredBaseUrl = trim((string) (defined('S3_PUBLIC_BASE_URL') ? S3_PUBLIC_BASE_URL : ''));
	if ($configuredBaseUrl !== '') {
		return rtrim($configuredBaseUrl, '/');
	}

	$endpoint = s3_endpoint();
	$bucket = s3_bucket();
	if ($endpoint === '' || $bucket === '') {
		return '';
	}

	if (s3_use_path_style()) {
		return $endpoint . '/' . rawurlencode($bucket);
	}

	$parts = parse_url($endpoint);
	if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
		return $endpoint . '/' . rawurlencode($bucket);
	}

	$host = $bucket . '.' . $parts['host'];
	$port = isset($parts['port']) ? ':' . $parts['port'] : '';
	$path = isset($parts['path']) ? rtrim($parts['path'], '/') : '';

	return $parts['scheme'] . '://' . $host . $port . $path;
}

function s3_is_configured(): bool
{
	return s3_endpoint() !== ''
		&& s3_bucket() !== ''
		&& s3_access_key() !== ''
		&& s3_secret_key() !== '';
}

function s3_normalize_object_key(string $objectKey): string
{
	return ltrim(str_replace('\\', '/', $objectKey), '/');
}

function s3_encode_object_key(string $objectKey): string
{
	$segments = array_map('rawurlencode', explode('/', s3_normalize_object_key($objectKey)));
	return implode('/', $segments);
}

function s3_object_public_url(string $objectKey): string
{
	$baseUrl = upload_public_base_path();
	return rtrim($baseUrl, '/') . '/' . s3_encode_object_key($objectKey);
}

function app_uploaded_object_manifest_enabled(): bool
{
	return app_file_storage_uses_s3() && s3_is_configured();
}

function app_uploaded_object_manifest_table_name(): string
{
	return 'uploaded_objects';
}

function app_uploaded_object_manifest_connection(): ?PDO
{
	if (!app_uploaded_object_manifest_enabled()) {
		return null;
	}

	if (!class_exists('Database', false)) {
		require_once __DIR__ . '/database.php';
	}

	return Database::connection();
}

function app_uploaded_object_manifest_ensure_schema(?PDO $pdo = null): bool
{
	$pdo = $pdo ?? app_uploaded_object_manifest_connection();
	if (!$pdo instanceof PDO) {
		return false;
	}

	$tableName = app_uploaded_object_manifest_table_name();
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS `{$tableName}` (
			`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`storage_driver` VARCHAR(32) NOT NULL DEFAULT 's3',
			`object_key` VARCHAR(500) NOT NULL,
			`public_url` VARCHAR(1000) NOT NULL,
			`preset_key` VARCHAR(100) DEFAULT NULL,
			`status` ENUM('draft','attached','deleted') NOT NULL DEFAULT 'draft',
			`created_by_user_id` BIGINT UNSIGNED DEFAULT NULL,
			`attached_by_user_id` BIGINT UNSIGNED DEFAULT NULL,
			`deleted_by_user_id` BIGINT UNSIGNED DEFAULT NULL,
			`context_json` LONGTEXT DEFAULT NULL,
			`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`last_seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`attached_at` DATETIME DEFAULT NULL,
			`deleted_at` DATETIME DEFAULT NULL,
			`expires_at` DATETIME DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uq_uploaded_objects_object_key` (`object_key`),
			KEY `idx_uploaded_objects_status_expires` (`status`, `expires_at`, `id`),
			KEY `idx_uploaded_objects_status_created` (`status`, `created_at`, `id`),
			KEY `idx_uploaded_objects_public_url` (`public_url`(191))
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
	);

	return true;
}

function app_uploaded_object_manifest_current_user_id(): ?int
{
	if (!function_exists('auth_user')) {
		return null;
	}

	$user = auth_user();
	$userId = (int) ($user['id'] ?? 0);
	return $userId > 0 ? $userId : null;
}

function app_uploaded_object_manifest_encode_context(array $context): ?string
{
	if ($context === []) {
		return null;
	}

	$json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	return is_string($json) ? $json : null;
}

function app_uploaded_object_manifest_log_warning(string $message, array $context = []): void
{
	if (function_exists('app_log')) {
		app_log('warning', $message, $context);
	}
}

function app_uploaded_object_manifest_record_draft(
	string $presetKey,
	string $objectKey,
	string $publicUrl,
	array $context = [],
	int $ttlHours = 24
): bool {
	$pdo = app_uploaded_object_manifest_connection();
	if (!$pdo instanceof PDO) {
		return false;
	}

	try {
		app_uploaded_object_manifest_ensure_schema($pdo);

		$ttlHours = max(1, $ttlHours);
		$contextJson = app_uploaded_object_manifest_encode_context($context);
		$userId = app_uploaded_object_manifest_current_user_id();

		$stmt = $pdo->prepare(
			'INSERT INTO `' . app_uploaded_object_manifest_table_name() . '` (
				storage_driver,
				object_key,
				public_url,
				preset_key,
				status,
				created_by_user_id,
				context_json,
				created_at,
				last_seen_at,
				expires_at,
				attached_at,
				deleted_at,
				attached_by_user_id,
				deleted_by_user_id
			) VALUES (
				:s3_driver,
				:object_key,
				:public_url,
				:preset_key,
				:draft_status,
				:created_by_user_id,
				:context_json,
				NOW(),
				NOW(),
				DATE_ADD(NOW(), INTERVAL ' . $ttlHours . ' HOUR),
				NULL,
				NULL,
				NULL,
				NULL
			)
			ON DUPLICATE KEY UPDATE
				public_url = VALUES(public_url),
				preset_key = VALUES(preset_key),
				status = VALUES(status),
				context_json = VALUES(context_json),
				last_seen_at = NOW(),
				expires_at = VALUES(expires_at),
				deleted_at = NULL,
				deleted_by_user_id = NULL'
		);
		$stmt->execute([
			's3_driver' => 's3',
			'object_key' => s3_normalize_object_key($objectKey),
			'public_url' => $publicUrl,
			'preset_key' => trim($presetKey) !== '' ? trim($presetKey) : null,
			'draft_status' => 'draft',
			'created_by_user_id' => $userId,
			'context_json' => $contextJson,
		]);

		return true;
	} catch (Throwable $exception) {
		app_uploaded_object_manifest_log_warning('Unable to record uploaded object draft.', [
			'object_key' => $objectKey,
			'public_url' => $publicUrl,
			'error' => $exception->getMessage(),
		]);
		return false;
	}
}

function app_uploaded_object_manifest_mark_attached(?string $path, array $context = []): bool
{
	$normalized = normalize_public_file_url($path);
	$objectKey = s3_object_key_from_public_url($normalized);
	if ($normalized === '' || $objectKey === null) {
		return false;
	}

	$pdo = app_uploaded_object_manifest_connection();
	if (!$pdo instanceof PDO) {
		return false;
	}

	try {
		app_uploaded_object_manifest_ensure_schema($pdo);

		$userId = app_uploaded_object_manifest_current_user_id();
		$contextJson = app_uploaded_object_manifest_encode_context($context);

		$stmt = $pdo->prepare(
			'INSERT INTO `' . app_uploaded_object_manifest_table_name() . '` (
				storage_driver,
				object_key,
				public_url,
				status,
				attached_by_user_id,
				context_json,
				created_at,
				last_seen_at,
				attached_at,
				expires_at,
				deleted_at,
				deleted_by_user_id
			) VALUES (
				:s3_driver,
				:object_key,
				:public_url,
				:attached_status,
				:attached_by_user_id,
				:context_json,
				NOW(),
				NOW(),
				NOW(),
				NULL,
				NULL,
				NULL
			)
			ON DUPLICATE KEY UPDATE
				public_url = VALUES(public_url),
				status = VALUES(status),
				attached_by_user_id = VALUES(attached_by_user_id),
				context_json = COALESCE(VALUES(context_json), context_json),
				last_seen_at = NOW(),
				attached_at = NOW(),
				expires_at = NULL,
				deleted_at = NULL,
				deleted_by_user_id = NULL'
		);
		$stmt->execute([
			's3_driver' => 's3',
			'object_key' => $objectKey,
			'public_url' => $normalized,
			'attached_status' => 'attached',
			'attached_by_user_id' => $userId,
			'context_json' => $contextJson,
		]);

		return true;
	} catch (Throwable $exception) {
		app_uploaded_object_manifest_log_warning('Unable to mark uploaded object as attached.', [
			'path' => $normalized,
			'object_key' => $objectKey,
			'error' => $exception->getMessage(),
		]);
		return false;
	}
}

function app_uploaded_object_manifest_mark_deleted(?string $path, array $context = []): bool
{
	$normalized = normalize_public_file_url($path);
	$objectKey = s3_object_key_from_public_url($normalized);
	if ($normalized === '' || $objectKey === null) {
		return false;
	}

	$pdo = app_uploaded_object_manifest_connection();
	if (!$pdo instanceof PDO) {
		return false;
	}

	try {
		app_uploaded_object_manifest_ensure_schema($pdo);

		$userId = app_uploaded_object_manifest_current_user_id();
		$contextJson = app_uploaded_object_manifest_encode_context($context);

		$stmt = $pdo->prepare(
			'INSERT INTO `' . app_uploaded_object_manifest_table_name() . '` (
				storage_driver,
				object_key,
				public_url,
				status,
				deleted_by_user_id,
				context_json,
				created_at,
				last_seen_at,
				deleted_at,
				expires_at
			) VALUES (
				:s3_driver,
				:object_key,
				:public_url,
				:deleted_status,
				:deleted_by_user_id,
				:context_json,
				NOW(),
				NOW(),
				NOW(),
				NULL
			)
			ON DUPLICATE KEY UPDATE
				public_url = VALUES(public_url),
				status = VALUES(status),
				deleted_by_user_id = VALUES(deleted_by_user_id),
				context_json = COALESCE(VALUES(context_json), context_json),
				last_seen_at = NOW(),
				deleted_at = NOW(),
				expires_at = NULL'
		);
		$stmt->execute([
			's3_driver' => 's3',
			'object_key' => $objectKey,
			'public_url' => $normalized,
			'deleted_status' => 'deleted',
			'deleted_by_user_id' => $userId,
			'context_json' => $contextJson,
		]);

		return true;
	} catch (Throwable $exception) {
		app_uploaded_object_manifest_log_warning('Unable to mark uploaded object as deleted.', [
			'path' => $normalized,
			'object_key' => $objectKey,
			'error' => $exception->getMessage(),
		]);
		return false;
	}
}

function app_uploaded_object_manifest_purge_stale_drafts(int $olderThanHours = 24, int $limit = 100): array
{
	$pdo = app_uploaded_object_manifest_connection();
	if (!$pdo instanceof PDO) {
		return [
			'deleted_objects' => 0,
			'marked_deleted' => 0,
			'failed' => 0,
		];
	}

	app_uploaded_object_manifest_ensure_schema($pdo);

	$olderThanHours = max(1, $olderThanHours);
	$limit = max(1, min($limit, 1000));
	$stmt = $pdo->prepare(
		'SELECT object_key, public_url
		 FROM `' . app_uploaded_object_manifest_table_name() . '`
		 WHERE status = :draft_status
		   AND COALESCE(expires_at, DATE_ADD(created_at, INTERVAL ' . $olderThanHours . ' HOUR)) < NOW()
		 ORDER BY id ASC
		 LIMIT :limit_rows'
	);
	$stmt->bindValue(':draft_status', 'draft', PDO::PARAM_STR);
	$stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$result = [
		'deleted_objects' => 0,
		'marked_deleted' => 0,
		'failed' => 0,
	];

	foreach ($rows as $row) {
		$objectKey = trim((string) ($row['object_key'] ?? ''));
		$publicUrl = trim((string) ($row['public_url'] ?? ''));
		if ($objectKey === '' || $publicUrl === '') {
			$result['failed']++;
			continue;
		}

		if (!s3_delete_object($objectKey)) {
			$result['failed']++;
			continue;
		}

		$result['deleted_objects']++;
		if (app_uploaded_object_manifest_mark_deleted($publicUrl, ['reason' => 'draft_purge'])) {
			$result['marked_deleted']++;
		}
	}

	return $result;
}

function app_uploaded_object_manifest_purge_deleted_records(int $retentionDays = 30): int
{
	$pdo = app_uploaded_object_manifest_connection();
	if (!$pdo instanceof PDO) {
		return 0;
	}

	app_uploaded_object_manifest_ensure_schema($pdo);
	$retentionDays = max(1, $retentionDays);

	$stmt = $pdo->prepare(
		'DELETE FROM `' . app_uploaded_object_manifest_table_name() . '`
		 WHERE status = :deleted_status
		   AND deleted_at IS NOT NULL
		   AND deleted_at < DATE_SUB(NOW(), INTERVAL ' . $retentionDays . ' DAY)'
	);
	$stmt->execute([
		'deleted_status' => 'deleted',
	]);

	return (int) $stmt->rowCount();
}

function s3_detect_content_type(string $filePath): string
{
	if (function_exists('mime_content_type')) {
		$mimeType = @mime_content_type($filePath);
		if (is_string($mimeType) && $mimeType !== '') {
			return $mimeType;
		}
	}

	return 'application/octet-stream';
}

function s3_signing_key(string $secretKey, string $dateStamp, string $region, string $service): string
{
	$dateKey = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
	$regionKey = hash_hmac('sha256', $region, $dateKey, true);
	$serviceKey = hash_hmac('sha256', $service, $regionKey, true);

	return hash_hmac('sha256', 'aws4_request', $serviceKey, true);
}

function s3_build_canonical_headers(array $headers): array
{
	$normalizedHeaders = [];

	foreach ($headers as $name => $value) {
		$normalizedName = strtolower(trim((string) $name));
		if ($normalizedName === '') {
			continue;
		}

		$normalizedValue = preg_replace('/\s+/', ' ', trim((string) $value));
		$normalizedHeaders[$normalizedName] = $normalizedValue;
	}

	ksort($normalizedHeaders);

	$canonicalHeaders = '';
	$signedHeaders = [];
	foreach ($normalizedHeaders as $name => $value) {
		$canonicalHeaders .= $name . ':' . $value . "\n";
		$signedHeaders[] = $name;
	}

	return [
		'canonical_headers' => $canonicalHeaders,
		'signed_headers' => implode(';', $signedHeaders),
		'normalized_headers' => $normalizedHeaders,
	];
}

function s3_put_object(string $localFilePath, string $objectKey): ?string
{
	if (!s3_is_configured()) {
		app_file_storage_set_last_error('S3 storage is not fully configured.');
		return null;
	}

	if (!function_exists('curl_init')) {
		app_file_storage_set_last_error('PHP curl extension is not available for S3 uploads.');
		return null;
	}

	$fileContents = @file_get_contents($localFilePath);
	if ($fileContents === false) {
		app_file_storage_set_last_error('Unable to read uploaded temporary file for S3 upload.', [
			'tmp_path' => $localFilePath,
		]);
		return null;
	}

	$endpoint = s3_endpoint();
	$bucket = s3_bucket();
	$region = s3_region();
	$accessKey = s3_access_key();
	$secretKey = s3_secret_key();
	$objectKey = s3_normalize_object_key($objectKey);
	$encodedObjectKey = s3_encode_object_key($objectKey);
	$contentType = s3_detect_content_type($localFilePath);
	$payloadHash = hash('sha256', $fileContents);
	$amzDate = gmdate('Ymd\THis\Z');
	$dateStamp = gmdate('Ymd');
	$uriPath = s3_use_path_style()
		? '/' . rawurlencode($bucket) . '/' . $encodedObjectKey
		: '/' . $encodedObjectKey;

	$parsedEndpoint = parse_url($endpoint);
	if (!is_array($parsedEndpoint) || empty($parsedEndpoint['scheme']) || empty($parsedEndpoint['host'])) {
		app_file_storage_set_last_error('S3 endpoint is invalid.', [
			'endpoint' => $endpoint,
		]);
		return null;
	}

	$host = s3_use_path_style() ? $parsedEndpoint['host'] : $bucket . '.' . $parsedEndpoint['host'];
	$port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
	$basePath = isset($parsedEndpoint['path']) ? rtrim($parsedEndpoint['path'], '/') : '';
	$requestUrl = $parsedEndpoint['scheme'] . '://' . $host . $port . $basePath . $uriPath;
	$canonicalUri = ($basePath !== '' ? $basePath : '') . $uriPath;
	$headersToSign = [
		'content-type' => $contentType,
		'host' => $host . $port,
		'x-amz-content-sha256' => $payloadHash,
		'x-amz-date' => $amzDate,
	];

	$acl = s3_object_acl();
	if ($acl !== '') {
		$headersToSign['x-amz-acl'] = $acl;
	}

	$canonicalHeaderParts = s3_build_canonical_headers($headersToSign);
	$canonicalHeaders = (string) ($canonicalHeaderParts['canonical_headers'] ?? '');
	$signedHeaders = (string) ($canonicalHeaderParts['signed_headers'] ?? '');
	$normalizedHeaders = is_array($canonicalHeaderParts['normalized_headers'] ?? null)
		? $canonicalHeaderParts['normalized_headers']
		: [];
	$headers = [];
	foreach ($normalizedHeaders as $name => $value) {
		$headers[] = $name . ': ' . $value;
	}

	$canonicalRequest = implode("\n", [
		'PUT',
		$canonicalUri,
		'',
		$canonicalHeaders,
		$signedHeaders,
		$payloadHash,
	]);
	$credentialScope = $dateStamp . '/' . $region . '/s3/aws4_request';
	$stringToSign = implode("\n", [
		'AWS4-HMAC-SHA256',
		$amzDate,
		$credentialScope,
		hash('sha256', $canonicalRequest),
	]);
	$signature = hash_hmac(
		'sha256',
		$stringToSign,
		s3_signing_key($secretKey, $dateStamp, $region, 's3')
	);
	$headers[] = 'Authorization: AWS4-HMAC-SHA256 Credential=' . $accessKey . '/' . $credentialScope
		. ', SignedHeaders=' . $signedHeaders
		. ', Signature=' . $signature;

	$curl = curl_init($requestUrl);
	if ($curl === false) {
		return null;
	}

	curl_setopt_array($curl, [
		CURLOPT_CUSTOMREQUEST => 'PUT',
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_POSTFIELDS => $fileContents,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_CONNECTTIMEOUT => 15,
	]);

	$response = curl_exec($curl);
	$httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$curlError = curl_error($curl);
	curl_close($curl);

	if ($response === false || $httpCode < 200 || $httpCode >= 300) {
		$responsePreview = '';
		if (is_string($response) && $response !== '') {
			$responsePreview = substr($response, -2000);
		}

		app_file_storage_set_last_error('S3 upload request failed.', [
			'http_code' => $httpCode,
			'curl_error' => $curlError,
			'request_url' => $requestUrl,
			'object_key' => $objectKey,
			'response_preview' => $responsePreview,
		]);
		return null;
	}

	return s3_object_public_url($objectKey);
}

function s3_delete_object(string $objectKey): bool
{
	if (!s3_is_configured()) {
		app_file_storage_set_last_error('S3 storage is not fully configured.');
		return false;
	}

	if (!function_exists('curl_init')) {
		app_file_storage_set_last_error('PHP curl extension is not available for S3 deletes.');
		return false;
	}

	$endpoint = s3_endpoint();
	$bucket = s3_bucket();
	$region = s3_region();
	$accessKey = s3_access_key();
	$secretKey = s3_secret_key();
	$objectKey = s3_normalize_object_key($objectKey);
	$encodedObjectKey = s3_encode_object_key($objectKey);
	$payloadHash = hash('sha256', '');
	$amzDate = gmdate('Ymd\THis\Z');
	$dateStamp = gmdate('Ymd');
	$uriPath = s3_use_path_style()
		? '/' . rawurlencode($bucket) . '/' . $encodedObjectKey
		: '/' . $encodedObjectKey;

	$parsedEndpoint = parse_url($endpoint);
	if (!is_array($parsedEndpoint) || empty($parsedEndpoint['scheme']) || empty($parsedEndpoint['host'])) {
		app_file_storage_set_last_error('S3 endpoint is invalid.', [
			'endpoint' => $endpoint,
		]);
		return false;
	}

	$host = s3_use_path_style() ? $parsedEndpoint['host'] : $bucket . '.' . $parsedEndpoint['host'];
	$port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
	$basePath = isset($parsedEndpoint['path']) ? rtrim($parsedEndpoint['path'], '/') : '';
	$requestUrl = $parsedEndpoint['scheme'] . '://' . $host . $port . $basePath . $uriPath;
	$canonicalUri = ($basePath !== '' ? $basePath : '') . $uriPath;
	$headersToSign = [
		'host' => $host . $port,
		'x-amz-content-sha256' => $payloadHash,
		'x-amz-date' => $amzDate,
	];

	$canonicalHeaderParts = s3_build_canonical_headers($headersToSign);
	$canonicalHeaders = (string) ($canonicalHeaderParts['canonical_headers'] ?? '');
	$signedHeaders = (string) ($canonicalHeaderParts['signed_headers'] ?? '');
	$normalizedHeaders = is_array($canonicalHeaderParts['normalized_headers'] ?? null)
		? $canonicalHeaderParts['normalized_headers']
		: [];
	$headers = [];
	foreach ($normalizedHeaders as $name => $value) {
		$headers[] = $name . ': ' . $value;
	}

	$canonicalRequest = implode("\n", [
		'DELETE',
		$canonicalUri,
		'',
		$canonicalHeaders,
		$signedHeaders,
		$payloadHash,
	]);
	$credentialScope = $dateStamp . '/' . $region . '/s3/aws4_request';
	$stringToSign = implode("\n", [
		'AWS4-HMAC-SHA256',
		$amzDate,
		$credentialScope,
		hash('sha256', $canonicalRequest),
	]);
	$signature = hash_hmac(
		'sha256',
		$stringToSign,
		s3_signing_key($secretKey, $dateStamp, $region, 's3')
	);
	$headers[] = 'Authorization: AWS4-HMAC-SHA256 Credential=' . $accessKey . '/' . $credentialScope
		. ', SignedHeaders=' . $signedHeaders
		. ', Signature=' . $signature;

	$curl = curl_init($requestUrl);
	if ($curl === false) {
		app_file_storage_set_last_error('Unable to initialize curl for S3 delete request.');
		return false;
	}

	curl_setopt_array($curl, [
		CURLOPT_CUSTOMREQUEST => 'DELETE',
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER => true,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_CONNECTTIMEOUT => 15,
	]);

	$response = curl_exec($curl);
	$httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$curlError = curl_error($curl);
	curl_close($curl);

	if ($response === false || ($httpCode !== 204 && $httpCode !== 200 && $httpCode !== 404)) {
		$responsePreview = '';
		if (is_string($response) && $response !== '') {
			$responsePreview = substr($response, -2000);
		}

		app_file_storage_set_last_error('S3 delete request failed.', [
			'http_code' => $httpCode,
			'curl_error' => $curlError,
			'request_url' => $requestUrl,
			'object_key' => $objectKey,
			'response_preview' => $responsePreview,
		]);
		return false;
	}

	return true;
}

function s3_generate_object_key(string $prefix, string $originalName, ?string $subdir = null): string
{
	$safeOriginalName = basename(trim($originalName));
	$safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $safeOriginalName) ?: 'upload.bin';
	$randomSuffix = bin2hex(random_bytes(8));
	$storedName = sprintf('%s-%d-%s-%s', $prefix, time(), $randomSuffix, $safeOriginalName);
	$normalizedSubdir = $subdir !== null ? trim(str_replace('\\', '/', $subdir), '/') : '';

	return $normalizedSubdir !== '' ? $normalizedSubdir . '/' . $storedName : $storedName;
}

function s3_presign_put_object(string $objectKey, string $contentType, int $expiresIn = 900): ?array
{
	if (!s3_is_configured()) {
		app_file_storage_set_last_error('S3 storage is not fully configured.');
		return null;
	}

	$endpoint = s3_endpoint();
	$bucket = s3_bucket();
	$region = s3_region();
	$accessKey = s3_access_key();
	$secretKey = s3_secret_key();
	$objectKey = s3_normalize_object_key($objectKey);
	$encodedObjectKey = s3_encode_object_key($objectKey);
	$contentType = trim($contentType) !== '' ? trim($contentType) : 'application/octet-stream';
	$expiresIn = max(60, min($expiresIn, 3600));

	$parsedEndpoint = parse_url($endpoint);
	if (!is_array($parsedEndpoint) || empty($parsedEndpoint['scheme']) || empty($parsedEndpoint['host'])) {
		app_file_storage_set_last_error('S3 endpoint is invalid.', [
			'endpoint' => $endpoint,
		]);
		return null;
	}

	$host = s3_use_path_style() ? $parsedEndpoint['host'] : $bucket . '.' . $parsedEndpoint['host'];
	$port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
	$basePath = isset($parsedEndpoint['path']) ? rtrim($parsedEndpoint['path'], '/') : '';
	$uriPath = s3_use_path_style()
		? '/' . rawurlencode($bucket) . '/' . $encodedObjectKey
		: '/' . $encodedObjectKey;
	$canonicalUri = ($basePath !== '' ? $basePath : '') . $uriPath;
	$requestUrl = $parsedEndpoint['scheme'] . '://' . $host . $port . $canonicalUri;
	$amzDate = gmdate('Ymd\THis\Z');
	$dateStamp = gmdate('Ymd');
	$credentialScope = $dateStamp . '/' . $region . '/s3/aws4_request';

	$headersToSign = [
		'content-type' => $contentType,
		'host' => $host . $port,
	];
	$acl = s3_object_acl();
	if ($acl !== '') {
		$headersToSign['x-amz-acl'] = $acl;
	}

	$canonicalHeaderParts = s3_build_canonical_headers($headersToSign);
	$signedHeaders = (string) ($canonicalHeaderParts['signed_headers'] ?? '');
	$canonicalHeaders = (string) ($canonicalHeaderParts['canonical_headers'] ?? '');

	$queryParams = [
		'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
		'X-Amz-Credential' => $accessKey . '/' . $credentialScope,
		'X-Amz-Date' => $amzDate,
		'X-Amz-Expires' => (string) $expiresIn,
		'X-Amz-SignedHeaders' => $signedHeaders,
	];
	ksort($queryParams);
	$canonicalQueryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

	$canonicalRequest = implode("\n", [
		'PUT',
		$canonicalUri,
		$canonicalQueryString,
		$canonicalHeaders,
		$signedHeaders,
		'UNSIGNED-PAYLOAD',
	]);

	$stringToSign = implode("\n", [
		'AWS4-HMAC-SHA256',
		$amzDate,
		$credentialScope,
		hash('sha256', $canonicalRequest),
	]);

	$signature = hash_hmac(
		'sha256',
		$stringToSign,
		s3_signing_key($secretKey, $dateStamp, $region, 's3')
	);

	$queryParams['X-Amz-Signature'] = $signature;
	$presignedUrl = $requestUrl . '?' . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

	$requiredHeaders = [
		'Content-Type' => $contentType,
	];
	if ($acl !== '') {
		$requiredHeaders['x-amz-acl'] = $acl;
	}

	return [
		'upload_url' => $presignedUrl,
		'public_url' => s3_object_public_url($objectKey),
		'object_key' => $objectKey,
		'method' => 'PUT',
		'headers' => $requiredHeaders,
		'expires_in' => $expiresIn,
	];
}

function app_direct_upload_is_available(): bool
{
	return app_file_storage_uses_s3() && s3_is_configured();
}

function app_direct_upload_presets(): array
{
	return [
		'course_thumbnail' => [
			'prefix' => 'course-thumb',
			'subdir' => 'courses/thumbnails',
			'max_bytes' => 0,
			'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'],
			'mime_prefixes' => ['image/'],
		],
		'activity_thumbnail' => [
			'prefix' => 'activity-thumb',
			'subdir' => 'activities/thumbnails',
			'max_bytes' => 0,
			'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'],
			'mime_prefixes' => ['image/'],
		],
		'avatar' => [
			'prefix' => 'avatar',
			'subdir' => 'users/avatars',
			'max_bytes' => 10 * 1024 * 1024,
			'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'],
			'mime_prefixes' => ['image/'],
		],
		'teacher_intro_video' => [
			'prefix' => 'teacher-video',
			'subdir' => 'users/teacher-videos',
			'max_bytes' => 0,
			'extensions' => ['mp4', 'mov', 'webm'],
			'mime_prefixes' => ['video/'],
		],
		'home_intro_video' => [
			'prefix' => 'home-intro-video',
			'subdir' => 'home/intro-videos',
			'max_bytes' => 0,
			'extensions' => ['mp4', 'mov', 'webm'],
			'mime_prefixes' => ['video/'],
		],
		'portfolio_media' => [
			'prefix' => 'portfolio',
			'subdir' => 'portfolios/media',
			'max_bytes' => 0,
			'extensions' => ['jpg', 'jpeg', 'png', 'mp4', 'mov', 'webm'],
			'mime_prefixes' => ['image/', 'video/'],
		],
		'material_file' => [
			'prefix' => 'material',
			'subdir' => 'materials/files',
			'max_bytes' => 0,
			'extensions' => ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'mp4', 'mov', 'webm', 'mp3', 'avi'],
			'mime_prefixes' => ['application/', 'image/', 'video/', 'audio/'],
		],
		'assignment_file' => [
			'prefix' => 'assignment',
			'subdir' => 'assignments/files',
			'max_bytes' => 0,
			'extensions' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'],
			'mime_prefixes' => ['application/', 'image/'],
		],
		'lesson_attachment' => [
			'prefix' => 'lesson-attachment',
			'subdir' => 'lessons/attachments',
			'max_bytes' => 0,
			'extensions' => ['pdf', 'ppt', 'pptx', 'doc', 'docx'],
			'mime_prefixes' => ['application/'],
		],
		'assignment_submission' => [
			'prefix' => 'submission',
			'subdir' => 'assignments/submissions',
			'max_bytes' => 0,
			'extensions' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'mp4', 'mov', 'webm'],
			'mime_prefixes' => ['application/', 'image/', 'video/'],
		],
	];
}

function app_direct_upload_preset(string $presetKey): ?array
{
	$presets = app_direct_upload_presets();
	$preset = $presets[$presetKey] ?? null;
	return is_array($preset) ? $preset : null;
}

function app_file_path_looks_like_video(?string $path): bool
{
	$normalized = strtolower(trim((string) $path));
	if ($normalized === '') {
		return false;
	}

	$extension = strtolower((string) pathinfo(parse_url($normalized, PHP_URL_PATH) ?: $normalized, PATHINFO_EXTENSION));
	return in_array($extension, ['mp4', 'mov', 'webm', 'avi', 'm4v'], true);
}

function app_uploaded_file_looks_like_video(array $file): bool
{
	$name = strtolower((string) ($file['name'] ?? ''));
	if ($name !== '' && app_file_path_looks_like_video($name)) {
		return true;
	}

	$type = strtolower(trim((string) ($file['type'] ?? '')));
	return $type !== '' && str_starts_with($type, 'video/');
}

function app_direct_upload_build_spec(string $presetKey, string $originalName, string $contentType, int $fileSize = 0): ?array
{
	$preset = app_direct_upload_preset($presetKey);
	if (!is_array($preset)) {
		app_file_storage_set_last_error('Unknown direct upload preset.', [
			'preset' => $presetKey,
		]);
		return null;
	}

	$originalName = trim($originalName);
	if ($originalName === '') {
		app_file_storage_set_last_error('Missing original filename for direct upload.');
		return null;
	}

	$extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
	$allowedExtensions = is_array($preset['extensions'] ?? null) ? $preset['extensions'] : [];
	if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
		app_file_storage_set_last_error('File extension is not allowed for direct upload.', [
			'extension' => $extension,
			'preset' => $presetKey,
		]);
		return null;
	}

	$contentType = trim($contentType) !== '' ? trim($contentType) : 'application/octet-stream';
	$allowedMimePrefixes = is_array($preset['mime_prefixes'] ?? null) ? $preset['mime_prefixes'] : [];
	$matchesMimePrefix = false;
	foreach ($allowedMimePrefixes as $mimePrefix) {
		if ($mimePrefix !== '' && str_starts_with(strtolower($contentType), strtolower((string) $mimePrefix))) {
			$matchesMimePrefix = true;
			break;
		}
	}
	if (!$matchesMimePrefix) {
		app_file_storage_set_last_error('File MIME type is not allowed for direct upload.', [
			'content_type' => $contentType,
			'preset' => $presetKey,
		]);
		return null;
	}

	$maxBytes = (int) ($preset['max_bytes'] ?? 0);
	if ($maxBytes > 0 && $fileSize > 0 && $fileSize > $maxBytes) {
		app_file_storage_set_last_error('File exceeds direct upload size limit.', [
			'file_size' => $fileSize,
			'max_bytes' => $maxBytes,
			'preset' => $presetKey,
		]);
		return null;
	}

	$objectKey = s3_generate_object_key(
		(string) ($preset['prefix'] ?? 'upload'),
		$originalName,
		(string) ($preset['subdir'] ?? '')
	);
	$spec = s3_presign_put_object($objectKey, $contentType);
	if ($spec === null) {
		return null;
	}

	app_uploaded_object_manifest_record_draft($presetKey, $objectKey, (string) ($spec['public_url'] ?? ''), [
		'source' => 'direct_upload',
		'content_type' => $contentType,
		'file_size' => $fileSize,
	]);

	$spec['max_bytes'] = $maxBytes;
	$spec['preset'] = $presetKey;
	return $spec;
}

function store_uploaded_file_for_preset(array $file, string $presetKey): ?string
{
	$preset = app_direct_upload_preset($presetKey);
	if (!is_array($preset)) {
		app_file_storage_set_last_error('Unknown upload preset.', [
			'preset' => $presetKey,
		]);
		return null;
	}

	$prefix = trim((string) ($preset['prefix'] ?? 'upload'));
	$subdir = trim((string) ($preset['subdir'] ?? ''));
	return store_uploaded_file($file, $prefix !== '' ? $prefix : 'upload', $subdir !== '' ? $subdir : null);
}

function store_uploaded_file_locally(string $tmpPath, string $storedName, string $normalizedSubdir): ?string
{
	$uploadDir = upload_storage_dir($normalizedSubdir);
	if (!app_ensure_directory($uploadDir)) {
		app_file_storage_set_last_error('Unable to create local upload directory.', [
			'upload_dir' => $uploadDir,
		]);
		return null;
	}

	$targetPath = $uploadDir . '/' . $storedName;
	$moveSucceeded = false;
	if (is_uploaded_file($tmpPath)) {
		$moveSucceeded = move_uploaded_file($tmpPath, $targetPath);
	}

	if (!$moveSucceeded) {
		$moveSucceeded = @rename($tmpPath, $targetPath);
	}

	if (!$moveSucceeded && !copy($tmpPath, $targetPath)) {
		app_file_storage_set_last_error('Unable to move uploaded file into local storage.', [
			'tmp_path' => $tmpPath,
			'target_path' => $targetPath,
		]);
		return null;
	}

	$publicBase = local_upload_public_base_path();
	if ($normalizedSubdir !== '') {
		return $publicBase . '/' . $normalizedSubdir . '/' . $storedName;
	}

	return $publicBase . '/' . $storedName;
}

function store_uploaded_file(array $file, string $prefix, ?string $subdir = null): ?string
{
	app_file_storage_clear_last_error();

	if (empty($file['name']) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
		app_file_storage_set_last_error('No upload file was provided.');
		return null;
	}

	if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
		app_file_storage_set_last_error('Upload finished with a PHP upload error.', [
			'upload_error' => (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE),
		]);
		return null;
	}

	$originalName = basename((string) $file['name']);
	$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'upload.bin';
	$storedName = sprintf('%s-%d-%s', $prefix, time(), $safeName);
	$tmpPath = (string) ($file['tmp_name'] ?? '');
	$normalizedSubdir = $subdir !== null ? trim(str_replace('\\', '/', $subdir), '/') : '';

	if ($tmpPath === '' || !is_file($tmpPath)) {
		app_file_storage_set_last_error('Uploaded temporary file is missing.', [
			'tmp_path' => $tmpPath,
		]);
		return null;
	}

	if (app_file_storage_uses_s3()) {
		$objectKey = $normalizedSubdir !== '' ? $normalizedSubdir . '/' . $storedName : $storedName;
		$s3Url = s3_put_object($tmpPath, $objectKey);
		if ($s3Url !== null) {
			app_uploaded_object_manifest_record_draft($prefix, $objectKey, $s3Url, [
				'source' => 'server_upload',
				'subdir' => $normalizedSubdir,
				'filename' => $storedName,
			]);
			return $s3Url;
		}

		$s3Error = app_file_storage_last_error_message();
		$s3ErrorContext = app_file_storage_last_error_context();
		app_log('warning', 'S3 upload failed, falling back to local storage.', [
			'driver' => app_file_storage_driver(),
			'subdir' => $normalizedSubdir,
			'filename' => $storedName,
			's3_error' => $s3Error,
			's3_error_context' => $s3ErrorContext,
		]);
		app_file_storage_clear_last_error();
	}

	$localUrl = store_uploaded_file_locally($tmpPath, $storedName, $normalizedSubdir);
	if ($localUrl === null) {
		app_log('error', 'File upload failed for all storage backends.', [
			'driver' => app_file_storage_driver(),
			'subdir' => $normalizedSubdir,
			'filename' => $storedName,
			'storage_error' => app_file_storage_last_error_message(),
		]);
	}

	return $localUrl;
}

function app_uploaded_file_relative_path_from_public_url(?string $path): ?string
{
	$normalized = normalize_public_file_url($path);
	if ($normalized === '') {
		return null;
	}

	$publicBase = rtrim(local_upload_public_base_path(), '/');
	if ($publicBase === '' || !str_starts_with($normalized, $publicBase . '/')) {
		return null;
	}

	$suffix = ltrim(substr($normalized, strlen($publicBase)), '/');
	if ($suffix === '') {
		return null;
	}

	$segments = [];
	foreach (explode('/', $suffix) as $segment) {
		$decoded = rawurldecode($segment);
		if ($decoded === '' || $decoded === '.' || $decoded === '..') {
			return null;
		}

		$segments[] = $decoded;
	}

	return implode('/', $segments);
}

function s3_object_key_from_public_url(?string $path): ?string
{
	$normalized = normalize_public_file_url($path);
	if ($normalized === '') {
		return null;
	}

	$publicBase = rtrim(upload_public_base_path(), '/');
	if ($publicBase === '' || !str_starts_with($normalized, $publicBase . '/')) {
		return null;
	}

	$suffix = ltrim(substr($normalized, strlen($publicBase)), '/');
	if ($suffix === '') {
		return null;
	}

	$segments = [];
	foreach (explode('/', $suffix) as $segment) {
		$decoded = rawurldecode($segment);
		if ($decoded === '' || $decoded === '.' || $decoded === '..') {
			return null;
		}

		$segments[] = $decoded;
	}

	return s3_normalize_object_key(implode('/', $segments));
}

function app_delete_uploaded_file_by_url(?string $path): bool
{
	app_file_storage_clear_last_error();

	$normalized = normalize_public_file_url($path);
	if ($normalized === '') {
		return true;
	}

	if (!is_trusted_uploaded_file_url($normalized)) {
		app_file_storage_set_last_error('Refusing to delete an untrusted uploaded file URL.', [
			'path' => $path,
			'normalized_path' => $normalized,
		]);
		return false;
	}

	$localRelativePath = app_uploaded_file_relative_path_from_public_url($normalized);
	if ($localRelativePath !== null) {
		$baseDir = str_replace('\\', '/', rtrim(upload_storage_dir(), '/\\'));
		$targetPath = str_replace('\\', '/', upload_storage_dir($localRelativePath));
		if (!str_starts_with($targetPath, $baseDir . '/')) {
			app_file_storage_set_last_error('Resolved local upload path is outside the storage directory.', [
				'path' => $normalized,
				'target_path' => $targetPath,
			]);
			return false;
		}

		if (!is_file($targetPath)) {
			return true;
		}

		if (@unlink($targetPath)) {
			app_uploaded_object_manifest_mark_deleted($normalized, ['reason' => 'file_delete']);
			return true;
		}

		app_file_storage_set_last_error('Unable to delete local uploaded file.', [
			'path' => $normalized,
			'target_path' => $targetPath,
		]);
		return false;
	}

	$objectKey = s3_object_key_from_public_url($normalized);
	if ($objectKey !== null) {
		$deleted = s3_delete_object($objectKey);
		if ($deleted) {
			app_uploaded_object_manifest_mark_deleted($normalized, ['reason' => 'file_delete']);
		}

		return $deleted;
	}

	app_file_storage_set_last_error('Uploaded file URL does not match any managed storage backend.', [
		'path' => $normalized,
	]);
	return false;
}

function app_cleanup_replaced_uploaded_file(?string $oldPath, ?string $newPath): bool
{
	$oldNormalized = normalize_public_file_url($oldPath);
	if ($oldNormalized === '') {
		return true;
	}

	$newNormalized = normalize_public_file_url($newPath);
	if ($newNormalized !== '' && $newNormalized === $oldNormalized) {
		return true;
	}

	return app_delete_uploaded_file_by_url($oldNormalized);
}
