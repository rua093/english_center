<?php
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/models/AcademicModel.php';

$m = new AcademicModel();
$assignments = $m->listAssignments();
echo "Total assignments: " . count($assignments) . PHP_EOL;
foreach ($assignments as $row) {
    echo "  ID=" . ($row['id'] ?? '?') 
       . " schedule_id=" . ($row['schedule_id'] ?? '?') 
       . " title=" . ($row['title'] ?? '?') 
       . " class=" . ($row['class_name'] ?? '?') 
       . PHP_EOL;
}

echo PHP_EOL;

// List all classes to find actual class IDs
$lookups = $m->classroomLookups();
$classes = $lookups['classes'] ?? [];
echo "Classes:" . PHP_EOL;
foreach ($classes as $c) {
    $classId = (int)($c['id'] ?? 0);
    echo "  Class ID=" . $classId . " name=" . ($c['class_name'] ?? '?') . PHP_EOL;
    
    $schedules = $m->listSchedulesByClass($classId);
    echo "    Schedules: " . count($schedules) . PHP_EOL;
    foreach (array_slice($schedules, 0, 5) as $s) {
        echo "      Schedule ID=" . ($s['id'] ?? '?') . " date=" . ($s['study_date'] ?? '?') . PHP_EOL;
    }
}

echo PHP_EOL . "assignmentsBySchedule structure:" . PHP_EOL;
$scheduleById = [];
$classroomAssignmentsBySchedule = [];
// Use first class
if (!empty($classes)) {
    $firstClassId = (int)($classes[0]['id'] ?? 0);
    echo "Using class ID: $firstClassId" . PHP_EOL;
    $schedules = $m->listSchedulesByClass($firstClassId);
    foreach ($schedules as $s) {
        $sid = (int)($s['id'] ?? 0);
        if ($sid > 0) $scheduleById[$sid] = $s;
    }
    
    foreach ($assignments as $row) {
        $sid = (int)($row['schedule_id'] ?? 0);
        $aid = (int)($row['id'] ?? 0);
        if ($sid <= 0 || $aid <= 0) {
            echo "  SKIP: schedule_id=$sid, id=$aid" . PHP_EOL;
            continue;
        }
        if (!isset($scheduleById[$sid])) {
            echo "  SKIP: schedule $sid not in class $firstClassId (belongs to class=" . ($row['class_name'] ?? '?') . ")" . PHP_EOL;
            continue;
        }
        if (!isset($classroomAssignmentsBySchedule[$sid])) {
            $classroomAssignmentsBySchedule[$sid] = [];
        }
        $classroomAssignmentsBySchedule[$sid][] = [
            'id' => $aid,
            'title' => (string)($row['title'] ?? ''),
        ];
        echo "  ADDED: assignment $aid -> schedule $sid" . PHP_EOL;
    }
}

$json = json_encode($classroomAssignmentsBySchedule, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo PHP_EOL . "JSON output: " . ($json ?: '(empty)') . PHP_EOL;
