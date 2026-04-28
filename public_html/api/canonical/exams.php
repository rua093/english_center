<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

/**
 * @return array{exam_name:string,exam_type:string,exam_date:string}
 */
function api_exams_read_column_meta(array $source, string $prefix = ''): array
{
    return [
        'exam_name' => trim((string) ($source[$prefix . 'exam_name'] ?? '')),
        'exam_type' => trim((string) ($source[$prefix . 'exam_type'] ?? '')),
        'exam_date' => trim((string) ($source[$prefix . 'exam_date'] ?? '')),
    ];
}

function api_exams_validate_column_meta(array $meta, string $invalidPayloadCode = 'INVALID_PAYLOAD'): void
{
    $examName = (string) ($meta['exam_name'] ?? '');
    $examType = (string) ($meta['exam_type'] ?? '');
    $examDate = (string) ($meta['exam_date'] ?? '');

    if ($examName === '' || $examType === '' || $examDate === '') {
        api_error('Vui lòng nhập đầy đủ thông tin cột điểm.', ['code' => $invalidPayloadCode], 422);
    }

    if (!in_array($examType, ['entry', 'periodic', 'final'], true)) {
        api_error('Loại bài kiểm tra không hợp lệ.', ['code' => 'INVALID_EXAM_TYPE'], 422);
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $examDate) !== 1) {
        api_error('Ngày kiểm tra không hợp lệ.', ['code' => 'INVALID_EXAM_DATE'], 422);
    }
}

function api_exams_format_score(?float $score): string
{
    if ($score === null) {
        return '';
    }

    $normalized = round($score, 2);
    if (abs($normalized) < 0.00001) {
        $normalized = 0.0;
    }

    return rtrim(rtrim(number_format($normalized, 2, '.', ''), '0'), '.');
}

function api_exams_parse_component_score(mixed $rawValue, string $label, string $errorCode): ?float
{
    $text = trim((string) $rawValue);
    if ($text === '') {
        return null;
    }

    $text = str_replace(',', '.', $text);
    if (!is_numeric($text)) {
        api_error($label . ' không hợp lệ.', ['code' => $errorCode], 422);
    }

    $score = round((float) $text, 2);
    if ($score < 0 || $score > 10) {
        api_error($label . ' phải nằm trong khoảng 0 đến 10.', ['code' => $errorCode], 422);
    }

    return $score;
}

/**
 * @return array{score_listening:?float,score_speaking:?float,score_reading:?float,score_writing:?float}
 */
function api_exams_read_component_scores(array $source): array
{
    return [
        'score_listening' => api_exams_parse_component_score($source['score_listening'] ?? '', 'Điểm Listening', 'INVALID_SCORE_LISTENING'),
        'score_speaking' => api_exams_parse_component_score($source['score_speaking'] ?? '', 'Điểm Speaking', 'INVALID_SCORE_SPEAKING'),
        'score_reading' => api_exams_parse_component_score($source['score_reading'] ?? '', 'Điểm Reading', 'INVALID_SCORE_READING'),
        'score_writing' => api_exams_parse_component_score($source['score_writing'] ?? '', 'Điểm Writing', 'INVALID_SCORE_WRITING'),
    ];
}

/**
 * @param array{score_listening:?float,score_speaking:?float,score_reading:?float,score_writing:?float} $scores
 */
function api_exams_compute_total_score(array $scores): ?float
{
    $filledCount = 0;
    foreach ($scores as $score) {
        if ($score !== null) {
            $filledCount++;
        }
    }

    if ($filledCount === 0) {
        return null;
    }

    if ($filledCount < 4) {
        api_error(
            'Vui lòng nhập đủ 4 điểm thành phần hoặc để trống toàn bộ.',
            ['code' => 'INCOMPLETE_COMPONENT_SCORES'],
            422
        );
    }

    $total = ($scores['score_listening'] ?? 0.0)
        + ($scores['score_speaking'] ?? 0.0)
        + ($scores['score_reading'] ?? 0.0)
        + ($scores['score_writing'] ?? 0.0);

    return round($total / 4, 2);
}

/**
 * @param array{score_listening:?float,score_speaking:?float,score_reading:?float,score_writing:?float} $scores
 * @return array{exam_id:int,score_listening:string,score_speaking:string,score_reading:string,score_writing:string,result:string,teacher_comment:string}
 */
