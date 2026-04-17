<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function courses_manage_redirect_query(array $source): array
{
    $query = [];

    $coursePage = input_int($source, 'course_page');
    if ($coursePage > 0) {
        $query['course_page'] = $coursePage;
    }

    $coursePerPage = input_int($source, 'course_per_page');
    if ($coursePerPage > 0) {
        $query['course_per_page'] = $coursePerPage;
    }

    return $query;
}

function api_courses_save_action(): void
{
    api_require_post(page_url('courses-academic'));

    $courseId = input_int($_POST, 'id');
    api_guard_permission($courseId > 0 ? 'academic.classes.update' : 'academic.classes.create');

    $courseName = input_string($_POST, 'course_name');
    $basePrice = max(0, input_float($_POST, 'base_price'));
    $totalSessions = max(0, input_int($_POST, 'total_sessions'));
    $description = input_string($_POST, 'description');

    $redirectQuery = courses_manage_redirect_query($_POST);
    $redirectPath = page_url('courses-academic', $redirectQuery);

    if ($courseName === '') {
        set_flash('error', 'Vui lòng nhập tên khóa học.');

        if ($courseId > 0) {
            $redirectQuery['edit'] = $courseId;
        }

        redirect(page_url('courses-academic', $redirectQuery));
    }

    (new AcademicModel())->saveCourse([
        'id' => $courseId,
        'course_name' => $courseName,
        'description' => $description,
        'base_price' => $basePrice,
        'total_sessions' => $totalSessions,
    ]);

    set_flash('success', $courseId > 0 ? 'Đã cập nhật khóa học.' : 'Đã tạo khóa học mới.');
    redirect($redirectPath);
}

function api_courses_edit_action(): void
{
    api_guard_permission('academic.classes.update');

    $query = courses_manage_redirect_query($_GET);
    $query['edit'] = (int) ($_GET['id'] ?? 0);

    redirect(page_url('courses-academic', $query));
}

function api_courses_delete_action(): void
{
    api_guard_permission('academic.classes.delete');
    api_require_post(page_url('courses-academic'));

    $courseId = (int) ($_GET['id'] ?? 0);
    if ($courseId <= 0) {
        set_flash('error', 'Khóa học không hợp lệ.');
        redirect(page_url('courses-academic', courses_manage_redirect_query($_GET)));
    }

    try {
        (new AcademicModel())->deleteCourse($courseId);
        set_flash('success', 'Đã xóa khóa học.');
    } catch (Throwable) {
        set_flash('error', 'Không thể xóa khóa học. Dữ liệu có thể đang được tham chiếu bởi lớp học hoặc roadmap.');
    }

    redirect(page_url('courses-academic', courses_manage_redirect_query($_GET)));
}
