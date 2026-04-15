<?php
$classId = (int) ($_GET['id'] ?? 0);
if ($classId > 0) {
    require_permission('academic.classes.update');
} else {
    require_permission('academic.classes.create');
}

$academicModel = new AcademicModel();
$editingClass = $classId > 0 ? $academicModel->findClass($classId) : null;
$lookups = $academicModel->classLookups();

$module = 'classes';
$adminTitle = $editingClass ? 'Học vụ - Sửa lớp học' : 'Học vụ - Thêm lớp học';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-3xl px-4 sm:px-6">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2><?= $editingClass ? 'Chỉnh sửa lớp học' : 'Thêm lớp học'; ?></h2>
            <form class="grid gap-3" method="post" action="/api/classes/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingClass['id'] ?? 0); ?>">
                <label>Khóa học
                    <select name="course_id" required>
                        <option value="">-- Chọn khóa học --</option>
                        <?php foreach ($lookups['courses'] as $course): ?>
                            <option value="<?= (int) $course['id']; ?>" <?= (int) ($editingClass['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : ''; ?>><?= e((string) $course['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Tên lớp<input type="text" name="class_name" value="<?= e((string) ($editingClass['class_name'] ?? '')); ?>" required></label>
                <label>Giáo viên
                    <select name="teacher_id" required>
                        <option value="">-- Chọn giáo viên --</option>
                        <?php foreach ($lookups['teachers'] as $teacher): ?>
                            <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingClass['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e((string) $teacher['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Ngày bắt đầu<input type="date" name="start_date" value="<?= e((string) ($editingClass['start_date'] ?? '')); ?>"></label>
                <label>Ngày kết thúc<input type="date" name="end_date" value="<?= e((string) ($editingClass['end_date'] ?? '')); ?>"></label>
                <label>Trạng thái
                    <select name="status">
                        <option value="upcoming" <?= (($editingClass['status'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>>Sắp mở</option>
                        <option value="active" <?= (($editingClass['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Đang học</option>
                        <option value="graduated" <?= (($editingClass['status'] ?? '') === 'graduated') ? 'selected' : ''; ?>>Đã tốt nghiệp</option>
                        <option value="cancelled" <?= (($editingClass['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </label>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu lớp học</button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('classes-academic')); ?>">Quay lại</a>
            </form>
        </article>
    </div>
</section>


