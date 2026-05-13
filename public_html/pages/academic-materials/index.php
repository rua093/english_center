<?php
require_any_permission(['materials.view']);
require_once __DIR__ . '/../../core/file_storage.php';

$academicModel = new AcademicModel();
$materialPage = max(1, (int) ($_GET['material_page'] ?? 1));
$materialPerPage = ui_pagination_resolve_per_page('material_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$materialTotal = $academicModel->countMaterials($searchQuery);
$materialTotalPages = max(1, (int) ceil($materialTotal / $materialPerPage));
if ($materialPage > $materialTotalPages) {
    $materialPage = $materialTotalPages;
}
$materials = $academicModel->listMaterialsPage($materialPage, $materialPerPage, $searchQuery);
$materialPerPageOptions = ui_pagination_per_page_options();
$editingMaterial = null;
if (!empty($_GET['edit'])) {
    $editingMaterial = $academicModel->findMaterial((int) $_GET['edit']);
}

$editingMaterialFilePath = normalize_public_file_url((string) ($editingMaterial['file_path'] ?? ''));

$module = 'materials';
$adminTitle = t('admin.materials.title');

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
            <h3><?= e($editingMaterial ? t('admin.materials.edit') : t('admin.materials.add')); ?></h3>
            <form class="grid gap-3" method="post" action="/api/materials/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingMaterial['id'] ?? 0); ?>">
                <input type="hidden" name="existing_file_path" value="<?= e($editingMaterialFilePath); ?>">
                <label>
                    <?= e(t('admin.material_edit.material_title')); ?>
                    <input type="text" name="title" required value="<?= e((string) ($editingMaterial['title'] ?? '')); ?>">
                </label>
                <div>
                    <label for="material-description"><?= e(t('admin.material_edit.description')); ?></label>
                    <?= render_bbcode_editor('description', (string) ($editingMaterial['description'] ?? ''), ['id' => 'material-description', 'rows' => 3, 'placeholder' => t('admin.material_edit.description_placeholder')]); ?>
                </div>
                <label>
                    <?= e(t('admin.material_edit.upload_file')); ?>
                    <input type="file" name="material_file" accept=".pdf,.mp3,.mp4,.mov,.avi,.doc,.docx,.ppt,.pptx,.jpg,.png">
                </label>
                <?php if ($editingMaterialFilePath !== ''): ?>
                    <p class="text-xs text-slate-500"><?= e(t('admin.material_edit.current_file')); ?>: <a class="font-semibold text-blue-700 hover:underline" href="<?= e($editingMaterialFilePath); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.material_edit.open_file')); ?></a>. <?= e(t('admin.material_edit.replace_hint')); ?></p>
                <?php endif; ?>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.material_edit.save')); ?></button>
            </form>
        </article>
        <?php endif; ?>

        <article
            class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="materials-academic"
            data-ajax-page-param="material_page"
            data-ajax-search-param="search"
        >
            <h3><?= e(t('admin.materials.list')); ?></h3>
            <div class="admin-table-toolbar mb-3 flex flex-wrap items-center gap-3">
                <label class="relative w-full max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input data-ajax-search="1" type="search" value="<?= e($searchQuery); ?>" placeholder="<?= e(t('admin.materials.search_placeholder')); ?>" autocomplete="off" class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </label>
                <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500"><?= e(t('admin.materials.showing_rows', ['shown' => (string) count($materials), 'total' => (string) $materialTotal])); ?></span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr><th><?= e(t('admin.material_edit.material_title')); ?></th><th><?= e(t('admin.material_edit.description')); ?></th><th><?= e(t('admin.common.actions')); ?></th></tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($materials)): ?>
                        <tr><td colspan="3"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.materials.empty')); ?></div></td></tr>
                    <?php else: ?>
                    <?php foreach ($materials as $material): ?>
                        <?php $materialFilePath = normalize_public_file_url((string) ($material['file_path'] ?? '')); ?>
                        <tr>
                            <td><?= e((string) $material['title']); ?></td>
                            <td>
                                <?php $materialDescription = trim((string) ($material['description'] ?? '')); ?>
                                <?php if ($materialDescription === ''): ?>
                                    <span class="text-slate-400">-</span>
                                <?php else: ?>
                                    <div class="bbcode-content"><?= bbcode_to_html($materialDescription); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($materialFilePath !== ''): ?>
                                        <a
                                            href="<?= e($materialFilePath); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.material_edit.open_file')); ?>"
                                            aria-label="<?= e(t('admin.material_edit.open_file')); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.material_edit.open_file')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400"><?= e(t('admin.materials.no_file')); ?></span>
                                    <?php endif; ?>
                                    <?php if ($canUpdateMaterial): ?>
                                        <a
                                            href="<?= e(page_url('materials-academic-edit', ['id' => (int) $material['id'], 'material_page' => $materialPage, 'material_per_page' => $materialPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.common.edit')); ?>"
                                            aria-label="<?= e(t('admin.common.edit')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.common.edit')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($canDeleteMaterial): ?>
                                        <form class="inline-block" method="post" action="/api/materials/delete?id=<?= (int) $material['id']; ?>" onsubmit="return confirm(<?= e(json_encode(t('admin.materials.delete_confirm'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>);">
                                            <?= csrf_input(); ?>
                                            <button
                                                class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                data-action-kind="delete"
                                                data-skip-action-icon="1"
                                                type="submit"
                                                title="<?= e(t('admin.common.delete')); ?>"
                                                aria-label="<?= e(t('admin.common.delete')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.common.delete')); ?></span>
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
                    <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                            <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.materials.page_info', ['current' => (string) $materialPage, 'total' => (string) $materialTotalPages, 'count' => (string) $materialTotal])); ?></span>
                            <div class="ml-auto inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('materials-academic')); ?>">
                                    <input type="hidden" name="page" value="materials-academic">
                                    <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                    <label class="text-[11px] font-semibold text-slate-500" for="material-per-page"><?= e(t('admin.common.rows')); ?></label>
                                    <select id="material-per-page" name="material_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                        <?php foreach ($materialPerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $materialPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php if ($materialPage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('materials-academic', ['material_page' => $materialPage - 1, 'material_per_page' => $materialPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                                <?php endif; ?>

                                <?php if ($materialPage < $materialTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('materials-academic', ['material_page' => $materialPage + 1, 'material_per_page' => $materialPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.next')); ?></a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </div>




