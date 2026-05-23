<?php
declare(strict_types=1);

function app_server_role(): string
{
    if (defined('APP_SERVER_ROLE')) {
        $role = strtolower(trim((string) APP_SERVER_ROLE));
        if ($role !== '') {
            return $role;
        }
    }

    $environment = strtolower(trim((string) (defined('APP_ENV') ? APP_ENV : 'production')));
    return str_contains($environment, 'sandbox') ? 'sandbox' : 'primary';
}

function app_is_sandbox_server(): bool
{
    return app_server_role() === 'sandbox';
}

function app_is_primary_server(): bool
{
    return !app_is_sandbox_server();
}

function app_maintenance_flag_file(): string
{
    return BASE_PATH . '/storage/maintenance.flag';
}

function app_is_maintenance_flag_enabled(): bool
{
    return is_file(app_maintenance_flag_file());
}

function app_is_maintenance_mode_enabled(): bool
{
    return (defined('MAINTENANCE_MODE') && (bool) MAINTENANCE_MODE) || app_is_maintenance_flag_enabled();
}

function app_set_maintenance_mode(bool $enabled): void
{
    $flagFile = app_maintenance_flag_file();
    $flagDir = dirname($flagFile);
    if (!app_ensure_directory($flagDir)) {
        throw new RuntimeException('Cannot create maintenance directory: ' . $flagDir);
    }

    if ($enabled) {
        $written = @file_put_contents($flagFile, (string) time(), LOCK_EX);
        if ($written === false) {
            throw new RuntimeException('Cannot enable maintenance mode.');
        }

        return;
    }

    if (is_file($flagFile) && !@unlink($flagFile)) {
        throw new RuntimeException('Cannot disable maintenance mode.');
    }
}

function app_sync_control_password(): string
{
    if (!defined('SYNC_CONTROL_PASSWORD')) {
        return '';
    }

    return trim((string) SYNC_CONTROL_PASSWORD);
}

function app_sync_control_password_is_valid(string $providedPassword): bool
{
    $configuredPassword = app_sync_control_password();
    if ($configuredPassword === '') {
        return false;
    }

    $providedPassword = trim($providedPassword);
    if ($providedPassword === '') {
        return false;
    }

    return hash_equals($configuredPassword, $providedPassword);
}

function sync_change_log_mode_file(): string
{
    return BASE_PATH . '/storage/cache/sync-change-log.mode';
}

function sync_change_log_mode(): string
{
    $modeFile = sync_change_log_mode_file();
    if (!is_file($modeFile)) {
        return 'trigger';
    }

    $mode = strtolower(trim((string) @file_get_contents($modeFile)));
    return in_array($mode, ['trigger', 'app'], true) ? $mode : 'trigger';
}

function sync_change_log_set_mode(string $mode): void
{
    $normalizedMode = strtolower(trim($mode));
    if (!in_array($normalizedMode, ['trigger', 'app'], true)) {
        return;
    }

    $modeFile = sync_change_log_mode_file();
    $modeDir = dirname($modeFile);
    if (!app_ensure_directory($modeDir)) {
        return;
    }

    @file_put_contents($modeFile, $normalizedMode, LOCK_EX);
}

function sync_change_log_use_application_driver(PDO $pdo): bool
{
    if (!app_is_sandbox_server()) {
        return false;
    }

    return sync_change_log_mode() === 'app';
}

function sync_change_log_bootstrap_marker_file(): string
{
    return BASE_PATH . '/storage/cache/sync-change-log.bootstrap';
}

function sync_change_log_bootstrap_if_needed(): void
{
    if (!app_is_sandbox_server()) {
        return;
    }

    $markerFile = sync_change_log_bootstrap_marker_file();
    $markerDir = dirname($markerFile);
    if (!app_ensure_directory($markerDir)) {
        return;
    }

    $lastBootstrapAt = is_file($markerFile) ? (int) @filemtime($markerFile) : 0;
    if ($lastBootstrapAt > 0 && (time() - $lastBootstrapAt) < 300) {
        return;
    }

    try {
        $pdo = Database::connection();
        if (sync_change_log_infrastructure_needs_install($pdo)) {
            sync_change_log_install_infrastructure($pdo);
        }

        @touch($markerFile);
    } catch (Throwable $exception) {
        app_log('error', 'Unable to auto-bootstrap sandbox sync change log', [
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'server_role' => app_server_role(),
        ]);
    }
}

function failover_guard_sandbox_server(): void
{
    if (!app_is_sandbox_server()) {
        throw new RuntimeException('This operation is only available on the sandbox server.');
    }
}

function failover_guard_primary_server(): void
{
    if (!app_is_primary_server()) {
        throw new RuntimeException('This operation is only available on the primary server.');
    }
}

function sync_backup_allowed_ip_ranges(): array
{
    if (defined('SYNC_BACKUP_ALLOWED_IPS')) {
        $configured = SYNC_BACKUP_ALLOWED_IPS;
        if (is_array($configured)) {
            return array_values(array_filter(array_map('trim', $configured), static fn ($value): bool => $value !== ''));
        }

        if (is_string($configured) && trim($configured) !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $configured))));
        }
    }

    return [
        '127.0.0.1',
        '::1',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
    ];
}

function sync_backup_request_is_allowed(): bool
{
    $remoteAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($remoteAddress === '') {
        return false;
    }

    foreach (sync_backup_allowed_ip_ranges() as $allowedRange) {
        if (sync_backup_ip_matches($remoteAddress, $allowedRange)) {
            return true;
        }
    }

    return false;
}

