<?php
declare(strict_types=1);

require_permission('academic.assignments.update');
redirect('/?page=academic-assignment-edit&id=' . (int) ($_GET['id'] ?? 0));
