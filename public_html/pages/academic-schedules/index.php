<?php
require_permission('academic.schedules.view');

$academicModel = new AcademicModel();
$schedules = $academicModel->listSchedules();
$lookups = $academicModel->scheduleLookups();

$editingSchedule = null;
if (!empty($_GET['edit'])) {
    $editingSchedule = $academicModel->findSchedule((int) $_GET['edit']);
}

$module = 'schedules';
$adminTitle = 'Học vụ - Lịch học';

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');
$canDeleteSchedule = has_permission('academic.schedules.delete');

$canCreateAssignment = has_permission('academic.assignments.create');
$canUpdateAssignment = has_permission('academic.assignments.update');

$canCreateMaterial = has_permission('materials.create');
$canUpdateMaterial = has_permission('materials.update');
?>
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($canCreateSchedule || $canUpdateSchedule): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingSchedule ? 'Sửa lịch học' : 'Thêm lịch học'; ?></h3>
            <form class="grid gap-3" method="post" action="/api/schedules/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingSchedule['id'] ?? 0); ?>">
                <label>
                    Lớp học
                    <select name="class_id" required>
                        <?php foreach ($lookups['classes'] as $class): ?>
                            <option value="<?= (int) $class['id']; ?>" <?= (int) ($editingSchedule['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : ''; ?>><?= e((string) $class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Phòng học
                    <select name="room_id" required>
                        <?php foreach ($lookups['rooms'] as $room): ?>
                            <option value="<?= (int) $room['id']; ?>" <?= (int) ($editingSchedule['room_id'] ?? 0) === (int) $room['id'] ? 'selected' : ''; ?>><?= e((string) $room['room_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Giáo viên
                    <select name="teacher_id" required>
                        <?php foreach ($lookups['teachers'] as $teacher): ?>
                            <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingSchedule['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e((string) $teacher['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Ngày học
                    <input type="date" name="study_date" required value="<?= e((string) ($editingSchedule['study_date'] ?? '')); ?>">
                </label>
                <label>
                    Giờ bắt đầu
                    <input type="time" name="start_time" required value="<?= e((string) ($editingSchedule['start_time'] ?? '')); ?>">
                </label>
                <label>
                    Giờ kết thúc
                    <input type="time" name="end_time" required value="<?= e((string) ($editingSchedule['end_time'] ?? '')); ?>">
                </label>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu lịch học</button>
            </form>
        </article>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr><th>Lớp học</th><th>Phòng</th><th>Giáo viên</th><th>Ngày học</th><th>Giờ</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                        <tr><td colspan="6"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lịch học nào.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= e((string) $schedule['class_name']); ?></td>
                            <td><?= e((string) ($schedule['room_name'] ?? '')); ?></td>
                            <td><?= e((string) $schedule['teacher_name']); ?></td>
                            <td><?= e((string) $schedule['study_date']); ?></td>
                            <td><?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($canUpdateSchedule): ?>
                                        <a
                                            href="<?= e(page_url('schedules-academic-edit', ['id' => (int) $schedule['id']])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="Sửa"
                                            aria-label="Sửa"
                                        >
                                            <span class="admin-action-icon-label">Sửa</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteSchedule): ?>
                                        <form class="inline-block" method="post" action="/api/schedules/delete?id=<?= (int) $schedule['id']; ?>">
                                            <?= csrf_input(); ?>
                                            <button
                                                class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                data-action-kind="delete"
                                                data-skip-action-icon="1"
                                                type="submit"
                                                title="Xóa"
                                                aria-label="Xóa"
                                            >
                                                <span class="admin-action-icon-label">Xóa</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                                </span>
                                            </button>
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




