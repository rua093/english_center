<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/models/AcademicModel.php';

$lockDir = dirname(__DIR__) . '/storage/locks';
if (!app_ensure_directory($lockDir)) {
    fwrite(STDERR, "Cannot create lock directory: {$lockDir}\n");
    exit(1);
}

$lockFile = $lockDir . '/sync-overdue-tuition-notifications.lock';
$lockHandle = fopen($lockFile, 'c+');
if ($lockHandle === false) {
    fwrite(STDERR, "Cannot open lock file: {$lockFile}\n");
    exit(1);
}

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "[" . date('Y-m-d H:i:s') . "] Job is already running.\n");
    fclose($lockHandle);
    exit(0);
}

ftruncate($lockHandle, 0);
fwrite($lockHandle, (string) getmypid());
fflush($lockHandle);

try {
    $model = new AcademicModel();
    $createdCount = $model->syncOverdueMonthlyTuitionNotifications();
    fwrite(STDOUT, "[" . date('Y-m-d H:i:s') . "] Created {$createdCount} overdue monthly tuition notification(s).\n");
    $exitCode = 0;
} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Job failed: " . $e->getMessage() . "\n");
    $exitCode = 1;
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

exit($exitCode);
