<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/tuitions.php';

api_run_action('tuitions.update', 'api_tuitions_update_action', page_url('dashboard-student'));
