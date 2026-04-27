<?php
require_any_permission(['academic.classes.view', 'academic.schedules.view']);

$academicModel = new AcademicModel();
$classPage = max(1, (int) ($_GET['class_page'] ?? 1));
$classPerPage = ui_pagination_resolve_per_page('class_per_page', 10);

$currentUserRole = (string) (auth_user()['role'] ?? '');
$currentUserId = (int) (auth_user()['id'] ?? 0);
$teacherId = ($currentUserRole === 'teacher' && $currentUserId > 0) ? $currentUserId : 0;

$classTotal = $academicModel->countClasses($teacherId);
$classTotalPages = max(1, (int) ceil($classTotal / $classPerPage));
if ($classPage > $classTotalPages) {
    $classPage = $classTotalPages;
}
$classes = $academicModel->listClassesPage($classPage, $classPerPage, $teacherId);
$classPerPageOptions = ui_pagination_per_page_options();
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
            <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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

        <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Danh sách lớp học</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm" data-disable-row-detail="1">
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
                                    <?php if (can_access_page('classrooms-academic')): ?>
                                        <a
                                            href="<?= e(page_url('classrooms-academic', ['course_id' => (int) ($class['course_id'] ?? 0), 'class_id' => (int) $class['id'], 'class_page' => $classPage, 'class_per_page' => $classPerPage])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-skip-action-icon="1"
                                            title="Chi tiết"
                                            aria-label="Chi tiết"
                                        >
                                            <span class="admin-action-icon-label">Chi tiết</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($canUpdateClass): ?>
                                        <a
                                            href="<?= e(page_url('classes-academic-edit', ['id' => (int) $class['id'], 'class_page' => $classPage, 'class_per_page' => $classPerPage])); ?>"
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
                <?php if ($classTotal > 0): ?>
                    <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                            <span class="font-medium">Trang <?= (int) $classPage; ?>/<?= (int) $classTotalPages; ?> - Tổng <?= (int) $classTotal; ?> lớp học</span>
                            <div class="inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('classes-academic')); ?>">
                                    <input type="hidden" name="page" value="classes-academic">
                                    <label class="text-[11px] font-semibold text-slate-500" for="class-per-page">Số dòng</label>
                                    <select id="class-per-page" name="class_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                        <?php foreach ($classPerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $classPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php if ($classPage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classes-academic', ['class_page' => $classPage - 1, 'class_per_page' => $classPerPage])); ?>">Trước</a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                                <?php endif; ?>

                                <?php if ($classPage < $classTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classes-academic', ['class_page' => $classPage + 1, 'class_per_page' => $classPerPage])); ?>">Sau</a>
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




