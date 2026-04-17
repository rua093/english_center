<?php
require_admin_or_staff();
require_permission('activity.view');

$academicModel = new AcademicModel();
$editingActivity = null;
if (!empty($_GET['edit'])) {
    $editingActivity = $academicModel->findActivity((int) $_GET['edit']);
}

$activityPage = max(1, (int) ($_GET['activity_page'] ?? 1));
$activityPerPage = ui_pagination_resolve_per_page('activity_per_page', 10);
$activityTotal = $academicModel->countActivities();
$activityTotalPages = max(1, (int) ceil($activityTotal / $activityPerPage));
if ($activityPage > $activityTotalPages) {
    $activityPage = $activityTotalPages;
}
$activities = $academicModel->listActivitiesPage($activityPage, $activityPerPage);
$activityPerPageOptions = ui_pagination_per_page_options();

$module = 'activities';
$adminTitle = 'Quản lý hoạt động';

$success = get_flash('success');
$error = get_flash('error');
?>
<div class="grid gap-4">
    <?php
    $canCreateActivity = has_permission('activity.create');
    $canUpdateActivity = has_permission('activity.update');
    $canDeleteActivity = has_permission('activity.delete');
    ?>

    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreateActivity || $canUpdateActivity): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingActivity ? 'Sửa hoạt động' : 'Thêm hoạt động'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/activities/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingActivity['id'] ?? 0); ?>">
                <label>
                    Tên hoạt động
                    <input type="text" name="activity_name" value="<?= e((string) ($editingActivity['activity_name'] ?? '')); ?>" required>
                </label>
                <label class="md:col-span-2">
                    Mô tả
                    <textarea name="description" rows="4"><?= e((string) ($editingActivity['description'] ?? '')); ?></textarea>
                </label>
                <label class="md:col-span-2">
                    Nội dung chi tiết
                    <textarea name="content" rows="4"><?= e((string) ($editingActivity['content'] ?? '')); ?></textarea>
                </label>
                <label>
                    Địa điểm
                    <input type="text" name="location" value="<?= e((string) ($editingActivity['location'] ?? '')); ?>" placeholder="Ví dụ: Cơ sở A - Phòng 203">
                </label>
                <label>
                    Ngày bắt đầu
                    <input type="date" name="start_date" value="<?= e((string) ($editingActivity['start_date'] ?? '')); ?>" required>
                </label>
                <label>
                    Trạng thái
                    <select name="status">
                        <option value="upcoming" <?= (($editingActivity['status'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>>upcoming</option>
                        <option value="ongoing" <?= (($editingActivity['status'] ?? '') === 'ongoing') ? 'selected' : ''; ?>>ongoing</option>
                        <option value="finished" <?= (($editingActivity['status'] ?? '') === 'finished') ? 'selected' : ''; ?>>finished</option>
                    </select>
                </label>
                <label>
                    Phí tham gia
                    <input type="number" step="1000" min="0" name="fee" value="<?= (float) ($editingActivity['fee'] ?? 0); ?>">
                </label>
                <label>
                    Thumbnail URL
                    <input type="text" name="image_thumbnail" value="<?= e((string) ($editingActivity['image_thumbnail'] ?? '')); ?>">
                </label>
                <div class="md:col-span-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu hoạt động</button>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách hoạt động</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th>Tên hoạt động</th>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Địa điểm</th>
                        <th>Số đăng ký</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activities)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có hoạt động nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <tr>
                                <td><?= e((string) $act['activity_name']); ?></td>
                                <td><?= e((string) ($act['start_date'] ?? '')); ?></td>
                                <td><?= e((string) ($act['end_date'] ?? '')); ?></td>
                                <td><?= e((string) ($act['location'] ?? '-')); ?></td>
                                <td><?= (int) $act['registered']; ?></td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateActivity): ?>
                                            <a href="<?= e(page_url('activities-manage', ['edit' => (int) $act['id'], 'activity_page' => $activityPage, 'activity_per_page' => $activityPerPage])); ?>">Sửa</a>
                                        <?php endif; ?>
                                        <?php if ($canDeleteActivity): ?>
                                            <form class="inline-block" method="post" action="/api/activities/delete?id=<?= (int) $act['id']; ?>" onsubmit="return confirm('Có chắc không?')">
                                                <?= csrf_input(); ?>
                                                <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa</button>
                                            </form>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($activityTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $activityPage; ?>/<?= (int) $activityTotalPages; ?> - Tổng <?= (int) $activityTotal; ?> hoạt động</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('activities-manage')); ?>">
                                <input type="hidden" name="page" value="activities-manage">
                                <label class="text-[11px] font-semibold text-slate-500" for="activity-per-page">Số dòng</label>
                                <select id="activity-per-page" name="activity_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($activityPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $activityPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($activityPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage - 1, 'activity_per_page' => $activityPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($activityPage < $activityTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-manage', ['activity_page' => $activityPage + 1, 'activity_per_page' => $activityPerPage])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>
