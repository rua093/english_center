<?php
declare(strict_types=1);

require_permission('academic.schedules.update');
redirect('/?page=academic-schedule-edit&id=' . (int) ($_GET['id'] ?? 0));
