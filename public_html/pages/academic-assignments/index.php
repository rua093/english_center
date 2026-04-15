<?php
require_permission('academic.assignments.view');

$academicModel = new AcademicModel();
$assignments = $academicModel->listAssignments();
$lessons = $academicModel->assignmentLookups();

$editingAssignment = null;
if (!empty($_GET['edit'])) {
    $editingAssignment = $academicModel->findAssignment((int) $_GET['edit']);
}

$module = 'assignments';
$adminTitle = 'Học vụ - Bài tập';

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');

$canCreateAssignment = has_permission('academic.assignments.create');
$canUpdateAssignment = has_permission('academic.assignments.update');
$canDeleteAssignment = has_permission('academic.assignments.delete');

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

        <?php if ($canCreateAssignment || $canUpdateAssignment): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingAssignment ? 'Sửa bài tập' : 'Thêm bài tập'; ?></h3>
            <form class="grid gap-3" method="post" action="/api/assignments/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingAssignment['id'] ?? 0); ?>">
                <label>
                    Buổi học
                    <select name="lesson_id" required>
                        <?php foreach ($lessons as $lesson): ?>
                            <option value="<?= (int) $lesson['id']; ?>" <?= (int) ($editingAssignment['lesson_id'] ?? 0) === (int) $lesson['id'] ? 'selected' : ''; ?>><?= e((string) $lesson['actual_title']); ?> - <?= e((string) $lesson['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tiêu đề
                    <input type="text" name="title" required value="<?= e((string) ($editingAssignment['title'] ?? '')); ?>">
                </label>
                <label>
                    Mô tả
                    <textarea name="description" rows="4"><?= e((string) ($editingAssignment['description'] ?? '')); ?></textarea>
                </label>
                <label>
                    Hạn nộp
                    <input type="datetime-local" name="deadline" required value="<?= !empty($editingAssignment['deadline']) ? e(date('Y-m-d\TH:i', strtotime((string) $editingAssignment['deadline']))) : ''; ?>">
                </label>
                <label>
                    File URL
                    <input type="text" name="file_url" value="<?= e((string) ($editingAssignment['file_url'] ?? '')); ?>">
                </label>
                <label>
                    Tải lên file
                    <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
                </label>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu bài tập</button>
            </form>
        </article>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr><th>Bài tập</th><th>Lớp học</th><th>Hạn nộp</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($assignments)): ?>
                        <tr><td colspan="4"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có bài tập nào.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?= e((string) $assignment['title']); ?></td>
                            <td><?= e((string) $assignment['class_name']); ?></td>
                            <td><?= e((string) $assignment['deadline']); ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($canUpdateAssignment): ?>
                                        <a href="<?= e(page_url('assignments-academic-edit', ['id' => (int) $assignment['id']])); ?>">Sửa</a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteAssignment): ?>
                                        <form class="inline-block" method="post" action="/api/assignments/delete?id=<?= (int) $assignment['id']; ?>">
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




