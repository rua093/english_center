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