function sync_backup_ip_matches(string $remoteAddress, string $allowedRange): bool
{
    if ($remoteAddress === $allowedRange) {
        return true;
    }

    if (!str_contains($allowedRange, '/')) {
        return false;
    }

    [$subnet, $prefixLength] = explode('/', $allowedRange, 2);
    $prefixLength = (int) $prefixLength;
    $remoteBinary = @inet_pton($remoteAddress);
    $subnetBinary = @inet_pton($subnet);
    if ($remoteBinary === false || $subnetBinary === false || strlen($remoteBinary) !== strlen($subnetBinary)) {
        return false;
    }

    $totalBits = strlen($remoteBinary) * 8;
    if ($prefixLength < 0 || $prefixLength > $totalBits) {
        return false;
    }

    $fullBytes = intdiv($prefixLength, 8);
    $remainingBits = $prefixLength % 8;
    if ($fullBytes > 0 && substr($remoteBinary, 0, $fullBytes) !== substr($subnetBinary, 0, $fullBytes)) {
        return false;
    }

    if ($remainingBits === 0) {
        return true;
    }

    $mask = (0xFF << (8 - $remainingBits)) & 0xFF;
    return (ord($remoteBinary[$fullBytes]) & $mask) === (ord($subnetBinary[$fullBytes]) & $mask);
}

function sync_backup_ensure_requirements(): void
{
    failover_guard_sandbox_server();

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

    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL extension is not available.');
    }

    if (!function_exists('openssl_decrypt')) {
        throw new RuntimeException('PHP OpenSSL extension is not available.');
    }

    if (!function_exists('simplexml_load_string')) {
        throw new RuntimeException('PHP SimpleXML extension is not available.');
    }
}

function sync_backup_pull_latest_into_database(): array
{
    sync_backup_ensure_requirements();

    $tmpDir = BASE_PATH . '/storage/tmp';
    if (!app_ensure_directory($tmpDir)) {
        throw new RuntimeException('Cannot create temporary directory: ' . $tmpDir);
    }

    $latestObject = sync_backup_find_latest_backup_object();
    if ($latestObject === null) {
        throw new RuntimeException('No encrypted backup file was found in S3 backups/.');
    }

    $encryptedPath = $tmpDir . '/' . basename($latestObject['key']);
    $decryptedPath = $tmpDir . '/' . pathinfo($latestObject['key'], PATHINFO_FILENAME) . '.raw.sql';

    try {
        sync_backup_download_object($latestObject['key'], $encryptedPath);

        $encryptedPayload = @file_get_contents($encryptedPath);
        if ($encryptedPayload === false || $encryptedPayload === '') {
            throw new RuntimeException('Cannot read downloaded encrypted backup file.');
        }

        $sqlContents = sync_backup_decrypt_payload($encryptedPayload);
        if (@file_put_contents($decryptedPath, $sqlContents, LOCK_EX) === false) {
            throw new RuntimeException('Cannot write decrypted SQL file into temporary storage.');
        }

        $pdo = Database::connection();
        sync_schema_reset_database($pdo);
        sync_backup_import_sql($pdo, $sqlContents);
        $bootstrapResult = sync_change_log_install_infrastructure($pdo);
        @touch(sync_change_log_bootstrap_marker_file());

        return [
            'object_key' => $latestObject['key'],
            'last_modified' => $latestObject['last_modified'],
            'downloaded_file' => basename($encryptedPath),
            'change_log_bootstrap' => $bootstrapResult,
        ];
    } finally {
        sync_backup_cleanup_temp_files([$encryptedPath, $decryptedPath]);
    }
}

