<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/file_storage.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_activities_save_action(): void
{
	api_require_post(page_url('activities-manage'));

	$activityId = input_int($_POST, 'id');
	api_guard_permission($activityId > 0 ? 'activity.update' : 'activity.create');

	$payload = $_POST;
	$payload['id'] = $activityId;

	$thumbnailPath = trim((string) ($_POST['existing_image_thumbnail'] ?? ''));
	if (isset($_FILES['activity_thumbnail']) && is_array($_FILES['activity_thumbnail']) && (int) ($_FILES['activity_thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
		$storedThumbnail = store_uploaded_file($_FILES['activity_thumbnail'], 'activity_thumb');
		if ($storedThumbnail === null) {
			set_flash('error', 'Không thể tải lên ảnh thumbnail. Vui lòng thử lại với tệp hợp lệ.');
			$query = $activityId > 0 ? ['edit' => $activityId] : [];
			redirect(page_url('activities-manage', $query));
		}

		$thumbnailPath = $storedThumbnail;
	}

	$payload['image_thumbnail'] = $thumbnailPath;

	(new AcademicModel())->saveActivity($payload);
	set_flash('success', 'Đã lưu hoạt động ngoại khóa thành công.');

	redirect(page_url('activities-manage'));
}

function api_activities_delete_action(): void
{
	api_guard_permission('activity.delete');
	api_require_post(page_url('activities-manage'));

	(new AcademicModel())->deleteActivity((int) ($_GET['id'] ?? 0));
	set_flash('success', 'Đã xóa hoạt động ngoại khóa.');
	redirect(page_url('activities-manage'));
}
