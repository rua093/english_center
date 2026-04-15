<?php
$materialId = (int) ($_GET['id'] ?? 0);
if ($materialId > 0) {
    require_permission('materials.update');
} else {
    require_permission('materials.create');
}

$academicModel = new AcademicModel();
$editingMaterial = $materialId > 0 ? $academicModel->findMaterial($materialId) : null;
$materialCourses = $academicModel->classLookups()['courses'] ?? [];

$module = 'materials';
$adminTitle = $editingMaterial ? 'Học vụ - Sửa tài liệu' : 'Học vụ - Thêm tài liệu';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-3xl px-4 sm:px-6">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2><?= $editingMaterial ? 'Chỉnh sửa tài liệu' : 'Thêm tài liệu'; ?></h2>
            <form class="grid gap-3" method="post" action="/api/materials/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingMaterial['id'] ?? 0); ?>">
                <label>Khóa học
                    <select name="course_id" required>
                        <option value="">-- Chọn khóa học --</option>
                        <?php foreach ($materialCourses as $course): ?>
                            <option value="<?= (int) $course['id']; ?>" <?= (int) ($editingMaterial['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : ''; ?>><?= e((string) $course['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Tiêu đề<input type="text" name="title" value="<?= e((string) ($editingMaterial['title'] ?? '')); ?>" required></label>
                <label>Kiểu tài liệu
                    <select name="type" required>
                        <option value="pdf" <?= (($editingMaterial['type'] ?? 'pdf') === 'pdf') ? 'selected' : ''; ?>>PDF</option>
                        <option value="mp3" <?= (($editingMaterial['type'] ?? '') === 'mp3') ? 'selected' : ''; ?>>MP3</option>
                        <option value="video" <?= (($editingMaterial['type'] ?? '') === 'video') ? 'selected' : ''; ?>>Video</option>
                    </select>
                </label>
                <label>Tải lên file<input type="file" name="material_file" accept=".pdf,.ppt,.pptx,.doc,.docx,.jpg,.jpeg,.png,.mp4,.mov,.webm"></label>
                <label>Hoặc đường dẫn file<input type="text" name="file_path" value="<?= e((string) ($editingMaterial['file_path'] ?? '')); ?>"></label>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu tài liệu</button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('materials-academic')); ?>">Quay lại</a>
            </form>

            <?php if (!empty($editingMaterial['file_path'])): ?>
                <div class="mt-5">
                    <h3>Xem trước</h3>
                    <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', (string) $editingMaterial['file_path'])): ?>
                        <img class="w-full rounded-xl" src="<?= e((string) $editingMaterial['file_path']); ?>" alt="preview">
                    <?php elseif (preg_match('/\.(mp4|mov|webm)$/i', (string) $editingMaterial['file_path'])): ?>
                        <video class="w-full rounded-xl" controls><source src="<?= e((string) $editingMaterial['file_path']); ?>"></video>
                    <?php else: ?>
                        <a href="<?= e((string) $editingMaterial['file_path']); ?>" target="_blank">Mở file</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>


