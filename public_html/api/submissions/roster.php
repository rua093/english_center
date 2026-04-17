<?php
declare(strict_types=1);

require_permission('academic.submissions.view');

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
    if (api_expects_json()) {
        api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
    }
    redirect(page_url('submissions-academic'));
}

$classId = max(0, (int) ($_GET['class_id'] ?? 0));
$assignmentId = max(0, (int) ($_GET['assignment_id'] ?? 0));

if ($classId <= 0 || $assignmentId <= 0) {
    api_error('Thiếu tham số lớp học hoặc bài tập.', ['code' => 'INVALID_FILTER'], 422);
}

$academicModel = new AcademicModel();
$rows = $academicModel->listSubmissionRosterByClassAndAssignment($classId, $assignmentId);

$summary = [
    'total' => 0,
    'submitted' => 0,
    'missing' => 0,
    'graded' => 0,
    'pending' => 0,
];

foreach ($rows as $row) {
    $hasSubmission = (int) ($row['submission_id'] ?? 0) > 0;
    $hasScore = trim((string) ($row['score'] ?? '')) !== '';

    $summary['total']++;

    if (!$hasSubmission) {
        $summary['missing']++;
        continue;
    }

    $summary['submitted']++;

    if ($hasScore) {
        $summary['graded']++;
    } else {
        $summary['pending']++;
    }
}

api_success('OK', [
    'rows' => $rows,
    'summary' => $summary,
]);