function sync_schema_reset_database(PDO $pdo): void
{
    $schemaName = (string) DB_NAME;
    $viewsStatement = $pdo->prepare(
        'SELECT TABLE_NAME FROM information_schema.views WHERE table_schema = :schema_name ORDER BY TABLE_NAME ASC'
    );
    $viewsStatement->execute(['schema_name' => $schemaName]);
    $views = $viewsStatement->fetchAll(PDO::FETCH_COLUMN);

    $tablesStatement = $pdo->prepare(
        'SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = :schema_name AND table_type = :table_type ORDER BY TABLE_NAME ASC'
    );
    $tablesStatement->execute([
        'schema_name' => $schemaName,
        'table_type' => 'BASE TABLE',
    ]);
    $tables = $tablesStatement->fetchAll(PDO::FETCH_COLUMN);

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    try {
        foreach ($views as $viewName) {
            $normalizedViewName = sync_change_log_quote_identifier((string) $viewName);
            $pdo->exec('DROP VIEW IF EXISTS ' . $normalizedViewName);
        }

        foreach ($tables as $tableName) {
            $normalizedTableName = sync_change_log_quote_identifier((string) $tableName);
            $pdo->exec('DROP TABLE IF EXISTS ' . $normalizedTableName);
        }
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}

function sync_backup_find_latest_backup_object(): ?array
{
    $response = sync_backup_send_s3_request('GET', '', hash('sha256', ''), [
        'content-type' => 'application/xml',
    ], null, [
        'list-type' => '2',
        'prefix' => 'backups/',
        'max-keys' => '1000',
    ]);

    if ($response['http_code'] < 200 || $response['http_code'] >= 300) {
        throw new RuntimeException(
            'S3 list request failed with HTTP ' . $response['http_code'] . ': ' . sync_backup_truncate_response($response['body'])
        );
    }

    $xml = @simplexml_load_string($response['body']);
    if (!$xml instanceof SimpleXMLElement) {
        throw new RuntimeException('Unable to parse S3 list response XML.');
    }

    $latestObject = null;
    foreach ($xml->Contents as $content) {
        $key = trim((string) ($content->Key ?? ''));
        $lastModified = trim((string) ($content->LastModified ?? ''));
        if ($key === '' || $lastModified === '' || !str_ends_with(strtolower($key), '.sql')) {
            continue;
        }

        $timestamp = strtotime($lastModified);
        if ($timestamp === false) {
            continue;
        }

        if ($latestObject === null || $timestamp > $latestObject['timestamp']) {
            $latestObject = [
                'key' => $key,
                'last_modified' => $lastModified,
                'timestamp' => $timestamp,
            ];
        }
    }

    return $latestObject;
}

function sync_backup_download_object(string $objectKey, string $targetPath): void
{
    $response = sync_backup_send_s3_request('GET', $objectKey, hash('sha256', ''), [
        'content-type' => 'application/octet-stream',
    ]);

    if ($response['http_code'] < 200 || $response['http_code'] >= 300) {
        throw new RuntimeException(
            'S3 download request failed with HTTP ' . $response['http_code'] . ': ' . sync_backup_truncate_response($response['body'])
        );
    }

    if (@file_put_contents($targetPath, $response['body'], LOCK_EX) === false) {
        throw new RuntimeException('Cannot save downloaded backup file into temporary storage.');
    }
}

function sync_backup_decrypt_payload(string $encryptedPayload): string
{
    $key = hash('sha256', (string) APP_SECRET, true);
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    if ($ivLength === false || $ivLength <= 0) {
        throw new RuntimeException('Cannot determine IV length for AES-256-CBC.');
    }

    if (strlen($encryptedPayload) <= $ivLength) {
        throw new RuntimeException('Encrypted backup payload is invalid or truncated.');
    }

    $iv = substr($encryptedPayload, 0, $ivLength);
    $ciphertext = substr($encryptedPayload, $ivLength);
    $sqlContents = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    if (!is_string($sqlContents) || $sqlContents === '') {
        throw new RuntimeException('Unable to decrypt backup payload with APP_SECRET.');
    }

    return $sqlContents;
}

function sync_backup_import_sql(PDO $pdo, string $sqlContents): void
{
    $sqlContents = trim($sqlContents);
    if ($sqlContents === '') {
        throw new RuntimeException('Decrypted SQL content is empty.');
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    try {
        $pdo->exec($sqlContents);
    } catch (PDOException $exception) {
        throw new RuntimeException('MySQL import failed: ' . $exception->getMessage(), 0, $exception);
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}

function sync_backup_send_s3_request(
    string $method,
    string $objectKey,
    string $payloadHash,
    array $extraHeaders,
    ?string $body = null,
    array $queryParameters = []
): array {
    $endpoint = s3_endpoint();
    $bucket = s3_bucket();
    $region = s3_region();
    $accessKey = s3_access_key();
    $secretKey = s3_secret_key();
    $objectKey = s3_normalize_object_key($objectKey);
    $encodedObjectKey = $objectKey !== '' ? s3_encode_object_key($objectKey) : '';
    $amzDate = gmdate('Ymd\THis\Z');
    $dateStamp = gmdate('Ymd');
    $uriPath = s3_use_path_style()
        ? '/' . rawurlencode($bucket) . ($encodedObjectKey !== '' ? '/' . $encodedObjectKey : '')
        : '/' . $encodedObjectKey;

    $parsedEndpoint = parse_url($endpoint);
    if (!is_array($parsedEndpoint) || empty($parsedEndpoint['scheme']) || empty($parsedEndpoint['host'])) {
        throw new RuntimeException('S3 endpoint is invalid: ' . $endpoint);
    }

    $host = s3_use_path_style() ? $parsedEndpoint['host'] : $bucket . '.' . $parsedEndpoint['host'];
    $port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
    $basePath = isset($parsedEndpoint['path']) ? rtrim($parsedEndpoint['path'], '/') : '';
    $canonicalUri = ($basePath !== '' ? $basePath : '') . $uriPath;
    $canonicalQuery = sync_backup_build_canonical_query($queryParameters);
    $requestUrl = $parsedEndpoint['scheme'] . '://' . $host . $port . $canonicalUri . ($canonicalQuery !== '' ? '?' . $canonicalQuery : '');

    $headersToSign = array_merge($extraHeaders, [
        'host' => $host . $port,
        'x-amz-content-sha256' => $payloadHash,
        'x-amz-date' => $amzDate,
    ]);

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
        strtoupper($method),
        $canonicalUri,
        $canonicalQuery,
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
    $signature = hash_hmac('sha256', $stringToSign, s3_signing_key($secretKey, $dateStamp, $region, 's3'));
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
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 15,
    ];

    if ($body !== null) {
        $curlOptions[CURLOPT_POSTFIELDS] = $body;
    }

    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('S3 request failed: ' . $curlError);
    }

    return [
        'http_code' => $httpCode,
        'body' => substr($response, $headerSize),
    ];
}

function sync_backup_build_canonical_query(array $queryParameters): string
{
    if ($queryParameters === []) {
        return '';
    }

    ksort($queryParameters);
    $parts = [];
    foreach ($queryParameters as $name => $value) {
        $parts[] = rawurlencode((string) $name) . '=' . rawurlencode((string) $value);
    }

    return implode('&', $parts);
}

function sync_backup_truncate_response(string $responseBody): string
{
    $preview = trim(substr($responseBody, 0, 1000));
    return $preview !== '' ? $preview : '(empty response)';
}

function sync_backup_cleanup_temp_files(array $paths): void
{
    foreach ($paths as $path) {
        $normalizedPath = trim((string) $path);
        if ($normalizedPath !== '' && is_file($normalizedPath)) {
            @unlink($normalizedPath);
        }
    }
}

function sync_change_log_table_name(): string
{
    return 'sync_change_log';
}

function sync_change_log_supported_operations(): array
{
    return ['INSERT', 'UPDATE', 'DELETE'];
}

function sync_change_log_excluded_tables(): array
{
    return [
        sync_change_log_table_name(),
        'password_reset_tokens',
        'email_outbox',
        'notification_reads',
    ];
}

function sync_change_log_quote_identifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function sync_change_log_base_tables(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT TABLE_NAME
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_type = 'BASE TABLE'
         ORDER BY TABLE_NAME ASC"
    );

    $tables = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $tableName) {
        $name = trim((string) $tableName);
        if ($name === '' || in_array($name, sync_change_log_excluded_tables(), true)) {
            continue;
        }

        $tables[] = $name;
    }

    return $tables;
}