function api_exams_build_score_response_payload(int $examId, array $scores, string $result, string $teacherComment): array
{
    return [
        'exam_id' => $examId,
        'score_listening' => api_exams_format_score($scores['score_listening'] ?? null),
        'score_speaking' => api_exams_format_score($scores['score_speaking'] ?? null),
        'score_reading' => api_exams_format_score($scores['score_reading'] ?? null),
        'score_writing' => api_exams_format_score($scores['score_writing'] ?? null),
        'result' => $result,
        'teacher_comment' => trim($teacherComment),
    ];
}

function api_exams_class_grid_action(): void
{
    if (!has_any_permission(['academic.classes.view', 'academic.schedules.view'])) {
        api_error('Không có quyền xem lớp học.', ['code' => 'FORBIDDEN'], 403);
    }

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
    }

    $classId = (int) ($_GET['class_id'] ?? 0);
    if ($classId <= 0) {
        api_error('Dữ liệu lớp học không hợp lệ.', ['code' => 'INVALID_CLASS_ID'], 422);
    }

    $academicModel = new AcademicModel();
    $class = $academicModel->findClass($classId);
    if (!is_array($class)) {
        api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
    }

	teacher_assert_class_scope($academicModel, $classId, page_url('classrooms-academic', ['class_id' => $classId]));

    $studentRows = $academicModel->listStudentsForClass($classId);

    $attendanceSummaryRows = $academicModel->summarizeAttendanceRateByClass($classId);
    $attendanceByStudentId = [];
    foreach ($attendanceSummaryRows as $summaryRow) {
        $summaryStudentId = (int) ($summaryRow['student_id'] ?? 0);
        if ($summaryStudentId <= 0) {
            continue;
        }

        $attendanceByStudentId[$summaryStudentId] = [
            'total_sessions' => max(0, (int) ($summaryRow['total_sessions'] ?? 0)),
            'attended_sessions' => max(0, (int) ($summaryRow['attended_sessions'] ?? 0)),
            'present_sessions' => max(0, (int) ($summaryRow['present_sessions'] ?? 0)),
            'late_sessions' => max(0, (int) ($summaryRow['late_sessions'] ?? 0)),
            'absent_sessions' => max(0, (int) ($summaryRow['absent_sessions'] ?? 0)),
        ];
    }

    $submissionSummaryRows = $academicModel->summarizeOnTimeSubmissionRateByClass($classId);
    $submissionByStudentId = [];
    foreach ($submissionSummaryRows as $summaryRow) {
        $summaryStudentId = (int) ($summaryRow['student_id'] ?? 0);
        if ($summaryStudentId <= 0) {
            continue;
        }

        $submissionByStudentId[$summaryStudentId] = [
            'total_assignments' => max(0, (int) ($summaryRow['total_assignments'] ?? 0)),
            'submitted_assignments' => max(0, (int) ($summaryRow['submitted_assignments'] ?? 0)),
            'on_time_assignments' => max(0, (int) ($summaryRow['on_time_assignments'] ?? 0)),
            'late_assignments' => max(0, (int) ($summaryRow['late_assignments'] ?? 0)),
        ];
    }

    $students = [];
    foreach ($studentRows as $row) {
        $studentId = (int) ($row['student_id'] ?? 0);
        if ($studentId <= 0) {
            continue;
        }

        $attendance = $attendanceByStudentId[$studentId] ?? [];
        $attendanceTotalSessions = max(0, (int) ($attendance['total_sessions'] ?? 0));
        $attendanceAttendedSessions = max(0, (int) ($attendance['attended_sessions'] ?? 0));
        $attendanceRate = $attendanceTotalSessions > 0
            ? round(($attendanceAttendedSessions / $attendanceTotalSessions) * 100, 1)
            : null;

        $submission = $submissionByStudentId[$studentId] ?? [];
        $submissionTotalAssignments = max(0, (int) ($submission['total_assignments'] ?? 0));
        $submissionSubmittedAssignments = max(0, (int) ($submission['submitted_assignments'] ?? 0));
        $submissionOnTimeAssignments = max(0, (int) ($submission['on_time_assignments'] ?? 0));
        $submissionRate = $submissionTotalAssignments > 0
            ? round(($submissionOnTimeAssignments / $submissionTotalAssignments) * 100, 1)
            : null;

        $students[] = [
            'id' => $studentId,
            'name' => (string) ($row['student_name'] ?? ('Học viên #' . $studentId)),
            'learning_status' => ((string) ($row['learning_status'] ?? 'official')) === 'trial' ? 'trial' : 'official',
            'metrics' => [
                'attendance' => [
                    'total_sessions' => $attendanceTotalSessions,
                    'attended_sessions' => $attendanceAttendedSessions,
                    'present_sessions' => max(0, (int) ($attendance['present_sessions'] ?? 0)),
                    'late_sessions' => max(0, (int) ($attendance['late_sessions'] ?? 0)),
                    'absent_sessions' => max(0, (int) ($attendance['absent_sessions'] ?? 0)),
                    'rate' => $attendanceRate,
                ],
                'submission' => [
                    'total_assignments' => $submissionTotalAssignments,
                    'submitted_assignments' => $submissionSubmittedAssignments,
                    'on_time_assignments' => $submissionOnTimeAssignments,
                    'late_assignments' => max(0, (int) ($submission['late_assignments'] ?? 0)),
                    'rate' => $submissionRate,
                ],
            ],
        ];
    }

    $columnRows = $academicModel->listExamColumnsByClass($classId);
    $exams = [];
    foreach ($columnRows as $col) {
        $examName = trim((string) ($col['exam_name'] ?? ''));
        $examType = (string) ($col['exam_type'] ?? '');
        $examDate = (string) ($col['exam_date'] ?? '');
        if ($examName === '' || $examType === '' || $examDate === '') {
            continue;
        }

        $key = hash('sha256', $examName . '|' . $examType . '|' . $examDate);
        $exams[] = [
            'key' => $key,
            'exam_name' => $examName,
            'exam_type' => $examType,
            'exam_date' => $examDate,
        ];
    }

    $rows = $academicModel->listExamRowsByClass($classId);
    $cells = [];
    foreach ($rows as $row) {
        $studentId = (int) ($row['student_id'] ?? 0);
        if ($studentId <= 0) {
            continue;
        }

        $examName = trim((string) ($row['exam_name'] ?? ''));
        $examType = (string) ($row['exam_type'] ?? '');
        $examDate = (string) ($row['exam_date'] ?? '');
        if ($examName === '' || $examType === '' || $examDate === '') {
            continue;
        }

        $key = hash('sha256', $examName . '|' . $examType . '|' . $examDate);
        if (!isset($cells[$studentId])) {
            $cells[$studentId] = [];
        }

        $cells[$studentId][$key] = [
            'exam_id' => (int) ($row['id'] ?? 0),
            'score_listening' => (string) ($row['score_listening'] ?? ''),
            'score_speaking' => (string) ($row['score_speaking'] ?? ''),
            'score_reading' => (string) ($row['score_reading'] ?? ''),
            'score_writing' => (string) ($row['score_writing'] ?? ''),
            'result' => (string) ($row['result'] ?? ''),
            'teacher_comment' => (string) ($row['teacher_comment'] ?? ''),
        ];
    }

    api_success('OK', [
        'class' => [
            'id' => (int) ($class['id'] ?? 0),
            'class_name' => (string) ($class['class_name'] ?? ''),
        ],
        'students' => $students,
        'exams' => $exams,
        'cells' => $cells,
    ]);
}

