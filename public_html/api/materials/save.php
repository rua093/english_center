<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/materials.php';

api_run_action('materials.save', 'api_materials_save_action', page_url('materials-academic'));