function sync_change_log_single_primary_key_column(PDO $pdo, string $tableName): ?string
{
    $stmt = $pdo->prepare(
        "SELECT COLUMN_NAME
         FROM information_schema.key_column_usage
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
           AND constraint_name = 'PRIMARY'
         ORDER BY ORDINAL_POSITION ASC"
    );
    $stmt->execute(['table_name' => $tableName]);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($columns) !== 1) {
        return null;
    }

    $column = trim((string) ($columns[0] ?? ''));
    return $column !== '' ? $column : null;
}

function sync_change_log_table_columns(PDO $pdo, string $tableName): array
{
    $stmt = $pdo->prepare(
        "SELECT COLUMN_NAME
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
         ORDER BY ORDINAL_POSITION ASC"
    );
    $stmt->execute(['table_name' => $tableName]);

    $columns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $columnName) {
        $name = trim((string) $columnName);
        if ($name !== '') {
            $columns[] = $name;
        }
    }

    return $columns;
}

function sync_change_log_trigger_creation_is_forbidden(Throwable $exception): bool
{
    $message = strtolower(trim($exception->getMessage()));
    $code = (string) $exception->getCode();

    if ($code === '1419') {
        return true;
    }

    return str_contains($message, 'super privilege')
        || str_contains($message, 'binary logging is enabled')
        || str_contains($message, 'log_bin_trust_function_creators');
}

function sync_change_log_drop_existing_triggers(PDO $pdo): void
{
    foreach (sync_change_log_base_tables($pdo) as $tableName) {
        $pdo->exec('DROP TRIGGER IF EXISTS ' . sync_change_log_quote_identifier(sync_change_log_trigger_name($tableName, 'ai')));
        $pdo->exec('DROP TRIGGER IF EXISTS ' . sync_change_log_quote_identifier(sync_change_log_trigger_name($tableName, 'au')));
        $pdo->exec('DROP TRIGGER IF EXISTS ' . sync_change_log_quote_identifier(sync_change_log_trigger_name($tableName, 'ad')));
    }
}

function sync_change_log_json_object_expression(array $columns, string $rowAlias): string
{
    $parts = [];
    foreach ($columns as $columnName) {
        $parts[] = "'" . str_replace("'", "\\'", $columnName) . "'";
        $parts[] = $rowAlias . '.' . sync_change_log_quote_identifier($columnName);
    }

    if ($parts === []) {
        return "JSON_OBJECT('empty', 1)";
    }

    return 'JSON_OBJECT(' . implode(', ', $parts) . ')';
}

function sync_change_log_trigger_name(string $tableName, string $suffix): string
{
    return 'trg_sync_' . substr(md5($tableName . '_' . $suffix), 0, 20);
}

function sync_change_log_ensure_schema(PDO $pdo): void
{
    $tableName = sync_change_log_quote_identifier(sync_change_log_table_name());
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            table_name VARCHAR(128) NOT NULL,
            primary_key_column VARCHAR(128) NOT NULL,
            primary_key_value VARCHAR(255) DEFAULT NULL,
            operation ENUM('INSERT','UPDATE','DELETE') NOT NULL,
            row_before LONGTEXT NULL,
            row_after LONGTEXT NULL,
            changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            export_batch_token VARCHAR(96) NULL DEFAULT NULL,
            export_batch_created_at DATETIME NULL DEFAULT NULL,
            exported_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_sync_change_log_batch (export_batch_token, exported_at, id),
            KEY idx_sync_change_log_exported (exported_at, id),
            KEY idx_sync_change_log_table (table_name, changed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    sync_change_log_ensure_column($pdo, sync_change_log_table_name(), 'export_batch_token', 'ALTER TABLE ' . $tableName . ' ADD COLUMN export_batch_token VARCHAR(96) NULL DEFAULT NULL AFTER changed_at');
    sync_change_log_ensure_column($pdo, sync_change_log_table_name(), 'export_batch_created_at', 'ALTER TABLE ' . $tableName . ' ADD COLUMN export_batch_created_at DATETIME NULL DEFAULT NULL AFTER export_batch_token');
}

function sync_change_log_ensure_column(PDO $pdo, string $tableName, string $columnName, string $ddl): void
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
           AND column_name = :column_name"
    );
    $stmt->execute([
        'table_name' => $tableName,
        'column_name' => $columnName,
    ]);

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec($ddl);
    }
}

