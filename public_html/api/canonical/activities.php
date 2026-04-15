<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_activities_save_action(): void
{
	api_require_post(page_url('activities-manage'));

	$activityId = input_int($_POST, 'id');
	api_guard_permission($activityId > 0 ? 'activity.update' : 'activity.create');

	(new AcademicModel())->saveActivity($_POST);
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
