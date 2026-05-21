<?php
declare(strict_types=1);

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

function s3_put_object(string $localFilePath, string $objectKey): ?string
{
	if (!s3_is_configured() || !function_exists('curl_init')) {
		return null;
	}

	$fileContents = @file_get_contents($localFilePath);
	if ($fileContents === false) {
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
		return null;
	}

	$host = s3_use_path_style() ? $parsedEndpoint['host'] : $bucket . '.' . $parsedEndpoint['host'];
	$port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
	$basePath = isset($parsedEndpoint['path']) ? rtrim($parsedEndpoint['path'], '/') : '';
	$requestUrl = $parsedEndpoint['scheme'] . '://' . $host . $port . $basePath . $uriPath;
	$canonicalUri = ($basePath !== '' ? $basePath : '') . $uriPath;
	$canonicalHeaders = 'content-type:' . $contentType . "\n"
		. 'host:' . $host . $port . "\n"
		. 'x-amz-content-sha256:' . $payloadHash . "\n"
		. 'x-amz-date:' . $amzDate . "\n";
	$signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';
	$headers = [
		'Content-Type: ' . $contentType,
		'Host: ' . $host . $port,
		'X-Amz-Content-Sha256: ' . $payloadHash,
		'X-Amz-Date: ' . $amzDate,
	];

	$acl = s3_object_acl();
	if ($acl !== '') {
		$canonicalHeaders .= 'x-amz-acl:' . $acl . "\n";
		$signedHeaders .= ';x-amz-acl';
		$headers[] = 'X-Amz-Acl: ' . $acl;
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
	curl_close($curl);

	if ($response === false || $httpCode < 200 || $httpCode >= 300) {
		return null;
	}

	return s3_object_public_url($objectKey);
}

function store_uploaded_file(array $file, string $prefix, ?string $subdir = null): ?string
{
	if (empty($file['name']) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
		return null;
	}

	if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
		return null;
	}

	$originalName = basename((string) $file['name']);
	$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'upload.bin';
	$storedName = sprintf('%s-%d-%s', $prefix, time(), $safeName);
	$tmpPath = (string) ($file['tmp_name'] ?? '');
	$normalizedSubdir = $subdir !== null ? trim(str_replace('\\', '/', $subdir), '/') : '';

	if ($tmpPath === '' || !is_file($tmpPath)) {
		return null;
	}

	if (app_file_storage_uses_s3()) {
		$objectKey = $normalizedSubdir !== '' ? $normalizedSubdir . '/' . $storedName : $storedName;
		return s3_put_object($tmpPath, $objectKey);
	}

	$uploadDir = upload_storage_dir($subdir);
	if (!app_ensure_directory($uploadDir)) {
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
		return null;
	}

	$publicBase = upload_public_base_path();
	if ($normalizedSubdir !== '') {
		return $publicBase . '/' . $normalizedSubdir . '/' . $storedName;
	}

	return $publicBase . '/' . $storedName;
}
