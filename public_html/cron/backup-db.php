<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';

$lockDir = BASE_PATH . '/storage/locks';
if (!app_ensure_directory($lockDir)) {
    fwrite(STDERR, "Cannot create lock directory: {$lockDir}\n");
    exit(1);
}

$lockFile = $lockDir . '/backup-db.lock';
$lockHandle = fopen($lockFile, 'c+');
if ($lockHandle === false) {
    fwrite(STDERR, "Cannot open lock file: {$lockFile}\n");
    exit(1);
}

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "[SKIP] Backup job is already running\n");
    fclose($lockHandle);
    exit(0);
}

ftruncate($lockHandle, 0);
fwrite($lockHandle, (string) getmypid());
fflush($lockHandle);

$tmpDir = BASE_PATH . '/storage/tmp';
$sqlDumpPath = '';
$defaultsFilePath = '';

try {
    ensureBackupRequirements();

    if (!app_ensure_directory($tmpDir)) {
        throw new RuntimeException('Cannot create temporary directory: ' . $tmpDir);
    }

    $sqlDumpPath = tempnam($tmpDir, 'dbdump_');
    if ($sqlDumpPath === false) {
        throw new RuntimeException('Cannot create temporary SQL dump file.');
    }

    $defaultsFilePath = tempnam($tmpDir, 'mysql_');
    if ($defaultsFilePath === false) {
        throw new RuntimeException('Cannot create temporary MySQL defaults file.');
    }

    writeMysqlDefaultsFile($defaultsFilePath);
    runMysqlDump($defaultsFilePath, $sqlDumpPath);

    $sqlContents = @file_get_contents($sqlDumpPath);
    if ($sqlContents === false) {
        throw new RuntimeException('Cannot read SQL dump file after export.');
    }

    $encryptedPayload = encryptBackupPayload($sqlContents);
    $hash = hash('sha256', $encryptedPayload);
    $fileName = $hash . '.sql';
    $objectKey = 'backups/' . $fileName;

    if (s3ObjectExists($objectKey)) {
        fwrite(STDOUT, "[SKIP] File đã tồn tại\n");
        $exitCode = 0;
    } else {
        uploadBackupToS3($objectKey, $encryptedPayload);
        fwrite(STDOUT, "[OK] Backup thành công\n");
        $exitCode = 0;
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . "\n");
    $exitCode = 1;
} finally {
    if ($sqlDumpPath !== '' && is_file($sqlDumpPath)) {
        @unlink($sqlDumpPath);
    }

    if ($defaultsFilePath !== '' && is_file($defaultsFilePath)) {
        @unlink($defaultsFilePath);
    }

    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

exit($exitCode);

function ensureBackupRequirements(): void
{
    $requiredConstants = [
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
        'APP_SECRET',
        'S3_BUCKET',
        'S3_ENDPOINT',
        'S3_REGION',
        'S3_ACCESS_KEY',
        'S3_SECRET_KEY',
    ];

    foreach ($requiredConstants as $constantName) {
        if (!defined($constantName) || trim((string) constant($constantName)) === '') {
            throw new RuntimeException('Missing required configuration constant: ' . $constantName);
        }
    }

    if (!function_exists('exec')) {
        throw new RuntimeException('PHP exec() is disabled on this server.');
    }

    if (!function_exists('openssl_encrypt')) {
        throw new RuntimeException('PHP OpenSSL extension is not available.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL extension is not available.');
    }

    if (!s3_is_configured()) {
        throw new RuntimeException('S3 storage is not fully configured.');
    }
}

function writeMysqlDefaultsFile(string $defaultsFilePath): void
{
    $lines = [
        '[client]',
        'host=' . (string) DB_HOST,
        'user=' . (string) DB_USER,
        'password=' . (string) DB_PASS,
    ];

    if (defined('DB_PORT') && trim((string) DB_PORT) !== '') {
        $lines[] = 'port=' . (string) DB_PORT;
    }

    $written = @file_put_contents($defaultsFilePath, implode("\n", $lines) . "\n", LOCK_EX);
    if ($written === false) {
        throw new RuntimeException('Cannot write temporary MySQL defaults file.');
    }

    @chmod($defaultsFilePath, 0600);
}

function runMysqlDump(string $defaultsFilePath, string $sqlDumpPath): void
{
    $parts = [
        'mysqldump',
        '--defaults-extra-file=' . escapeshellarg($defaultsFilePath),
        '--skip-comments',
        '--compact',
        '--single-transaction',
        '--result-file=' . escapeshellarg($sqlDumpPath),
        escapeshellarg((string) DB_NAME),
    ];

    $command = implode(' ', $parts) . ' 2>&1';
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException('mysqldump failed: ' . trim(implode("\n", $output)));
    }

    if (!is_file($sqlDumpPath) || filesize($sqlDumpPath) === 0) {
        throw new RuntimeException('mysqldump completed but SQL dump file is empty.');
    }
}