function api_exams_create_column_action(): void
{
    api_require_post(page_url('classrooms-academic'));
    api_guard_permission('academic.submissions.grade');

    $classId = (int) ($_POST['class_id'] ?? 0);
    $columnMeta = api_exams_read_column_meta($_POST);
    api_exams_validate_column_meta($columnMeta);

    $examName = $columnMeta['exam_name'];
    $examType = $columnMeta['exam_type'];
    $examDate = $columnMeta['exam_date'];

    if ($classId <= 0) {
        api_error('Dữ liệu lớp học không hợp lệ.', ['code' => 'INVALID_CLASS_ID'], 422);
    }

    $academicModel = new AcademicModel();
    $class = $academicModel->findClass($classId);
    if (!is_array($class)) {
        api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
    }

	teacher_assert_class_scope($academicModel, $classId, page_url('classrooms-academic', ['class_id' => $classId]));

    if ($academicModel->countExamRowsForColumn($classId, $examName, $examType, $examDate) > 0) {
        api_error('Cột điểm đã tồn tại (trùng tên, loại, ngày).', ['code' => 'EXAM_COLUMN_EXISTS'], 409);
    }

    $studentRows = $academicModel->listStudentsForClass($classId);
    $studentIds = [];
    foreach ($studentRows as $row) {
        $studentId = (int) ($row['student_id'] ?? 0);
        if ($studentId > 0) {
            $studentIds[] = $studentId;
        }
    }

    if (empty($studentIds)) {
        api_error('Lớp học chưa có học viên để tạo cột điểm.', ['code' => 'NO_STUDENTS'], 422);
    }

    $academicModel->createExamColumnForStudents($classId, $studentIds, $examName, $examType, $examDate);

    api_success('Đã tạo cột điểm.', [
        'class_id' => $classId,
    ]);
}

