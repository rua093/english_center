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
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

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
                                        <a
                                            href="<?= e(page_url('assignments-academic-edit', ['id' => (int) $assignment['id']])); ?>"
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
                                    <?php if ($canDeleteAssignment): ?>
                                        <form class="inline-block" method="post" action="/api/assignments/delete?id=<?= (int) $assignment['id']; ?>">
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




