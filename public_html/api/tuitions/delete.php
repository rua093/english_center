<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/tuitions.php';

api_run_action('tuitions.delete', 'api_tuitions_delete_action', page_url('tuition-finance'));
