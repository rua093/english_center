<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/tuitions.php';

api_run_action('tuitions.request-adjust', 'api_tuitions_request_adjust_action', page_url('tuition-finance'));
