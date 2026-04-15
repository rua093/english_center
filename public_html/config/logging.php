<?php
declare(strict_types=1);

if (!defined('APP_LOG_DIR')) {
    define('APP_LOG_DIR', BASE_PATH . '/storage/logs');
}

if (!defined('APP_LOG_MAX_BYTES')) {
    define('APP_LOG_MAX_BYTES', 2097152);
}
