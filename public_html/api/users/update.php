<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../models/tables/UsersTableModel.php';
require_once __DIR__ . '/../canonical/users.php';

api_run_action('users.update', 'api_users_update_action', page_url('profile'));