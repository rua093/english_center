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
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 grid gap-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1>Quản lý học vụ</h1>
                <p>CRUD lớp học, lịch học, bài tập và chấm điểm theo quyền.</p>
            </div>
            <?php if (can_access_page('student-dashboard')): ?>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="/?page=student-dashboard">Bảng điều khiển học viên</a>
            <?php elseif (can_access_page('teacher-dashboard')): ?>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="/?page=teacher-dashboard">Bảng điều khiển giáo viên</a>
            <?php endif; ?>
        </div>

        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-emerald-200 bg-emerald-50 text-emerald-700">Vận hành học vụ</span>
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-amber-200 bg-amber-50 text-amber-700">Chỉnh sửa nhanh</span>
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-rose-200 bg-rose-50 text-rose-700">Theo phân quyền</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if ($canCreateClass || $canUpdateClass): ?>
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="/?page=academic-class-edit"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Create</span><span class="text-xs font-bold">Thêm lớp</span></a>
                <?php endif; ?>
                <?php if ($canCreateSchedule || $canUpdateSchedule): ?>
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="/?page=academic-schedule-edit"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Schedule</span><span class="text-xs font-bold">Thêm lịch</span></a>
                <?php endif; ?>
                <?php if ($canCreateAssignment || $canUpdateAssignment): ?>
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="/?page=academic-assignment-edit"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Assignment</span><span class="text-xs font-bold">Thêm bài tập</span></a>
                <?php endif; ?>
                <?php if ($canCreateMaterial || $canUpdateMaterial): ?>
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="/?page=academic-material-edit"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Material</span><span class="text-xs font-bold">Thêm tài liệu</span></a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <div class="mb-3 flex flex-wrap gap-2">
            <?php if (can_access_page('academic-classes')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'classes' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="/?page=academic-classes">Lớp học</a>
            <?php endif; ?>
            <?php if (can_access_page('academic-schedules')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'schedules' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="/?page=academic-schedules">Lịch học</a>
            <?php endif; ?>
            <?php if (can_access_page('academic-assignments')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'assignments' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="/?page=academic-assignments">Bài tập</a>
            <?php endif; ?>
            <?php if (can_access_page('academic-materials')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'materials' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="/?page=academic-materials">Tài liệu</a>
            <?php endif; ?>
            <?php if (can_access_page('academic-portfolios')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'portfolios' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="/?page=academic-portfolios">Portfolio</a>
            <?php endif; ?>
            <?php if (can_access_page('academic-submissions')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'submissions' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="/?page=academic-submissions">Chấm điểm</a>
            <?php endif; ?>
        </div>

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
                                        <a href="/?page=academic-schedule-edit&id=<?= (int) $schedule['id']; ?>">Sửa</a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteSchedule): ?>
                                        <form class="inline-block" method="post" action="/api/schedules/delete?id=<?= (int) $schedule['id']; ?>">
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
</section>




