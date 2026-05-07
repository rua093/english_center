<?php
declare(strict_types=1);

require_once __DIR__ . '/../canonical/activities.php';

api_run_action('activities.add-student', 'api_activities_add_student_action', page_url('activities-manage'));
