<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/materials.php';

api_run_action('materials.delete', 'api_materials_delete_action', page_url('materials-academic'));
