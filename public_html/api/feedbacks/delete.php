<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/feedbacks.php';

api_run_action('feedbacks.delete', 'api_feedbacks_delete_action', page_url('feedbacks-manage'));
