<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/assignments.php';

api_run_action('assignments.submit', 'api_assignments_submit_action', page_url('dashboard-student'));
