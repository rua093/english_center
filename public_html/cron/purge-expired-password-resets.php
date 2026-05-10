<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/models/tables/PasswordResetTokensTableModel.php';

try {
    $table = new PasswordResetTokensTableModel();
    $deleted = $table->purgeExpired(7);
    fwrite(STDOUT, "[" . date('Y-m-d H:i:s') . "] Deleted {$deleted} expired password reset token(s).\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Purge failed: " . $e->getMessage() . "\n");
    exit(1);
}
