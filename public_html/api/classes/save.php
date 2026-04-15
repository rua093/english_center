<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/classes.php';

api_run_action('classes.save', 'api_classes_save_action', page_url('classes-academic'));
