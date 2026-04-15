<?php
declare(strict_types=1);

require_permission('academic.submissions.grade');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-submissions');
}

$submissionId = (int) ($_POST['submission_id'] ?? 0);
$score = ($_POST['score'] ?? '') === '' ? null : (float) $_POST['score'];
$comment = trim((string) ($_POST['teacher_comment'] ?? ''));
if ($submissionId > 0) {
(new AcademicModel())->gradeSubmission($submissionId, $score, $comment);
set_flash('success', 'Đã cập nhật điểm bài nộp.');
}

redirect('/?page=academic-submissions');
