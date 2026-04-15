<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/activities.php';

api_run_action('activities.delete', 'api_activities_delete_action', page_url('activities-manage'));
