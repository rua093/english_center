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
        <form id="material-edit-upload-form" class="grid gap-3" method="post" action="/api/materials/save" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" value="<?= (int) ($editingMaterial['id'] ?? 0); ?>">
            <input type="hidden" name="existing_file_path" value="<?= e($existingMaterialFilePath); ?>">
            <input type="hidden" id="materialEditUploadedFileUrl" name="uploaded_file_url" value="">
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
                <input id="materialEditFileInput" type="file" name="material_file" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.jpeg,.png,.mp4,.mov,.webm,.mp3,.avi" data-direct-upload-preset="material_file">
            </label>
            <p id="materialEditUploadStatus" class="text-xs text-slate-500">Tài liệu sẽ được tải lên khi bạn chọn file.</p>
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
<script>
(function () {
    const form = document.getElementById('material-edit-upload-form');
    const fileInput = document.getElementById('materialEditFileInput');
    const hiddenInput = document.getElementById('materialEditUploadedFileUrl');
    const status = document.getElementById('materialEditUploadStatus');
    const submitButton = form ? form.querySelector('button[type="submit"]') : null;
    let isUploading = false;

    function isVideoFile(file) {
        if (!file) {
            return false;
        }

        const type = String(file.type || '').toLowerCase();
        if (type.startsWith('video/')) {
            return true;
        }

        return /\.(mp4|mov|webm|avi|m4v)$/i.test(String(file.name || ''));
    }

    if (!(form instanceof HTMLFormElement) || !(fileInput instanceof HTMLInputElement) || !(hiddenInput instanceof HTMLInputElement)) {
        return;
    }

    function setUploadingState(uploading, message) {
        isUploading = uploading;
        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = uploading;
        }
        if (status instanceof HTMLElement && typeof message === 'string') {
            status.textContent = message;
        }
    }

    async function uploadDirect(file) {
        const signResponse = await fetch('/api/index.php?resource=uploads&method=direct-sign&format=json', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: new URLSearchParams({
                preset: 'material_file',
                filename: file.name,
                content_type: file.type || 'application/octet-stream',
                file_size: String(file.size || 0),
                _token: <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            }),
        });
        const signPayload = await signResponse.json().catch(function () { return null; });
        if (!signResponse.ok || !signPayload || signPayload.status !== 'success' || !signPayload.data) {
            throw new Error((signPayload && signPayload.message) || 'Không thể tải file lên lúc này.');
        }

        const spec = signPayload.data;
        const headers = Object.assign({}, spec.headers || {});
        if (!headers['Content-Type']) {
            headers['Content-Type'] = file.type || 'application/octet-stream';
        }

        const uploadResponse = await fetch(spec.upload_url, {
            method: spec.method || 'PUT',
            headers: headers,
            body: file,
        });
        if (!uploadResponse.ok) {
            throw new Error('Không thể tải tài liệu lên.');
        }

        hiddenInput.value = String(spec.public_url || '');
        fileInput.value = '';
    }

    fileInput.addEventListener('change', async function () {
        const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        hiddenInput.value = '';

        if (!file) {
            setUploadingState(false, 'Tài liệu sẽ được tải lên khi bạn chọn file.');
            return;
        }

        try {
            setUploadingState(true, 'Đang tải tài liệu lên...');
            await uploadDirect(file);
            setUploadingState(false, 'Đã tải tài liệu xong. Bạn có thể lưu ngay.');
        } catch (error) {
            hiddenInput.value = '';
            setUploadingState(false, isVideoFile(file)
                ? 'Video tài liệu chưa được tải lên. Vui lòng thử lại.'
                : 'Không thể tải tài liệu lên lúc này. Bạn vẫn có thể lưu theo cách thông thường.');
        }
    });

    form.addEventListener('submit', function (event) {
        if (isUploading) {
            event.preventDefault();
            setUploadingState(false, 'Tài liệu vẫn đang được tải lên. Vui lòng chờ hoàn tất.');
            return;
        }

        const hasSelectedFile = !!(fileInput.files && fileInput.files[0]);
        const hasDirectUrl = String(hiddenInput.value || '').trim() !== '';
        if (hasSelectedFile && !hasDirectUrl && isVideoFile(fileInput.files[0])) {
            event.preventDefault();
            setUploadingState(false, 'Video tài liệu chưa được tải lên. Vui lòng thử lại.');
        }
    });
})();
</script>
