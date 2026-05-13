<?php
require_once __DIR__ . '/../../core/file_storage.php';

$materialId = (int) ($_GET['id'] ?? 0);
if ($materialId > 0) {
    require_permission('materials.update');
} else {
    require_permission('materials.create');
}

$academicModel = new AcademicModel();
$editingMaterial = $materialId > 0 ? $academicModel->findMaterial($materialId) : null;
$existingMaterialFilePath = normalize_public_file_url((string) ($editingMaterial['file_path'] ?? ''));

$module = 'materials';
$adminTitle = $editingMaterial ? t('admin.material_edit.title_edit') : t('admin.material_edit.title_add');
?>
<div class="grid gap-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= e($editingMaterial ? t('admin.material_edit.heading_edit') : t('admin.material_edit.heading_add')); ?></h2>
        <form class="grid gap-3" method="post" action="/api/materials/save" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" value="<?= (int) ($editingMaterial['id'] ?? 0); ?>">
            <input type="hidden" name="existing_file_path" value="<?= e($existingMaterialFilePath); ?>">
            <label>
                <?= e(t('admin.material_edit.material_title')); ?>
                <input type="text" name="title" value="<?= e((string) ($editingMaterial['title'] ?? '')); ?>" required>
            </label>
            <div>
                <label for="material-edit-description"><?= e(t('admin.material_edit.description')); ?></label>
                <?= render_bbcode_editor('description', (string) ($editingMaterial['description'] ?? ''), ['id' => 'material-edit-description', 'rows' => 3, 'placeholder' => t('admin.material_edit.description_placeholder')]); ?>
            </div>
            <label>
                <?= e(t('admin.material_edit.upload_file')); ?>
                <input type="file" name="material_file" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.jpeg,.png,.mp4,.mov,.webm,.mp3,.avi">
            </label>
            <?php if ($existingMaterialFilePath !== ''): ?>
                <p class="text-xs text-slate-500"><?= e(t('admin.material_edit.current_file')); ?>: <a class="font-semibold text-blue-700 hover:underline" href="<?= e($existingMaterialFilePath); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.material_edit.open_file')); ?></a>. <?= e(t('admin.material_edit.replace_hint')); ?></p>
            <?php endif; ?>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.material_edit.save')); ?></button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('materials-academic')); ?>"><?= e(t('admin.common.back')); ?></a>
        </form>

        <?php if ($existingMaterialFilePath !== ''): ?>
            <div class="mt-5">
                <h3><?= e(t('admin.material_edit.preview')); ?></h3>
                <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $existingMaterialFilePath)): ?>
                    <img class="w-full rounded-xl" src="<?= e($existingMaterialFilePath); ?>" alt="preview">
                <?php elseif (preg_match('/\.(mp4|mov|webm)$/i', $existingMaterialFilePath)): ?>
                    <video class="w-full rounded-xl" controls><source src="<?= e($existingMaterialFilePath); ?>"></video>
                <?php else: ?>
                    <a href="<?= e($existingMaterialFilePath); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.material_edit.open_file')); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </article>
</div>
