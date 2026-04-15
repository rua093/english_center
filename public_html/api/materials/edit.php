<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/materials.php';

api_run_action('materials.edit', 'api_materials_edit_action', page_url('materials-academic'));
