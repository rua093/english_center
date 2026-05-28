<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';

try {
    $logDir = defined('APP_LOG_DIR') ? (string) APP_LOG_DIR : (BASE_PATH . '/storage/logs');
    $retentionDays = defined('APP_LOG_RETENTION_DAYS') ? (int) APP_LOG_RETENTION_DAYS : 30;
    $retentionDays = max(1, $retentionDays);
    $cutoff = time() - ($retentionDays * 86400);
    $deleted = 0;
    $skipped = 0;

    if (!is_dir($logDir)) {
        fwrite(STDOUT, "[" . date('Y-m-d H:i:s') . "] Log directory does not exist, nothing to purge.\n");
        exit(0);
    }

    foreach (glob(rtrim($logDir, '/\\') . DIRECTORY_SEPARATOR . '*.log*') ?: [] as $path) {
        if (!is_file($path)) {
            $skipped++;
            continue;
        }

        $mtime = @filemtime($path);
        if ($mtime === false || $mtime >= $cutoff) {
            $skipped++;
            continue;
        }

        if (@unlink($path)) {
            $deleted++;
        } else {
            $skipped++;
        }
    }

    fwrite(STDOUT, "[" . date('Y-m-d H:i:s') . "] Deleted {$deleted} old log file(s), skipped {$skipped}.\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Log purge failed: " . $e->getMessage() . "\n");
    exit(1);
}