function api_exams_update_column_action(): void
{
    api_require_post(page_url('classrooms-academic'));
    api_guard_permission('academic.submissions.grade');

    $classId = (int) ($_POST['class_id'] ?? 0);
    if ($classId <= 0) {
        api_error('Dữ liệu lớp học không hợp lệ.', ['code' => 'INVALID_CLASS_ID'], 422);
    }

    $oldMeta = api_exams_read_column_meta($_POST, 'old_');
    $newMeta = api_exams_read_column_meta($_POST);
    api_exams_validate_column_meta($oldMeta, 'INVALID_OLD_PAYLOAD');
    api_exams_validate_column_meta($newMeta, 'INVALID_NEW_PAYLOAD');

    $oldExamName = $oldMeta['exam_name'];
    $oldExamType = $oldMeta['exam_type'];
    $oldExamDate = $oldMeta['exam_date'];

    $newExamName = $newMeta['exam_name'];
    $newExamType = $newMeta['exam_type'];
    $newExamDate = $newMeta['exam_date'];

    $academicModel = new AcademicModel();
    $class = $academicModel->findClass($classId);
    if (!is_array($class)) {
        api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
    }

	teacher_assert_class_scope($academicModel, $classId, page_url('classrooms-academic', ['class_id' => $classId]));

    if ($academicModel->countExamRowsForColumn($classId, $oldExamName, $oldExamType, $oldExamDate) <= 0) {
        api_error('Không tìm thấy cột điểm cần cập nhật.', ['code' => 'EXAM_COLUMN_NOT_FOUND'], 404);
    }

    $sameMeta = $oldExamName === $newExamName
        && $oldExamType === $newExamType
        && $oldExamDate === $newExamDate;

    if (!$sameMeta && $academicModel->countExamRowsForColumn($classId, $newExamName, $newExamType, $newExamDate) > 0) {
        api_error('Cột điểm mới bị trùng với cột đã có.', ['code' => 'EXAM_COLUMN_EXISTS'], 409);
    }

    try {
        $updatedRows = $sameMeta
            ? $academicModel->countExamRowsForColumn($classId, $oldExamName, $oldExamType, $oldExamDate)
            : $academicModel->updateExamColumnMeta(
                $classId,
                $oldExamName,
                $oldExamType,
                $oldExamDate,
                $newExamName,
                $newExamType,
                $newExamDate
            );
    } catch (Throwable $exception) {
        app_log('error', 'Failed to update exam column', [
            'class_id' => $classId,
            'old_exam_name' => $oldExamName,
            'old_exam_type' => $oldExamType,
            'old_exam_date' => $oldExamDate,
            'new_exam_name' => $newExamName,
            'new_exam_type' => $newExamType,
            'new_exam_date' => $newExamDate,
            'error' => $exception->getMessage(),
        ]);

        api_error('Không cập nhật được cột điểm.', ['code' => 'EXAM_COLUMN_UPDATE_FAILED'], 500);
    }

    api_success('Đã cập nhật thông tin cột điểm.', [
        'class_id' => $classId,
        'updated_rows' => $updatedRows,
        'old' => $oldMeta,
        'new' => $newMeta,
    ]);
}

function api_exams_delete_column_action(): void
{
    api_require_post(page_url('classrooms-academic'));
    api_guard_permission('academic.submissions.grade');

    $classId = (int) ($_POST['class_id'] ?? 0);
    if ($classId <= 0) {
        api_error('Dữ liệu lớp học không hợp lệ.', ['code' => 'INVALID_CLASS_ID'], 422);
    }

    $columnMeta = api_exams_read_column_meta($_POST);
    api_exams_validate_column_meta($columnMeta);

    $examName = $columnMeta['exam_name'];
    $examType = $columnMeta['exam_type'];
    $examDate = $columnMeta['exam_date'];

    $academicModel = new AcademicModel();
    $class = $academicModel->findClass($classId);
    if (!is_array($class)) {
        api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
    }

	teacher_assert_class_scope($academicModel, $classId, page_url('classrooms-academic', ['class_id' => $classId]));

    if ($academicModel->countExamRowsForColumn($classId, $examName, $examType, $examDate) <= 0) {
        api_error('Không tìm thấy cột điểm cần xóa.', ['code' => 'EXAM_COLUMN_NOT_FOUND'], 404);
    }

    try {
        $deletedRows = $academicModel->deleteExamColumn($classId, $examName, $examType, $examDate);
    } catch (Throwable $exception) {
        app_log('error', 'Failed to delete exam column', [
            'class_id' => $classId,
            'exam_name' => $examName,
            'exam_type' => $examType,
            'exam_date' => $examDate,
            'error' => $exception->getMessage(),
        ]);

        api_error('Không xóa được cột điểm.', ['code' => 'EXAM_COLUMN_DELETE_FAILED'], 500);
    }

    api_success('Đã xóa cột điểm.', [
        'class_id' => $classId,
        'deleted_rows' => $deletedRows,
        'exam_name' => $examName,
        'exam_type' => $examType,
        'exam_date' => $examDate,
    ]);
}

