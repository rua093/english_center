<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/logging.php';
require_once __DIR__ . '/config/mail.php';

date_default_timezone_set((string) APP_TIMEZONE);
