<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/schedules.php';

api_run_action('schedules.delete', 'api_schedules_delete_action', page_url('schedules-academic'));
