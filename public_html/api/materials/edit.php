<?php
declare(strict_types=1);

require_permission('materials.update');
redirect('/?page=academic-material-edit&id=' . (int) ($_GET['id'] ?? 0));
