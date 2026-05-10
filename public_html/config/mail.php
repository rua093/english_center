<?php
declare(strict_types=1);

if (!defined('MAIL_ENABLED')) {
    define('MAIL_ENABLED', false);
}

if (!defined('MAIL_TRANSPORT')) {
    define('MAIL_TRANSPORT', 'smtp');
}

if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', '');
}

if (!defined('MAIL_PORT')) {
    define('MAIL_PORT', 587);
}

if (!defined('MAIL_ENCRYPTION')) {
    define('MAIL_ENCRYPTION', 'tls');
}

if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', '');
}

if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', '');
}

if (!defined('MAIL_AUTH_MODE')) {
    define('MAIL_AUTH_MODE', 'login');
}

if (!defined('MAIL_FROM_ADDRESS')) {
    define('MAIL_FROM_ADDRESS', '');
}

if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', APP_NAME);
}

if (!defined('MAIL_REPLY_TO_ADDRESS')) {
    define('MAIL_REPLY_TO_ADDRESS', '');
}

if (!defined('MAIL_REPLY_TO_NAME')) {
    define('MAIL_REPLY_TO_NAME', APP_NAME);
}

if (!defined('MAIL_TIMEOUT')) {
    define('MAIL_TIMEOUT', 15);
}

if (!defined('MAIL_VERIFY_PEER')) {
    define('MAIL_VERIFY_PEER', true);
}

if (!defined('MAIL_MAX_ATTEMPTS')) {
    define('MAIL_MAX_ATTEMPTS', 5);
}

if (!defined('MAIL_BATCH_SIZE')) {
    define('MAIL_BATCH_SIZE', 20);
}

if (!defined('MAIL_INTERNAL_NOTIFICATION_RECIPIENTS')) {
    define('MAIL_INTERNAL_NOTIFICATION_RECIPIENTS', '');
}
