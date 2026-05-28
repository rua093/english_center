<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/models/tables/NotificationsTableModel.php';

try {
    $table = new NotificationsTableModel();
    $deleted = $table->purgeOlderThan(90);
    fwrite(STDOUT, "[" . date('Y-m-d H:i:s') . "] Deleted {$deleted} old notification(s).\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Notification purge failed: " . $e->getMessage() . "\n");
    exit(1);
}