function encryptBackupPayload(string $sqlContents): string
{
    $key = hash('sha256', (string) APP_SECRET, true);
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    if ($ivLength === false || $ivLength <= 0) {
        throw new RuntimeException('Cannot determine IV length for AES-256-CBC.');
    }

    // Keep encryption deterministic so identical dumps map to the same hash/file name.
    $iv = substr(hash_hmac('sha256', $sqlContents, $key, true), 0, $ivLength);
    $ciphertext = openssl_encrypt($sqlContents, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    if (!is_string($ciphertext) || $ciphertext === '') {
        throw new RuntimeException('Cannot encrypt SQL dump payload.');
    }

    return $iv . $ciphertext;
}

function s3ObjectExists(string $objectKey): bool
{
    $response = sendSignedS3Request('HEAD', $objectKey, hash('sha256', ''), [
        'content-type' => 'application/octet-stream',
    ]);

    if ($response['http_code'] === 200) {
        return true;
    }

    if ($response['http_code'] === 404) {
        return false;
    }

    throw new RuntimeException(
        'S3 HEAD request failed with HTTP ' . $response['http_code'] . ': ' . truncateResponsePreview($response['response'])
    );
}

function uploadBackupToS3(string $objectKey, string $payload): void
{
    $response = sendSignedS3Request(
        'PUT',
        $objectKey,
        hash('sha256', $payload),
        [
            'content-type' => 'application/octet-stream',
        ],
        $payload
    );

    if ($response['http_code'] < 200 || $response['http_code'] >= 300) {
        throw new RuntimeException(
            'S3 PUT request failed with HTTP ' . $response['http_code'] . ': ' . truncateResponsePreview($response['response'])
        );
    }
}

function sendSignedS3Request(
    string $method,
    string $objectKey,
    string $payloadHash,
    array $extraHeaders,
    ?string $body = null
): array {
    $endpoint = s3_endpoint();
    $bucket = s3_bucket();
    $region = s3_region();
    $accessKey = s3_access_key();
    $secretKey = s3_secret_key();
    $objectKey = s3_normalize_object_key($objectKey);
    $encodedObjectKey = s3_encode_object_key($objectKey);
    $amzDate = gmdate('Ymd\THis\Z');
    $dateStamp = gmdate('Ymd');
    $uriPath = s3_use_path_style()
        ? '/' . rawurlencode($bucket) . '/' . $encodedObjectKey
        : '/' . $encodedObjectKey;

    $parsedEndpoint = parse_url($endpoint);
    if (!is_array($parsedEndpoint) || empty($parsedEndpoint['scheme']) || empty($parsedEndpoint['host'])) {
        throw new RuntimeException('S3 endpoint is invalid: ' . $endpoint);
    }

    $host = s3_use_path_style() ? $parsedEndpoint['host'] : $bucket . '.' . $parsedEndpoint['host'];
    $port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
    $basePath = isset($parsedEndpoint['path']) ? rtrim($parsedEndpoint['path'], '/') : '';
    $requestUrl = $parsedEndpoint['scheme'] . '://' . $host . $port . $basePath . $uriPath;
    $canonicalUri = ($basePath !== '' ? $basePath : '') . $uriPath;
    $payloadLength = $body !== null ? strlen($body) : 0;

    $headersToSign = array_merge($extraHeaders, [
        'host' => $host . $port,
        'x-amz-content-sha256' => $payloadHash,
        'x-amz-date' => $amzDate,
    ]);

    $acl = s3_object_acl();
    if ($acl !== '' && strtoupper($method) === 'PUT') {
        $headersToSign['x-amz-acl'] = $acl;
    }

    if (strtoupper($method) === 'PUT') {
        $headersToSign['content-length'] = (string) $payloadLength;
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

    if (strtoupper($method) === 'PUT') {
        // Avoid delayed uploads on some S3-compatible providers that do not handle 100-continue well.
        $headers[] = 'Expect:';
    }

    $canonicalRequest = implode("\n", [
        strtoupper($method),
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
        throw new RuntimeException('Cannot initialize cURL for S3 request.');
    }

    $curlOptions = [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_CONNECTTIMEOUT => 15,
    ];

    if ($body !== null) {
        $curlOptions[CURLOPT_POSTFIELDS] = $body;
        $curlOptions[CURLOPT_POSTFIELDSIZE] = $payloadLength;
    }

    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('S3 request failed: ' . $curlError);
    }

    return [
        'http_code' => $httpCode,
        'response' => $response,
    ];
}

function truncateResponsePreview(string $response): string
{
    $preview = trim(substr($response, -1000));
    return $preview !== '' ? $preview : '(empty response)';
}
