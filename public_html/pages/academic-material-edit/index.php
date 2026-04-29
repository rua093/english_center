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
$adminTitle = $editingMaterial ? 'Học vụ - Sửa tài liệu' : 'Học vụ - Thêm tài liệu';
?>
<div class="grid gap-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= $editingMaterial ? 'Chỉnh sửa tài liệu' : 'Thêm tài liệu'; ?></h2>
        <form class="grid gap-3" method="post" action="/api/materials/save" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" value="<?= (int) ($editingMaterial['id'] ?? 0); ?>">
            <input type="hidden" name="existing_file_path" value="<?= e($existingMaterialFilePath); ?>">
            <label>
                Tiêu đề tài liệu
                <input type="text" name="title" value="<?= e((string) ($editingMaterial['title'] ?? '')); ?>" required>
            </label>
            <label>
                Mô tả tài liệu
                <textarea name="description" rows="3" placeholder="Mô tả ngắn về nội dung tài liệu"><?= e((string) ($editingMaterial['description'] ?? '')); ?></textarea>
            </label>
            <label>
                Tải lên file đính kèm
                <input type="file" name="material_file" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.jpeg,.png,.mp4,.mov,.webm,.mp3,.avi">
            </label>
            <?php if ($existingMaterialFilePath !== ''): ?>
                <p class="text-xs text-slate-500">File hiện tại: <a class="font-semibold text-blue-700 hover:underline" href="<?= e($existingMaterialFilePath); ?>" target="_blank" rel="noopener noreferrer">Mở file</a>. Chọn file mới để thay thế.</p>
            <?php endif; ?>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu tài liệu</button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('materials-academic')); ?>">Quay lại</a>
        </form>

        <?php if ($existingMaterialFilePath !== ''): ?>
            <div class="mt-5">
                <h3>Xem trước</h3>
                <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $existingMaterialFilePath)): ?>
                    <img class="w-full rounded-xl" src="<?= e($existingMaterialFilePath); ?>" alt="preview">
                <?php elseif (preg_match('/\.(mp4|mov|webm)$/i', $existingMaterialFilePath)): ?>
                    <video class="w-full rounded-xl" controls><source src="<?= e($existingMaterialFilePath); ?>"></video>
                <?php else: ?>
                    <a href="<?= e($existingMaterialFilePath); ?>" target="_blank" rel="noopener noreferrer">Mở file</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </article>
</div>
