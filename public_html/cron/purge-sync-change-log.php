<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';

try {
    $pdo = Database::connection();
    $released = sync_change_log_release_stale_batches($pdo, 180);
    $deleted = sync_change_log_purge_exported($pdo, 14);

    fwrite(
        STDOUT,
        "[" . date('Y-m-d H:i:s') . "] Released {$released} stale sync batch row(s), deleted {$deleted} exported change-log row(s).\n"
    );
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Sync change-log purge failed: " . $e->getMessage() . "\n");
    exit(1);
}
