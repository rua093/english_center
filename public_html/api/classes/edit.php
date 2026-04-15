<?php
declare(strict_types=1);

require_permission('academic.classes.update');
redirect('/?page=academic-class-edit&id=' . (int) ($_GET['id'] ?? 0));
