<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/portfolios.php';

api_run_action('portfolios.delete', 'api_portfolios_delete_action', page_url('portfolios-academic'));