function api_exams_save_score_action(): void
{
    api_require_post(page_url('classrooms-academic'));
    api_guard_permission('academic.submissions.grade');

    $classId = (int) ($_POST['class_id'] ?? 0);
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $examId = (int) ($_POST['exam_id'] ?? 0);

    $examName = trim((string) ($_POST['exam_name'] ?? ''));
    $examType = trim((string) ($_POST['exam_type'] ?? ''));
    $examDate = trim((string) ($_POST['exam_date'] ?? ''));
    $resultFallback = trim((string) ($_POST['result'] ?? ''));

    $componentScores = api_exams_read_component_scores($_POST);
    $totalScore = api_exams_compute_total_score($componentScores);
    $result = api_exams_format_score($totalScore);
    if ($totalScore === null && $resultFallback !== '') {
        $result = $resultFallback;
    }
    $teacherComment = isset($_POST['teacher_comment']) ? (string) $_POST['teacher_comment'] : '';

    if ($classId <= 0 || $studentId <= 0 || $examName === '' || $examType === '' || $examDate === '') {
        api_error('Dữ liệu không hợp lệ.', ['code' => 'INVALID_PAYLOAD'], 422);
    }

    if (!in_array($examType, ['entry', 'periodic', 'final'], true)) {
        api_error('Loại bài kiểm tra không hợp lệ.', ['code' => 'INVALID_EXAM_TYPE'], 422);
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $examDate) !== 1) {
        api_error('Ngày kiểm tra không hợp lệ.', ['code' => 'INVALID_EXAM_DATE'], 422);
    }

    $academicModel = new AcademicModel();
    $class = $academicModel->findClass($classId);
    if (!is_array($class)) {
        api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
    }

	teacher_assert_class_scope($academicModel, $classId, page_url('classrooms-academic', ['class_id' => $classId]));

    if (!$academicModel->isStudentEnrolledInClass($studentId, $classId)) {
        api_error('Học viên không thuộc lớp học.', ['code' => 'STUDENT_NOT_IN_CLASS'], 403);
    }

    if ($examId > 0) {
        $existing = $academicModel->findExamRowById($examId);
        if (!is_array($existing)) {
            api_error('Không tìm thấy bản ghi điểm.', ['code' => 'EXAM_ROW_NOT_FOUND'], 404);
        }

        if ((int) ($existing['class_id'] ?? 0) !== $classId || (int) ($existing['student_id'] ?? 0) !== $studentId) {
            api_error('Bản ghi điểm không hợp lệ.', ['code' => 'EXAM_ROW_MISMATCH'], 403);
        }

        $academicModel->updateExamResult(
            $examId,
            $result,
            $teacherComment,
            $componentScores['score_listening'],
            $componentScores['score_speaking'],
            $componentScores['score_reading'],
            $componentScores['score_writing']
        );
        api_success('Đã lưu điểm.', api_exams_build_score_response_payload($examId, $componentScores, $result, $teacherComment));
    }

    $existing = $academicModel->findExamRowByMeta($classId, $studentId, $examName, $examType, $examDate);
    if (is_array($existing)) {
        $existingId = (int) ($existing['id'] ?? 0);
        if ($existingId > 0) {
            $academicModel->updateExamResult(
                $existingId,
                $result,
                $teacherComment,
                $componentScores['score_listening'],
                $componentScores['score_speaking'],
                $componentScores['score_reading'],
                $componentScores['score_writing']
            );
            api_success(
                'Đã lưu điểm.',
                api_exams_build_score_response_payload($existingId, $componentScores, $result, $teacherComment)
            );
        }
    }

    $newId = $academicModel->createExamRow($classId, $studentId, $examName, $examType, $examDate);
    if ($newId > 0) {
        $academicModel->updateExamResult(
            $newId,
            $result,
            $teacherComment,
            $componentScores['score_listening'],
            $componentScores['score_speaking'],
            $componentScores['score_reading'],
            $componentScores['score_writing']
        );
    }

    api_success('Đã lưu điểm.', api_exams_build_score_response_payload($newId, $componentScores, $result, $teacherComment));
}
