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
$adminTitle = $editingClass ? t('admin.class_edit.title_edit') : t('admin.class_edit.title_add');
?>
<div class="grid gap-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= e($editingClass ? t('admin.class_edit.heading_edit') : t('admin.class_edit.heading_add')); ?></h2>
        <form class="grid gap-3" method="post" action="/api/classes/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingClass['id'] ?? 0); ?>">
                <label><?= e(t('admin.class_edit.course')); ?>
                    <select name="course_id" required>
                        <option value=""><?= e(t('admin.class_edit.choose_course')); ?></option>
                        <?php foreach ($lookups['courses'] as $course): ?>
                            <option value="<?= (int) $course['id']; ?>" <?= (int) ($editingClass['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : ''; ?>><?= e((string) $course['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= e(t('admin.class_edit.class_name')); ?><input type="text" name="class_name" value="<?= e((string) ($editingClass['class_name'] ?? '')); ?>" required></label>
                <label><?= e(t('admin.class_edit.teacher')); ?>
                    <select name="teacher_id" required>
                        <option value=""><?= e(t('admin.class_edit.choose_teacher')); ?></option>
                        <?php foreach ($lookups['teachers'] as $teacher): ?>
                            <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingClass['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e(teacher_dropdown_label($teacher)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= e(t('admin.class_edit.start_date')); ?><input type="date" name="start_date" value="<?= e((string) ($editingClass['start_date'] ?? '')); ?>"></label>
                <label><?= e(t('admin.class_edit.end_date')); ?><input type="date" name="end_date" value="<?= e((string) ($editingClass['end_date'] ?? '')); ?>"></label>
                <label><?= e(t('admin.class_edit.status')); ?>
                    <select name="status">
                        <option value="upcoming" <?= (($editingClass['status'] ?? 'upcoming') === 'upcoming') ? 'selected' : ''; ?>><?= e(t('admin.class_edit.status_upcoming')); ?></option>
                        <option value="active" <?= (($editingClass['status'] ?? '') === 'active') ? 'selected' : ''; ?>><?= e(t('admin.class_edit.status_active')); ?></option>
                        <option value="graduated" <?= (($editingClass['status'] ?? '') === 'graduated') ? 'selected' : ''; ?>><?= e(t('admin.class_edit.status_graduated')); ?></option>
                        <option value="cancelled" <?= (($editingClass['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>><?= e(t('admin.class_edit.status_cancelled')); ?></option>
                    </select>
                </label>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.class_edit.save')); ?></button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('classes-academic')); ?>"><?= e(t('admin.common.back')); ?></a>
        </form>
    </article>
</div>


