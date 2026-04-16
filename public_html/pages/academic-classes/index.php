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
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

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
                                        <a
                                            href="<?= e(page_url('classes-academic-edit', ['id' => (int) $class['id']])); ?>"
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
                                    <?php if ($canDeleteClass): ?>
                                        <form class="inline-block" method="post" action="/api/classes/delete?id=<?= (int) $class['id']; ?>">
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




