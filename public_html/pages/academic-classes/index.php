<?php
require_permission('academic.classes.view');

$academicModel = new AcademicModel();
$classes = $academicModel->listClasses();
$lookups = $academicModel->classLookups();

$editingClass = null;
if (!empty($_GET['edit'])) {
    $editingClass = $academicModel->findClass((int) $_GET['edit']);
}

$module = 'classes';
$adminTitle = 'Học vụ - Lớp học';

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');
$canDeleteClass = has_permission('academic.classes.delete');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');

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
            <?php if (can_access_page('dashboard-student')): ?>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('dashboard-student')); ?>">Bảng điều khiển học viên</a>
            <?php elseif (can_access_page('dashboard-teacher')): ?>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('dashboard-teacher')); ?>">Bảng điều khiển giáo viên</a>
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
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="<?= e(page_url('classes-academic-edit')); ?>"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Create</span><span class="text-xs font-bold">Thêm lớp</span></a>
                <?php endif; ?>
                <?php if ($canCreateSchedule || $canUpdateSchedule): ?>
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="<?= e(page_url('schedules-academic-edit')); ?>"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Schedule</span><span class="text-xs font-bold">Thêm lịch</span></a>
                <?php endif; ?>
                <?php if ($canCreateAssignment || $canUpdateAssignment): ?>
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="<?= e(page_url('assignments-academic-edit')); ?>"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Assignment</span><span class="text-xs font-bold">Thêm bài tập</span></a>
                <?php endif; ?>
                <?php if ($canCreateMaterial || $canUpdateMaterial): ?>
                    <a class="<?= ui_quick_action_link_classes(); ?>" href="<?= e(page_url('materials-academic-edit')); ?>"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Material</span><span class="text-xs font-bold">Thêm tài liệu</span></a>
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
            <?php if (can_access_page('classes-academic')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'classes' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('classes-academic')); ?>">Lớp học</a>
            <?php endif; ?>
            <?php if (can_access_page('schedules-academic')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'schedules' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('schedules-academic')); ?>">Lịch học</a>
            <?php endif; ?>
            <?php if (can_access_page('assignments-academic')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'assignments' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('assignments-academic')); ?>">Bài tập</a>
            <?php endif; ?>
            <?php if (can_access_page('materials-academic')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'materials' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('materials-academic')); ?>">Tài liệu</a>
            <?php endif; ?>
            <?php if (can_access_page('portfolios-academic')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'portfolios' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('portfolios-academic')); ?>">Portfolio</a>
            <?php endif; ?>
            <?php if (can_access_page('submissions-academic')): ?>
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 <?= $module === 'submissions' ? 'border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('submissions-academic')); ?>">Chấm điểm</a>
            <?php endif; ?>
        </div>

        <?php if ($canCreateClass || $canUpdateClass): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3><?= $editingClass ? 'Sửa lớp học' : 'Thêm lớp học'; ?></h3>
                <form class="grid gap-3" method="post" action="/api/classes/save">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id" value="<?= (int) ($editingClass['id'] ?? 0); ?>">
                    <label>
                        Khóa học
                        <select name="course_id" required>
                            <?php foreach ($lookups['courses'] as $course): ?>
                                <option value="<?= (int) $course['id']; ?>" <?= (int) ($editingClass['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : ''; ?>><?= e((string) $course['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Tên lớp
                        <input type="text" name="class_name" required value="<?= e((string) ($editingClass['class_name'] ?? '')); ?>">
                    </label>
                    <label>
                        Giáo viên
                        <select name="teacher_id" required>
                            <?php foreach ($lookups['teachers'] as $teacher): ?>
                                <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingClass['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e((string) $teacher['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Ngày bắt đầu
                        <input type="date" name="start_date" value="<?= e((string) ($editingClass['start_date'] ?? '')); ?>">
                    </label>
                    <label>
                        Ngày kết thúc
                        <input type="date" name="end_date" value="<?= e((string) ($editingClass['end_date'] ?? '')); ?>">
                    </label>
                    <label>
                        Trạng thái
                        <select name="status">
                            <option value="upcoming" <?= (($editingClass['status'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>>Sắp mở</option>
                            <option value="active" <?= (($editingClass['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Đang học</option>
                            <option value="graduated" <?= (($editingClass['status'] ?? '') === 'graduated') ? 'selected' : ''; ?>>Đã tốt nghiệp</option>
                            <option value="cancelled" <?= (($editingClass['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </label>
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu lớp học</button>
                </form>
            </article>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr><th>Tên lớp</th><th>Khóa học</th><th>Giáo viên</th><th>Trạng thái</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr><td colspan="5"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lớp học nào.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= e((string) $class['class_name']); ?></td>
                            <td><?= e((string) $class['course_name']); ?></td>
                            <td><?= e((string) $class['teacher_name']); ?></td>
                            <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $class['status']); ?>"><?= e((string) $class['status']); ?></span></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($canUpdateClass): ?>
                                        <a href="<?= e(page_url('classes-academic-edit', ['id' => (int) $class['id']])); ?>">Sửa</a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteClass): ?>
                                        <form class="inline-block" method="post" action="/api/classes/delete?id=<?= (int) $class['id']; ?>">
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




