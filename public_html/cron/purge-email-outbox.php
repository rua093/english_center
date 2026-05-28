<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/models/tables/EmailOutboxTableModel.php';

try {
    $table = new EmailOutboxTableModel();
    $deletedSent = $table->purgeSentOlderThan(30);
    $deletedFailed = $table->purgeFailedOlderThan(14);

    fwrite(
        STDOUT,
        "[" . date('Y-m-d H:i:s') . "] Deleted {$deletedSent} sent email(s) and {$deletedFailed} failed email(s) from outbox.\n"
    );
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Outbox purge failed: " . $e->getMessage() . "\n");
    exit(1);
}
