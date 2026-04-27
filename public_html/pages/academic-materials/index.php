<?php
require_any_permission(['materials.view']);
require_once __DIR__ . '/../../core/file_storage.php';

$academicModel = new AcademicModel();
$materialPage = max(1, (int) ($_GET['material_page'] ?? 1));
$materialPerPage = ui_pagination_resolve_per_page('material_per_page', 10);
$materialTotal = $academicModel->countMaterials();
$materialTotalPages = max(1, (int) ceil($materialTotal / $materialPerPage));
if ($materialPage > $materialTotalPages) {
    $materialPage = $materialTotalPages;
}
$materials = $academicModel->listMaterialsPage($materialPage, $materialPerPage);
$materialPerPageOptions = ui_pagination_per_page_options();
$materialCourses = $academicModel->classLookups();

$editingMaterial = null;
if (!empty($_GET['edit'])) {
    $editingMaterial = $academicModel->findMaterial((int) $_GET['edit']);
}

$editingMaterialFilePath = normalize_public_file_url((string) ($editingMaterial['file_path'] ?? ''));

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
        <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingMaterial ? 'Sửa tài liệu' : 'Thêm tài liệu'; ?></h3>
            <form class="grid gap-3" method="post" action="/api/materials/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingMaterial['id'] ?? 0); ?>">
                <input type="hidden" name="existing_file_path" value="<?= e($editingMaterialFilePath); ?>">
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
                    Mô tả tài liệu
                    <textarea name="description" rows="3" placeholder="Mô tả ngắn về nội dung tài liệu"><?= e((string) ($editingMaterial['description'] ?? '')); ?></textarea>
                </label>
                <label>
                    Tải lên file đính kèm
                    <input type="file" name="material_file" accept=".pdf,.mp3,.mp4,.mov,.avi,.doc,.docx,.ppt,.pptx,.jpg,.png">
                </label>
                <?php if ($editingMaterialFilePath !== ''): ?>
                    <p class="text-xs text-slate-500">File hiện tại: <a class="font-semibold text-blue-700 hover:underline" href="<?= e($editingMaterialFilePath); ?>" target="_blank" rel="noopener noreferrer">Mở file</a>. Chọn file mới để thay thế.</p>
                <?php endif; ?>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu tài liệu</button>
            </form>
        </article>
        <?php endif; ?>

        <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Danh sách tài liệu</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr><th>Tiêu đề</th><th>Khóa học</th><th>Mô tả tài liệu</th><th>Hành động</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($materials)): ?>
                        <tr><td colspan="4"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có tài liệu nào.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($materials as $material): ?>
                        <?php $materialFilePath = normalize_public_file_url((string) ($material['file_path'] ?? '')); ?>
                        <tr>
                            <td><?= e((string) $material['title']); ?></td>
                            <td><?= e((string) $material['course_name']); ?></td>
                            <td><?= e((string) ($material['description'] ?? '-')); ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($materialFilePath !== ''): ?>
                                        <a
                                            href="<?= e($materialFilePath); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-skip-action-icon="1"
                                            title="Mở file"
                                            aria-label="Mở file"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span class="admin-action-icon-label">Mở file</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">Chưa có file</span>
                                    <?php endif; ?>
                                    <?php if ($canUpdateMaterial): ?>
                                        <a
                                            href="<?= e(page_url('materials-academic-edit', ['id' => (int) $material['id'], 'material_page' => $materialPage, 'material_per_page' => $materialPerPage])); ?>"
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
                <?php if ($materialTotal > 0): ?>
                    <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                            <span class="font-medium">Trang <?= (int) $materialPage; ?>/<?= (int) $materialTotalPages; ?> - Tổng <?= (int) $materialTotal; ?> tài liệu</span>
                            <div class="inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('materials-academic')); ?>">
                                    <input type="hidden" name="page" value="materials-academic">
                                    <label class="text-[11px] font-semibold text-slate-500" for="material-per-page">Số dòng</label>
                                    <select id="material-per-page" name="material_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                        <?php foreach ($materialPerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $materialPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php if ($materialPage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('materials-academic', ['material_page' => $materialPage - 1, 'material_per_page' => $materialPerPage])); ?>">Trước</a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                                <?php endif; ?>

                                <?php if ($materialPage < $materialTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('materials-academic', ['material_page' => $materialPage + 1, 'material_per_page' => $materialPerPage])); ?>">Sau</a>
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




