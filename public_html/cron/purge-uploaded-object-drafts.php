<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';

try {
    $purgeResult = app_uploaded_object_manifest_purge_stale_drafts(24, 200);
    $deletedRecords = app_uploaded_object_manifest_purge_deleted_records(30);

    fwrite(
        STDOUT,
        "[" . date('Y-m-d H:i:s') . "] Deleted {$purgeResult['deleted_objects']} stale draft object(s), marked {$purgeResult['marked_deleted']} manifest row(s), failed {$purgeResult['failed']}, purged {$deletedRecords} deleted manifest row(s).\n"
    );
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Uploaded object draft purge failed: " . $e->getMessage() . "\n");
    exit(1);
}
