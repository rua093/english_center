<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/activities.php';

api_run_action('activities.pay', 'api_activities_pay_action', page_url('activities-student'));
