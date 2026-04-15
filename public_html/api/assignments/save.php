<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/assignments.php';

api_run_action('assignments.save', 'api_assignments_save_action', page_url('assignments-academic'));
