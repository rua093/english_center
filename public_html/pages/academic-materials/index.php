<?php
require_permission('materials.view');

$academicModel = new AcademicModel();
$materials = $academicModel->listMaterials();
$materialCourses = $academicModel->classLookups();

$editingMaterial = null;
if (!empty($_GET['edit'])) {
    $editingMaterial = $academicModel->findMaterial((int) $_GET['edit']);
}

$module = 'materials';
$adminTitle = 'Học vụ - Tài liệu';

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');

$canCreateAssignment = has_permission('academic.assignments.create');
$canUpdateAssignment = has_permission('academic.assignments.update');

$canCreateMaterial = has_permission('materials.create');
$canUpdateMaterial = has_permission('materials.update');
$canDeleteMaterial = has_permission('materials.delete');
?>
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($canCreateMaterial || $canUpdateMaterial): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingMaterial ? 'Sửa tài liệu' : 'Thêm tài liệu'; ?></h3>
            <form class="grid gap-3" method="post" action="/api/materials/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingMaterial['id'] ?? 0); ?>">
                <label>
                    Khóa học
                    <select name="course_id" required>
                        <?php foreach ($materialCourses['courses'] as $course): ?>
                            <option value="<?= (int) $course['id']; ?>" <?= (int) ($editingMaterial['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : ''; ?>><?= e((string) $course['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tiêu đề tài liệu
                    <input type="text" name="title" required value="<?= e((string) ($editingMaterial['title'] ?? '')); ?>">
                </label>
                <label>
                    Kiểu tài liệu
                    <select name="type" required>
                        <option value="pdf" <?= (($editingMaterial['type'] ?? 'pdf') === 'pdf') ? 'selected' : ''; ?>>PDF</option>
                        <option value="mp3" <?= (($editingMaterial['type'] ?? '') === 'mp3') ? 'selected' : ''; ?>>MP3</option>
                        <option value="video" <?= (($editingMaterial['type'] ?? '') === 'video') ? 'selected' : ''; ?>>Video</option>
                    </select>
                </label>
                <label>
                    Tải lên file
                    <input type="file" name="material_file" accept=".pdf,.mp3,.mp4,.mov,.avi">
                </label>
                <label>
                    Hoặc đường dẫn file hiện có
                    <input type="text" name="file_path" value="<?= e((string) ($editingMaterial['file_path'] ?? '')); ?>">
                </label>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu tài liệu</button>
            </form>
        </article>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr><th>Tiêu đề</th><th>Khóa học</th><th>Kiểu</th><th>File</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($materials)): ?>
                        <tr><td colspan="5"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có tài liệu nào.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?= e((string) $material['title']); ?></td>
                            <td><?= e((string) $material['course_name']); ?></td>
                            <td><?= e((string) $material['type']); ?></td>
                            <td><?= e((string) $material['file_path']); ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($canUpdateMaterial): ?>
                                        <a
                                            href="<?= e(page_url('materials-academic-edit', ['id' => (int) $material['id']])); ?>"
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
                                    <?php if ($canDeleteMaterial): ?>
                                        <form class="inline-block" method="post" action="/api/materials/delete?id=<?= (int) $material['id']; ?>">
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




