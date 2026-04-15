<?php
require_admin_or_staff();
require_permission('activity.view');

$academicModel = new AcademicModel();
$activities = $academicModel->listActivities();
$editingActivity = null;
if (!empty($_GET['edit'])) {
	$editingActivity = $academicModel->findActivity((int) $_GET['edit']);
}

$module = 'activities';
$adminTitle = 'Quản lý hoạt động';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <?php
        $canCreateActivity = has_permission('activity.create');
        $canUpdateActivity = has_permission('activity.update');
        $canDeleteActivity = has_permission('activity.delete');
        ?>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1>Quản lý hoạt động ngoại khóa</h1>
                <p>Tạo và quản lý các hoạt động, sự kiện ngoại khóa.</p>
            </div>
        </div>

        <?php if ($canCreateActivity || $canUpdateActivity): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3><?= $editingActivity ? 'Sửa hoạt động' : 'Thêm hoạt động'; ?></h3>
                <form class="grid gap-3" method="post" action="/api/activities/save">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id" value="<?= (int) ($editingActivity['id'] ?? 0); ?>">
                    <label>
                        Tên hoạt động
                        <input type="text" name="activity_name" value="<?= e((string) ($editingActivity['activity_name'] ?? '')); ?>" required>
                    </label>
                    <label>
                        Mô tả
                        <textarea name="description" rows="4"><?= e((string) ($editingActivity['description'] ?? '')); ?></textarea>
                    </label>
                    <label>
                        Nội dung chi tiết
                        <textarea name="content" rows="4"><?= e((string) ($editingActivity['content'] ?? '')); ?></textarea>
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
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu hoạt động</button>
                </form>
            </article>
        <?php endif; ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mt-6">
            <h3>Danh sách hoạt động</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>Tên hoạt động</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Địa điểm</th>
                            <th>Tham gia / Tối đa</th>
                            <th>Thao tác</th>
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
                                    <td><?= e((string) ($act['location'] ?? '')); ?></td>
                                    <td><?= (int) $act['registered']; ?> / <?= (int) $act['max_participants']; ?></td>
                                    <td>
                                        <span class="inline-flex flex-wrap items-center gap-2">
                                            <?php if ($canUpdateActivity): ?>
                                                <a href="<?= e(page_url('activities-manage', ['edit' => (int) $act['id']])); ?>">Sửa</a>
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
            </div>
        </div>
    </div>
</section>



