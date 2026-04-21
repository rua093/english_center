<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function roadmaps_manage_redirect_query(array $source): array
{
    $query = [];

    $courseId = input_int($source, 'course_id');
    if ($courseId > 0) {
        $query['course_id'] = $courseId;
    }

    $roadmapPage = input_int($source, 'roadmap_page');
    if ($roadmapPage > 0) {
        $query['roadmap_page'] = $roadmapPage;
    }

    $roadmapPerPage = input_int($source, 'roadmap_per_page');
    if ($roadmapPerPage > 0) {
        $query['roadmap_per_page'] = $roadmapPerPage;
    }

    return $query;
}

function api_roadmaps_save_action(): void
{
    api_require_post(page_url('roadmaps-academic'));

    $roadmapId = input_int($_POST, 'id');
    api_guard_permission($roadmapId > 0 ? 'academic.roadmaps.update' : 'academic.roadmaps.create');

    $courseId = input_int($_POST, 'course_id');
    $order = max(1, input_int($_POST, 'order', 1));
    $topicTitle = input_string($_POST, 'topic_title');
    $outlineContent = input_string($_POST, 'outline_content');

    $redirectQuery = roadmaps_manage_redirect_query($_POST);
    if (!isset($redirectQuery['course_id']) && $courseId > 0) {
        $redirectQuery['course_id'] = $courseId;
    }

    if ($courseId <= 0 || $topicTitle === '') {
        set_flash('error', 'Vui lòng chọn khóa học và nhập chủ đề roadmap.');

        if ($roadmapId > 0) {
            $redirectQuery['edit'] = $roadmapId;
        }

        redirect(page_url('roadmaps-academic', $redirectQuery));
    }

    try {
        (new AcademicModel())->saveRoadmap([
            'id' => $roadmapId,
            'course_id' => $courseId,
            'order' => $order,
            'topic_title' => $topicTitle,
            'outline_content' => $outlineContent,
        ]);
    } catch (InvalidArgumentException $exception) {
        set_flash('error', $exception->getMessage());

        if ($roadmapId > 0) {
            $redirectQuery['edit'] = $roadmapId;
        }

        redirect(page_url('roadmaps-academic', $redirectQuery));
    }

    set_flash('success', $roadmapId > 0 ? 'Đã cập nhật roadmap.' : 'Đã tạo roadmap mới.');
    redirect(page_url('roadmaps-academic', $redirectQuery));
}

function api_roadmaps_edit_action(): void
{
    api_guard_permission('academic.roadmaps.update');

    $query = roadmaps_manage_redirect_query($_GET);
    $query['edit'] = (int) ($_GET['id'] ?? 0);

    redirect(page_url('roadmaps-academic', $query));
}

function api_roadmaps_delete_action(): void
{
    api_guard_permission('academic.roadmaps.delete');
    api_require_post(page_url('roadmaps-academic'));

    $roadmapId = (int) ($_GET['id'] ?? 0);
    if ($roadmapId <= 0) {
        set_flash('error', 'Roadmap không hợp lệ.');
        redirect(page_url('roadmaps-academic', roadmaps_manage_redirect_query($_GET)));
    }

    try {
        (new AcademicModel())->deleteRoadmap($roadmapId);
        set_flash('success', 'Đã xóa roadmap.');
    } catch (Throwable) {
        set_flash('error', 'Không thể xóa roadmap. Chủ đề này có thể đã được gắn với buổi học.');
    }

    redirect(page_url('roadmaps-academic', roadmaps_manage_redirect_query($_GET)));
}
