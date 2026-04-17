<?php
declare(strict_types=1);

require_permission('academic.submissions.grade');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect(page_url('submissions-academic'));
}

function submissions_grade_resolve_redirect_page(array $source): string
{
	$requestedPage = strtolower(trim((string) ($source['redirect_page'] ?? '')));
	if ($requestedPage === 'classrooms-academic') {
		return $requestedPage;
	}

	return 'submissions-academic';
}

function submissions_grade_resolve_redirect_query(array $source, string $redirectPage): array
{
	if ($redirectPage === 'classrooms-academic') {
		$query = [];

		$courseId = max(0, (int) ($source['course_id'] ?? 0));
		if ($courseId > 0) {
			$query['course_id'] = $courseId;
		}

		$classId = max(0, (int) ($source['class_id'] ?? 0));
		if ($classId > 0) {
			$query['class_id'] = $classId;
		}

		$lessonId = max(0, (int) ($source['lesson_id'] ?? 0));
		if ($lessonId > 0) {
			$query['lesson_id'] = $lessonId;
		}

		$scheduleId = max(0, (int) ($source['schedule_id'] ?? 0));
		$focusScheduleId = max(0, (int) ($source['focus_schedule_id'] ?? 0));
		if ($focusScheduleId <= 0) {
			$focusScheduleId = $scheduleId;
		}

		if ($focusScheduleId > 0) {
			$query['focus_schedule_id'] = $focusScheduleId;
		}

		$weekStart = trim((string) ($source['week_start'] ?? ''));
		if ($weekStart !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart) === 1) {
			$query['week_start'] = $weekStart;
		}

		$weekRef = trim((string) ($source['week_ref'] ?? ''));
		if ($weekRef !== '' && preg_match('/^\d{4}-W\d{2}$/', $weekRef) === 1) {
			$query['week_ref'] = $weekRef;
		}

		return $query;
	}

	$classId = max(0, (int) ($source['class_id'] ?? 0));
	$lessonId = max(0, (int) ($source['lesson_id'] ?? 0));
	$assignmentId = max(0, (int) ($source['assignment_id'] ?? 0));
	$submissionPage = max(1, (int) ($source['submission_page'] ?? 1));
	$submissionPerPage = max(1, (int) ($source['submission_per_page'] ?? 10));
	$gradeStatus = trim((string) ($source['grade_status'] ?? 'pending'));
	if (!in_array($gradeStatus, ['pending', 'graded', 'all', 'missing'], true)) {
		$gradeStatus = 'pending';
	}

	return [
		'class_id' => $classId,
		'lesson_id' => $lessonId,
		'assignment_id' => $assignmentId,
		'grade_status' => $gradeStatus,
		'submission_page' => $submissionPage,
		'submission_per_page' => $submissionPerPage,
	];
}

$submissionId = (int) ($_POST['submission_id'] ?? 0);

$scoreInput = $_POST['score'] ?? '';
$score = null;
if (!is_array($scoreInput)) {
	$scoreValue = trim((string) $scoreInput);
	$score = $scoreValue === '' ? null : (float) $scoreValue;
}

$commentInput = $_POST['teacher_comment'] ?? '';
$comment = is_array($commentInput) ? '' : trim((string) $commentInput);

$selectedSubmissionIdsRaw = $_POST['selected_submission_ids'] ?? [];
$selectedSubmissionIds = is_array($selectedSubmissionIdsRaw) ? $selectedSubmissionIdsRaw : [];

$scoreMapRaw = $_POST['score'] ?? [];
$scoreMap = is_array($scoreMapRaw) ? $scoreMapRaw : [];

$commentMapRaw = $_POST['teacher_comment'] ?? [];
$commentMap = is_array($commentMapRaw) ? $commentMapRaw : [];

$classId = max(0, (int) ($_POST['class_id'] ?? 0));
$lessonId = max(0, (int) ($_POST['lesson_id'] ?? 0));
$assignmentId = max(0, (int) ($_POST['assignment_id'] ?? 0));
$submissionPage = max(1, (int) ($_POST['submission_page'] ?? 1));
$submissionPerPage = max(1, (int) ($_POST['submission_per_page'] ?? 10));
$gradeStatus = trim((string) ($_POST['grade_status'] ?? 'pending'));
if (!in_array($gradeStatus, ['pending', 'graded', 'all', 'missing'], true)) {
	$gradeStatus = 'pending';
}

$academicModel = new AcademicModel();
$updatedCount = 0;

if (!empty($selectedSubmissionIds)) {
	foreach ($selectedSubmissionIds as $rawId) {
		$currentSubmissionId = (int) $rawId;
		if ($currentSubmissionId <= 0) {
			continue;
		}

		$rawScoreValue = $scoreMap[$currentSubmissionId] ?? '';
		$scoreValue = trim((string) $rawScoreValue);
		$currentScore = $scoreValue === '' ? null : (float) $scoreValue;
		$currentComment = trim((string) ($commentMap[$currentSubmissionId] ?? ''));

		$academicModel->gradeSubmission($currentSubmissionId, $currentScore, $currentComment);
		$updatedCount++;
	}
} elseif ($submissionId > 0) {
	$academicModel->gradeSubmission($submissionId, $score, $comment);
	$updatedCount = 1;
}

if ($updatedCount > 0) {
	if ($updatedCount === 1) {
		set_flash('success', 'Đã cập nhật điểm bài nộp.');
	} else {
		set_flash('success', 'Đã cập nhật ' . $updatedCount . ' bài nộp.');
	}
} elseif (!empty($selectedSubmissionIds)) {
	set_flash('error', 'Không có bài nộp hợp lệ để cập nhật.');
}

$redirectPage = submissions_grade_resolve_redirect_page($_POST);
redirect(page_url($redirectPage, submissions_grade_resolve_redirect_query($_POST, $redirectPage)));
