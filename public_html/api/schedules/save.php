<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/schedules.php';

api_run_action('schedules.save', 'api_schedules_save_action', page_url('schedules-academic'));