function sync_change_log_install_infrastructure(PDO $pdo): array
{
    failover_guard_sandbox_server();

    sync_change_log_ensure_schema($pdo);
    $trackedTables = [];
    $skippedTables = [];
    $mode = 'trigger';

    try {
        foreach (sync_change_log_base_tables($pdo) as $tableName) {
            $primaryKeyColumn = sync_change_log_single_primary_key_column($pdo, $tableName);
            if ($primaryKeyColumn === null) {
                $skippedTables[] = $tableName;
                continue;
            }

            $columns = sync_change_log_table_columns($pdo, $tableName);
            if ($columns === []) {
                $skippedTables[] = $tableName;
                continue;
            }

            $quotedTableName = sync_change_log_quote_identifier($tableName);
            $logTableName = sync_change_log_quote_identifier(sync_change_log_table_name());
            $pkIdentifier = sync_change_log_quote_identifier($primaryKeyColumn);
            $insertTrigger = sync_change_log_trigger_name($tableName, 'ai');
            $updateTrigger = sync_change_log_trigger_name($tableName, 'au');
            $deleteTrigger = sync_change_log_trigger_name($tableName, 'ad');

            $pdo->exec('DROP TRIGGER IF EXISTS ' . sync_change_log_quote_identifier($insertTrigger));
            $pdo->exec('DROP TRIGGER IF EXISTS ' . sync_change_log_quote_identifier($updateTrigger));
            $pdo->exec('DROP TRIGGER IF EXISTS ' . sync_change_log_quote_identifier($deleteTrigger));

            $insertJson = sync_change_log_json_object_expression($columns, 'NEW');
            $updateBeforeJson = sync_change_log_json_object_expression($columns, 'OLD');
            $updateAfterJson = sync_change_log_json_object_expression($columns, 'NEW');
            $deleteJson = sync_change_log_json_object_expression($columns, 'OLD');

            $pdo->exec(
                'CREATE TRIGGER ' . sync_change_log_quote_identifier($insertTrigger)
                . ' AFTER INSERT ON ' . $quotedTableName . ' FOR EACH ROW '
                . 'INSERT INTO ' . $logTableName . ' (table_name, primary_key_column, primary_key_value, operation, row_before, row_after, changed_at) VALUES ('
                . $pdo->quote($tableName) . ', '
                . $pdo->quote($primaryKeyColumn) . ', '
                . 'CAST(NEW.' . $pkIdentifier . ' AS CHAR), '
                . "'INSERT', NULL, {$insertJson}, NOW())"
            );

            $pdo->exec(
                'CREATE TRIGGER ' . sync_change_log_quote_identifier($updateTrigger)
                . ' AFTER UPDATE ON ' . $quotedTableName . ' FOR EACH ROW '
                . 'INSERT INTO ' . $logTableName . ' (table_name, primary_key_column, primary_key_value, operation, row_before, row_after, changed_at) VALUES ('
                . $pdo->quote($tableName) . ', '
                . $pdo->quote($primaryKeyColumn) . ', '
                . 'CAST(NEW.' . $pkIdentifier . ' AS CHAR), '
                . "'UPDATE', {$updateBeforeJson}, {$updateAfterJson}, NOW())"
            );

            $pdo->exec(
                'CREATE TRIGGER ' . sync_change_log_quote_identifier($deleteTrigger)
                . ' AFTER DELETE ON ' . $quotedTableName . ' FOR EACH ROW '
                . 'INSERT INTO ' . $logTableName . ' (table_name, primary_key_column, primary_key_value, operation, row_before, row_after, changed_at) VALUES ('
                . $pdo->quote($tableName) . ', '
                . $pdo->quote($primaryKeyColumn) . ', '
                . 'CAST(OLD.' . $pkIdentifier . ' AS CHAR), '
                . "'DELETE', {$deleteJson}, NULL, NOW())"
            );

            $trackedTables[] = $tableName;
        }
    } catch (Throwable $exception) {
        if (!sync_change_log_trigger_creation_is_forbidden($exception)) {
            throw $exception;
        }

        sync_change_log_drop_existing_triggers($pdo);
        $mode = 'app';
        $trackedTables = [];
        $skippedTables = [];
        foreach (sync_change_log_base_tables($pdo) as $tableName) {
            if (sync_change_log_single_primary_key_column($pdo, $tableName) !== null && sync_change_log_table_columns($pdo, $tableName) !== []) {
                $trackedTables[] = $tableName;
            } else {
                $skippedTables[] = $tableName;
            }
        }
    }

    sync_change_log_set_mode($mode);

    return [
        'tracked_tables' => $trackedTables,
        'skipped_tables' => $skippedTables,
        'mode' => $mode,
    ];
}

function sync_change_log_infrastructure_needs_install(PDO $pdo): bool
{
    sync_change_log_ensure_schema($pdo);

    if (sync_change_log_mode() === 'app') {
        return false;
    }

    $trackedTableCount = 0;
    foreach (sync_change_log_base_tables($pdo) as $tableName) {
        if (sync_change_log_single_primary_key_column($pdo, $tableName) !== null) {
            $trackedTableCount++;
        }
    }

    if ($trackedTableCount === 0) {
        return false;
    }

    $expectedTriggerCount = $trackedTableCount * 3;
    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM information_schema.triggers
         WHERE trigger_schema = DATABASE()
           AND trigger_name LIKE 'trg_sync_%'"
    );
    $actualTriggerCount = (int) $stmt->fetchColumn();

    return $actualTriggerCount !== $expectedTriggerCount;
}

