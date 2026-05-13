<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/file_storage.php';
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
    api_guard_permission($courseId > 0 ? 'academic.courses.update' : 'academic.courses.create');

    $courseName = input_string($_POST, 'course_name');
    $basePrice = max(0, input_float($_POST, 'base_price'));
    $totalSessions = max(0, input_int($_POST, 'total_sessions'));
    $description = input_string($_POST, 'description');
    $thumbnailPath = input_string($_POST, 'existing_image_thumbnail');

    $redirectQuery = courses_manage_redirect_query($_POST);
    $redirectPath = page_url('courses-academic', $redirectQuery);

    if ($courseName === '') {
        set_flash('error', 'Vui lòng nhập tên khóa học.');

        if ($courseId > 0) {
            $redirectQuery['edit'] = $courseId;
        }

        redirect(page_url('courses-academic', $redirectQuery));
    }

    if (
        isset($_FILES['course_thumbnail'])
        && is_array($_FILES['course_thumbnail'])
        && (int) ($_FILES['course_thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ) {
        $storedThumbnail = store_uploaded_file($_FILES['course_thumbnail'], 'course_thumb');
        if ($storedThumbnail === null) {
            set_flash('error', 'Không thể tải lên ảnh minh họa khóa học. Vui lòng thử lại với tệp hợp lệ.');

            if ($courseId > 0) {
                $redirectQuery['edit'] = $courseId;
            }

            redirect(page_url('courses-academic', $redirectQuery));
        }

        $thumbnailPath = $storedThumbnail;
    }

    (new AcademicModel())->saveCourse([
        'id' => $courseId,
        'course_name' => $courseName,
        'description' => $description,
        'base_price' => $basePrice,
        'total_sessions' => $totalSessions,
        'image_thumbnail' => $thumbnailPath,
    ]);

    set_flash('success', $courseId > 0 ? 'Đã cập nhật khóa học.' : 'Đã tạo khóa học mới.');
    redirect($redirectPath);
}

function api_courses_edit_action(): void
{
    api_guard_permission('academic.courses.update');

    $query = courses_manage_redirect_query($_GET);
    $query['edit'] = (int) ($_GET['id'] ?? 0);

    redirect(page_url('courses-academic', $query));
}

function api_courses_delete_action(): void
{
    api_guard_permission('academic.courses.delete');
    api_require_post(page_url('courses-academic'));

    $courseId = (int) ($_GET['id'] ?? 0);
    if ($courseId <= 0) {
        set_flash('error', 'Khóa học không hợp lệ.');
        redirect(page_url('courses-academic', courses_manage_redirect_query($_GET)));
    }

    try {
        (new AcademicModel())->deleteCourse($courseId);
        set_flash('success', 'Đã chuyển khóa học vào trạng thái xóa mềm.');
    } catch (Throwable) {
        set_flash('error', 'Không thể xóa khóa học. Dữ liệu có thể đang được tham chiếu bởi lớp học hoặc lộ trình.');
    }

    redirect(page_url('courses-academic', courses_manage_redirect_query($_GET)));
}
