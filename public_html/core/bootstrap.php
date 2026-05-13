<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/bbcode.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db_helper.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/api_helpers.php';
require_once __DIR__ . '/file_storage.php';
require_once __DIR__ . '/get_version.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/page_routes.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/page_actions.php';

i18n_bootstrap();
sync_auth_permissions();
