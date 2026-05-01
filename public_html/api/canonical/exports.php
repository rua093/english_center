<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_exports_guard_permission(): void
{
    if (!has_permission('academic.exports.view')) {
        api_error('Không có quyền sử dụng chức năng xuất báo cáo.', ['code' => 'FORBIDDEN'], 403);
    }
}

function api_exports_students_action(): void
{
    api_exports_guard_permission();

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
    }

    $classId = (int) ($_GET['class_id'] ?? 0);
    if ($classId <= 0) {
        api_error('Vui lòng chọn lớp học hợp lệ.', ['code' => 'INVALID_CLASS_ID'], 422);
    }

    $academicModel = new AcademicModel();
    $class = $academicModel->findClass($classId);
    if (!is_array($class)) {
        api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
    }

    teacher_assert_class_scope($academicModel, $classId, page_url('exports-academic', ['class_id' => $classId]));

    api_success('OK', [
        'class' => $class,
        'students' => $academicModel->listStudentsForClass($classId),
    ]);
}

function api_exports_student_report_action(): void
{
    api_exports_guard_permission();
    api_require_post(page_url('exports-academic'));

    $classId = (int) ($_POST['class_id'] ?? 0);
    if ($classId <= 0) {
        api_error('Vui lòng chọn lớp học hợp lệ.', ['code' => 'INVALID_CLASS_ID'], 422);
    }

    $rawStudentIds = $_POST['student_ids'] ?? [];
    if (!is_array($rawStudentIds)) {
        $rawStudentIds = [];
    }

    $selectedStudentIds = [];
    foreach ($rawStudentIds as $studentId) {
        $normalizedId = (int) $studentId;
        if ($normalizedId > 0) {
            $selectedStudentIds[$normalizedId] = true;
        }
    }

    if ($selectedStudentIds === []) {
        api_error('Vui lòng chọn ít nhất một học viên.', ['code' => 'EMPTY_STUDENT_SELECTION'], 422);
    }

    $academicModel = new AcademicModel();
    $class = $academicModel->findClass($classId);
    if (!is_array($class)) {
        api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
    }

    teacher_assert_class_scope($academicModel, $classId, page_url('exports-academic', ['class_id' => $classId]));

    $report = $academicModel->buildStudentPerformanceExport($classId, array_keys($selectedStudentIds));
    if ($report === []) {
        api_error('Không lấy được dữ liệu báo cáo cho nhóm học viên đã chọn.', ['code' => 'EMPTY_REPORT'], 404);
    }

    api_success('OK', $report);
}
