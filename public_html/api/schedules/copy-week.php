<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/schedules.php';

api_run_action('schedules.copy_week', 'api_schedules_copy_week_action', page_url('schedules-academic'));
