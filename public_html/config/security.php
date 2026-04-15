<?php
declare(strict_types=1);

if (!defined('APP_SECRET')) {
    define('APP_SECRET', 'change-this-to-a-long-random-secret');
}

if (!defined('UPLOAD_STORAGE_PATH')) {
    define('UPLOAD_STORAGE_PATH', BASE_PATH . '/assets/uploads');
}

if (!defined('UPLOAD_PUBLIC_BASE_PATH')) {
    define('UPLOAD_PUBLIC_BASE_PATH', '/assets/uploads');
}
