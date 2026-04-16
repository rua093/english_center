<?php
$scheduleId = (int) ($_GET['id'] ?? 0);
if ($scheduleId > 0) {
    require_permission('academic.schedules.update');
} else {
    require_permission('academic.schedules.create');
}

$academicModel = new AcademicModel();
$editingSchedule = $scheduleId > 0 ? $academicModel->findSchedule($scheduleId) : null;
$lookups = $academicModel->scheduleLookups();

$module = 'schedules';
$adminTitle = $editingSchedule ? 'Học vụ - Sửa lịch học' : 'Học vụ - Thêm lịch học';
?>
<div class="grid gap-4">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= $editingSchedule ? 'Chỉnh sửa lịch học' : 'Thêm lịch học'; ?></h2>
        <form class="grid gap-3" method="post" action="/api/schedules/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingSchedule['id'] ?? 0); ?>">
                <label>Lớp học
                    <select name="class_id" required>
                        <?php foreach ($lookups['classes'] as $class): ?>
                            <option value="<?= (int) $class['id']; ?>" <?= (int) ($editingSchedule['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : ''; ?>><?= e((string) $class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Phòng học
                    <select name="room_id">
                        <option value="">Online / chưa chọn phòng</option>
                        <?php foreach ($lookups['rooms'] as $room): ?>
                            <option value="<?= (int) $room['id']; ?>" <?= (int) ($editingSchedule['room_id'] ?? 0) === (int) $room['id'] ? 'selected' : ''; ?>><?= e((string) $room['room_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Giáo viên
                    <select name="teacher_id" required>
                        <option value="">-- Chọn giáo viên --</option>
                        <?php foreach ($lookups['teachers'] as $teacher): ?>
                            <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingSchedule['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e((string) $teacher['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Ngày học<input type="date" name="study_date" value="<?= e((string) ($editingSchedule['study_date'] ?? '')); ?>" required></label>
                <label>Giờ bắt đầu<input type="time" name="start_time" value="<?= e((string) ($editingSchedule['start_time'] ?? '')); ?>" required></label>
                <label>Giờ kết thúc<input type="time" name="end_time" value="<?= e((string) ($editingSchedule['end_time'] ?? '')); ?>" required></label>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu lịch học</button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('schedules-academic')); ?>">Quay lại</a>
        </form>
    </article>
</div>


