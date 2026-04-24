<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/activities.php';

api_run_action('activities.join', 'api_activities_join_action', page_url('activities-student'));
