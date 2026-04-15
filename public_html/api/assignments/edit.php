<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/assignments.php';

api_run_action('assignments.edit', 'api_assignments_edit_action', page_url('assignments-academic'));