function sync_change_log_status(PDO $pdo, int $recentLimit = 12): array
{
    sync_change_log_ensure_schema($pdo);

    $pendingCount = (int) $pdo->query(
        'SELECT COUNT(*) FROM ' . sync_change_log_quote_identifier(sync_change_log_table_name()) . ' WHERE exported_at IS NULL'
    )->fetchColumn();
    $reservedCount = (int) $pdo->query(
        'SELECT COUNT(*) FROM ' . sync_change_log_quote_identifier(sync_change_log_table_name()) . ' WHERE exported_at IS NULL AND export_batch_token IS NOT NULL'
    )->fetchColumn();

    $lastChangeAt = $pdo->query(
        'SELECT MAX(changed_at) FROM ' . sync_change_log_quote_identifier(sync_change_log_table_name())
    )->fetchColumn();

    $recentStmt = $pdo->prepare(
        'SELECT id, table_name, primary_key_column, primary_key_value, operation, changed_at, exported_at
         FROM ' . sync_change_log_quote_identifier(sync_change_log_table_name()) . '
         ORDER BY id DESC
         LIMIT :limit_rows'
    );
    $recentStmt->bindValue(':limit_rows', $recentLimit, PDO::PARAM_INT);
    $recentStmt->execute();

    return [
        'pending_count' => $pendingCount,
        'reserved_count' => $reservedCount,
        'last_change_at' => is_string($lastChangeAt) ? $lastChangeAt : '',
        'recent_changes' => $recentStmt->fetchAll(),
        'tracked_candidates' => sync_change_log_base_tables($pdo),
        'mode' => sync_change_log_mode(),
    ];
}

function sync_change_log_prepare_statement_context(PDO $pdo, string $sql, array $params = []): ?array
{
    if (!sync_change_log_use_application_driver($pdo)) {
        return null;
    }

    $normalizedSql = trim($sql);
    if ($normalizedSql === '') {
        return null;
    }

    if (!preg_match('/^(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+`?([a-zA-Z0-9_]+)`?/i', $normalizedSql, $matches)) {
        return null;
    }

    $verb = strtoupper(trim((string) ($matches[1] ?? '')));
    $tableName = trim((string) ($matches[2] ?? ''));
    if ($tableName === '' || in_array($tableName, sync_change_log_excluded_tables(), true)) {
        return null;
    }

    $primaryKeyColumn = sync_change_log_single_primary_key_column($pdo, $tableName);
    if ($primaryKeyColumn === null) {
        return null;
    }

    $operation = str_starts_with($verb, 'INSERT') ? 'INSERT' : (str_starts_with($verb, 'UPDATE') ? 'UPDATE' : 'DELETE');
    $primaryKeyValue = sync_change_log_extract_primary_key_value_from_params($primaryKeyColumn, $params);
    if (in_array($operation, ['UPDATE', 'DELETE'], true) && $primaryKeyValue === null) {
        return null;
    }

    return [
        'operation' => $operation,
        'table_name' => $tableName,
        'primary_key_column' => $primaryKeyColumn,
        'primary_key_value' => $primaryKeyValue,
    ];
}

function sync_change_log_finalize_statement_context(
    PDO $pdo,
    ?array $context,
    int $affectedRows,
    array $params = [],
    ?string $businessInsertId = null
): void
{
    if ($context === null || $affectedRows <= 0) {
        return;
    }

    $operation = strtoupper(trim((string) ($context['operation'] ?? '')));
    $tableName = trim((string) ($context['table_name'] ?? ''));
    $primaryKeyColumn = trim((string) ($context['primary_key_column'] ?? ''));
    $primaryKeyValue = $context['primary_key_value'] ?? null;

    if ($operation === 'INSERT') {
        if ($businessInsertId !== null && $businessInsertId !== '') {
            $primaryKeyValue = $businessInsertId;
        } elseif ($primaryKeyValue === null) {
            $primaryKeyValue = sync_change_log_extract_primary_key_value_from_params($primaryKeyColumn, $params);
        }

        if ($primaryKeyValue === null) {
            return;
        }

        $afterRow = sync_change_log_fetch_row_by_primary_key($pdo, $tableName, $primaryKeyColumn, $primaryKeyValue);
        if ($afterRow === null) {
            return;
        }

        sync_change_log_append_entry($pdo, $tableName, $primaryKeyColumn, $primaryKeyValue, 'INSERT', null, $afterRow);
        return;
    }

    if ($primaryKeyValue === null) {
        return;
    }

    if ($operation === 'UPDATE') {
        $afterRow = sync_change_log_fetch_row_by_primary_key($pdo, $tableName, $primaryKeyColumn, $primaryKeyValue);
        if ($afterRow === null) {
            return;
        }

        sync_change_log_append_entry($pdo, $tableName, $primaryKeyColumn, $primaryKeyValue, 'UPDATE', null, $afterRow);
        return;
    }

    if ($operation === 'DELETE') {
        sync_change_log_append_entry($pdo, $tableName, $primaryKeyColumn, $primaryKeyValue, 'DELETE', null, null);
    }
}

function sync_change_log_execute_statement(PDO $pdo, string $sql, array $params, callable $executor, ?int &$statementInsertId = null): int
{
    $statementInsertId = null;
    $context = sync_change_log_prepare_statement_context($pdo, $sql, $params);
    if ($context === null) {
        $affectedRows = (int) $executor();
        if ($affectedRows > 0 && preg_match('/^\s*INSERT\s+INTO\b/i', $sql)) {
            $capturedInsertId = (string) $pdo->lastInsertId();
            if ($capturedInsertId !== '' && $capturedInsertId !== '0' && ctype_digit($capturedInsertId)) {
                $statementInsertId = (int) $capturedInsertId;
            }
        }

        return $affectedRows;
    }

    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }

    $businessInsertId = null;
    $affectedRows = 0;

    try {
        $affectedRows = (int) $executor();

        if ($affectedRows > 0 && strtoupper(trim((string) ($context['operation'] ?? ''))) === 'INSERT') {
            $capturedInsertId = (string) $pdo->lastInsertId();
            if ($capturedInsertId !== '' && $capturedInsertId !== '0' && ctype_digit($capturedInsertId)) {
                $businessInsertId = $capturedInsertId;
                $statementInsertId = (int) $capturedInsertId;
            }
        }

        try {
            sync_change_log_finalize_statement_context($pdo, $context, $affectedRows, $params, $businessInsertId);
        } catch (Throwable $loggingException) {
            app_log('warning', 'App-mode sync change log write failed', [
                'error' => $loggingException->getMessage(),
                'sql_preview' => substr(trim($sql), 0, 240),
                'server_role' => app_server_role(),
            ]);
        }

        if ($businessInsertId !== null) {
            $pdo->query('SELECT LAST_INSERT_ID(' . $businessInsertId . ')');
        }

        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->commit();
        }

        return $affectedRows;
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function sync_change_log_extract_primary_key_value_from_params(string $primaryKeyColumn, array $params): mixed
{
    $candidateKeys = [$primaryKeyColumn, 'id', ':' . $primaryKeyColumn, ':id'];
    foreach ($candidateKeys as $candidateKey) {
        if (array_key_exists($candidateKey, $params)) {
            return $params[$candidateKey];
        }
    }

    return null;
}

