<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/database.php';

try {
    $db = Database::connection();

    $leadCount = (int) $db->query('SELECT COUNT(*) AS c FROM student_leads')->fetchColumn();
    $appCount = (int) $db->query('SELECT COUNT(*) AS c FROM job_applications')->fetchColumn();

    echo "student_leads count: " . $leadCount . PHP_EOL;
    echo "job_applications count: " . $appCount . PHP_EOL;

    $lead = $db->query('SELECT id, student_name, parent_name, parent_phone, school_name, current_grade, current_level, study_time FROM student_leads ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $app = $db->query('SELECT id, full_name, email, phone, position_applied FROM job_applications ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

    echo "\nlast lead sample:\n" . json_encode($lead, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "\nlast application sample:\n" . json_encode($app, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

    exit(0);
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(2);
}
