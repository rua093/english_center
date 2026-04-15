<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/feedbacks.php';

api_run_action('feedbacks.save', 'api_feedbacks_save_action', page_url('feedbacks-manage'));