function sync_change_log_fetch_row_by_primary_key(PDO $pdo, string $tableName, string $primaryKeyColumn, mixed $primaryKeyValue): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM ' . sync_change_log_quote_identifier($tableName)
        . ' WHERE ' . sync_change_log_quote_identifier($primaryKeyColumn) . ' = :primary_key_value LIMIT 1'
    );
    $stmt->execute(['primary_key_value' => $primaryKeyValue]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function sync_change_log_append_entry(
    PDO $pdo,
    string $tableName,
    string $primaryKeyColumn,
    mixed $primaryKeyValue,
    string $operation,
    ?array $rowBefore,
    ?array $rowAfter
): void {
    $stmt = $pdo->prepare(
        'INSERT INTO ' . sync_change_log_quote_identifier(sync_change_log_table_name()) . '
        (table_name, primary_key_column, primary_key_value, operation, row_before, row_after, changed_at)
        VALUES (:table_name, :primary_key_column, :primary_key_value, :operation, :row_before, :row_after, NOW())'
    );
    $stmt->execute([
        'table_name' => $tableName,
        'primary_key_column' => $primaryKeyColumn,
        'primary_key_value' => $primaryKeyValue !== null ? (string) $primaryKeyValue : null,
        'operation' => strtoupper($operation),
        'row_before' => $rowBefore !== null ? json_encode($rowBefore, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        'row_after' => $rowAfter !== null ? json_encode($rowAfter, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
    ]);
}

function sync_change_log_export_pending_sql(PDO $pdo, bool $markExported = false, int $limit = 5000): array
{
    failover_guard_sandbox_server();
    sync_change_log_ensure_schema($pdo);

    $batchToken = sync_change_log_reserve_batch($pdo, $limit);
    $rows = sync_change_log_batch_rows($pdo, $batchToken, $limit);

    $lines = [
        '-- SYNC_BATCH_TOKEN: ' . $batchToken,
        '-- SYNC_BATCH_MODE: incremental',
        '-- SYNC_CHANGE_COUNT: ' . count($rows),
        'SET FOREIGN_KEY_CHECKS=0;',
    ];
    $exportedIds = [];

    foreach ($rows as $row) {
        $statement = sync_change_log_build_sql_statement($pdo, is_array($row) ? $row : []);
        if ($statement === '') {
            continue;
        }

        $lines[] = $statement;
        $exportedIds[] = (int) ($row['id'] ?? 0);
    }

    $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
    $sql = implode("\n", $lines) . "\n";

    if ($markExported && $exportedIds !== []) {
        sync_change_log_acknowledge_batch($pdo, $batchToken);
    }

    return [
        'batch_token' => $batchToken,
        'sql' => $sql,
        'exported_count' => count($exportedIds),
    ];
}

function sync_change_log_reserve_batch(PDO $pdo, int $limit): string
{
    $logTable = sync_change_log_quote_identifier(sync_change_log_table_name());
    $existingBatchStmt = $pdo->query(
        'SELECT export_batch_token
         FROM ' . $logTable . '
         WHERE exported_at IS NULL
           AND export_batch_token IS NOT NULL
         ORDER BY id ASC
         LIMIT 1'
    );
    $existingBatchToken = trim((string) $existingBatchStmt->fetchColumn());
    if ($existingBatchToken !== '') {
        return $existingBatchToken;
    }

    $batchToken = bin2hex(random_bytes(16));
    $reserveStmt = $pdo->prepare(
        'UPDATE ' . $logTable . '
         SET export_batch_token = :batch_token,
             export_batch_created_at = NOW()
         WHERE exported_at IS NULL
           AND export_batch_token IS NULL
         ORDER BY id ASC
         LIMIT ' . max(1, $limit)
    );
    $reserveStmt->execute(['batch_token' => $batchToken]);

    $checkStmt = $pdo->prepare(
        'SELECT COUNT(*) FROM ' . $logTable . '
         WHERE export_batch_token = :batch_token
           AND exported_at IS NULL'
    );
    $checkStmt->execute(['batch_token' => $batchToken]);
    if ((int) $checkStmt->fetchColumn() === 0) {
        return $batchToken;
    }

    return $batchToken;
}

function sync_change_log_batch_rows(PDO $pdo, string $batchToken, int $limit): array
{
    $stmt = $pdo->prepare(
        'SELECT id, table_name, primary_key_column, primary_key_value, operation, row_before, row_after
         FROM ' . sync_change_log_quote_identifier(sync_change_log_table_name()) . '
         WHERE exported_at IS NULL
           AND export_batch_token = :batch_token
         ORDER BY id ASC
         LIMIT :limit_rows'
    );
    $stmt->bindValue(':batch_token', $batchToken, PDO::PARAM_STR);
    $stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function sync_change_log_acknowledge_batch(PDO $pdo, string $batchToken): array
{
    failover_guard_sandbox_server();
    sync_change_log_ensure_schema($pdo);

    $normalizedToken = trim($batchToken);
    if ($normalizedToken === '') {
        throw new RuntimeException('Sync batch token is required.');
    }

    $stmt = $pdo->prepare(
        'UPDATE ' . sync_change_log_quote_identifier(sync_change_log_table_name()) . '
         SET exported_at = NOW()
         WHERE exported_at IS NULL
           AND export_batch_token = :batch_token'
    );
    $stmt->execute(['batch_token' => $normalizedToken]);

    $updatedCount = (int) $stmt->rowCount();
    if ($updatedCount > 0) {
        $cleanupStmt = $pdo->prepare(
            'UPDATE ' . sync_change_log_quote_identifier(sync_change_log_table_name()) . '
             SET export_batch_token = NULL,
                 export_batch_created_at = NULL
             WHERE export_batch_token = :batch_token'
        );
        $cleanupStmt->execute(['batch_token' => $normalizedToken]);
    }

    return [
        'batch_token' => $normalizedToken,
        'acknowledged_count' => $updatedCount,
    ];
}

function sync_change_log_extract_batch_token(string $sqlContents): string
{
    if (preg_match('/^\s*--\s*SYNC_BATCH_TOKEN:\s*([a-f0-9]{16,96})\s*$/mi', $sqlContents, $matches)) {
        return strtolower(trim((string) ($matches[1] ?? '')));
    }

    return '';
}

function sync_change_log_acknowledgement_url(): string
{
    return trim((string) (defined('SANDBOX_SYNC_ACK_URL') ? SANDBOX_SYNC_ACK_URL : ''));
}

function sync_change_log_acknowledgement_token(): string
{
    $configured = trim((string) (defined('SANDBOX_SYNC_ACK_TOKEN') ? SANDBOX_SYNC_ACK_TOKEN : ''));
    if ($configured !== '') {
        return $configured;
    }

    return trim((string) (defined('INTERNAL_API_TOKEN') ? INTERNAL_API_TOKEN : ''));
}

function sync_change_log_acknowledge_remote_batch(string $batchToken): array
{
    $ackUrl = sync_change_log_acknowledgement_url();
    $ackToken = sync_change_log_acknowledgement_token();
    if ($ackUrl === '' || $ackToken === '') {
        return [
            'acknowledged' => false,
            'reason' => 'missing_configuration',
        ];
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL extension is not available for sync acknowledgement.');
    }

    $curl = curl_init($ackUrl);
    if ($curl === false) {
        throw new RuntimeException('Cannot initialize cURL for sync acknowledgement.');
    }

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'action' => 'acknowledge-change-batch',
            'batch_token' => $batchToken,
            'internal_token' => $ackToken,
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Internal-Token: ' . $ackToken,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 15,
    ]);

    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('Sync acknowledgement request failed: ' . $curlError);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new RuntimeException('Sync acknowledgement endpoint returned HTTP ' . $httpCode . ': ' . trim(substr($response, 0, 1000)));
    }

    return [
        'acknowledged' => true,
        'http_code' => $httpCode,
        'response_preview' => trim(substr($response, 0, 500)),
    ];
}

function sync_change_log_build_sql_statement(PDO $pdo, array $row): string
{
    $tableName = trim((string) ($row['table_name'] ?? ''));
    $primaryKeyColumn = trim((string) ($row['primary_key_column'] ?? ''));
    $operation = strtoupper(trim((string) ($row['operation'] ?? '')));
    $rowBefore = json_decode((string) ($row['row_before'] ?? ''), true);
    $rowAfter = json_decode((string) ($row['row_after'] ?? ''), true);

    if ($tableName === '' || $primaryKeyColumn === '' || !in_array($operation, sync_change_log_supported_operations(), true)) {
        return '';
    }

    $quotedTable = sync_change_log_quote_identifier($tableName);
    $quotedPrimaryKey = sync_change_log_quote_identifier($primaryKeyColumn);

    if ($operation === 'INSERT' && is_array($rowAfter)) {
        return sync_change_log_build_upsert_statement($pdo, $quotedTable, $rowAfter);
    }

    if ($operation === 'UPDATE' && is_array($rowAfter)) {
        return sync_change_log_build_upsert_statement($pdo, $quotedTable, $rowAfter);
    }

    if ($operation === 'DELETE') {
        $primaryKeyValue = is_array($rowBefore) ? ($rowBefore[$primaryKeyColumn] ?? $row['primary_key_value'] ?? null) : ($row['primary_key_value'] ?? null);
        return 'DELETE FROM ' . $quotedTable
            . ' WHERE ' . $quotedPrimaryKey . ' = ' . sync_change_log_sql_literal($pdo, $primaryKeyValue) . ' LIMIT 1;';
    }

    return '';
}

function sync_change_log_sql_literal(PDO $pdo, mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    return $pdo->quote((string) $value);
}

function sync_change_log_build_upsert_statement(PDO $pdo, string $quotedTable, array $rowData): string
{
    if ($rowData === []) {
        return '';
    }

    $columns = array_keys($rowData);
    $quotedColumns = array_map('sync_change_log_quote_identifier', $columns);
    $values = array_map(static fn ($value): string => sync_change_log_sql_literal($pdo, $value), array_values($rowData));
    $assignments = [];

    foreach ($columns as $columnName) {
        $quotedColumn = sync_change_log_quote_identifier((string) $columnName);
        $assignments[] = $quotedColumn . ' = VALUES(' . $quotedColumn . ')';
    }

    return 'INSERT INTO ' . $quotedTable
        . ' (' . implode(', ', $quotedColumns) . ') VALUES (' . implode(', ', $values) . ')'
        . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $assignments) . ';';
}
