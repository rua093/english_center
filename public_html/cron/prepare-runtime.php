<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Forbidden\n";
    exit(1);
}

require_once dirname(__DIR__) . '/core/bootstrap.php';

$hasFailure = false;

foreach (app_required_writable_directories() as $directory) {
    $created = app_ensure_directory($directory);
    $exists = is_dir($directory);
    $writable = $exists && is_writable($directory);

    if (!$created || !$writable) {
        $hasFailure = true;
        fwrite(STDERR, sprintf("[FAIL] %s%s", $directory, PHP_EOL));
        continue;
    }

    fwrite(STDOUT, sprintf("[OK] %s%s", $directory, PHP_EOL));
}

exit($hasFailure ? 1 : 0);
