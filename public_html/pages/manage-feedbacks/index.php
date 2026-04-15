<?php
require_admin_or_staff();
require_permission('feedback.view');

$academicModel = new AcademicModel();
$feedbacks = $academicModel->listFeedbacks();
$students = Database::connection()->query("SELECT u.id, u.full_name FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.role_name = 'student' ORDER BY u.full_name")->fetchAll();
$teachers = Database::connection()->query("SELECT u.id, u.full_name FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.role_name = 'teacher' ORDER BY u.full_name")->fetchAll();
$classes = Database::connection()->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

$module = 'feedbacks';
$adminTitle = 'Quản lý phản hồi';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <?php
        $canCreateFeedback = has_permission('feedback.create');
        $canDeleteFeedback = has_permission('feedback.delete');
        ?>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1>Quản lý đánh giá phản hồi</h1>
                <p>Theo dõi và quản lý phản hồi từ học viên.</p>
            </div>
        </div>

        <?php if ($canCreateFeedback): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Thêm đánh giá</h3>
                <form class="grid gap-3" method="post" action="/api/feedbacks/save">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id" value="">
                    <label>
                        Học viên
                        <select name="student_id" required>
                            <option value="">Chọn học viên</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= (int) $s['id']; ?>"><?= e((string) $s['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Giáo viên (tùy chọn)
                        <select name="teacher_id">
                            <option value="">Không chọn</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= (int) $t['id']; ?>"><?= e((string) $t['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Lớp học
                        <select name="class_id" required>
                            <option value="">Chọn lớp học</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= (int) $c['id']; ?>"><?= e((string) $c['class_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Đánh giá (1-5)
                        <input type="number" name="rating" min="1" max="5" required>
                    </label>
                    <label>
                        Nhận xét
                        <textarea name="comment" rows="4"></textarea>
                    </label>
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu đánh giá</button>
                </form>
            </article>
        <?php endif; ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mt-6">
            <h3>Danh sách đánh giá</h3>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>Học viên</th>
                            <th>Giáo viên</th>
                            <th>Lớp học</th>
                            <th>Đánh giá</th>
                            <th>Nhận xét</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($feedbacks)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có đánh giá nào.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($feedbacks as $fb): ?>
                                <tr>
                                    <td><?= e((string) $fb['student_name']); ?></td>
                                    <td><?= $fb['teacher_name'] ? e((string) $fb['teacher_name']) : '-'; ?></td>
                                    <td><?= e((string) $fb['course_name']); ?></td>
                                    <td><?= (int) $fb['rating']; ?>/5</td>
                                    <td><?= e((string) substr($fb['comment'] ?? '', 0, 50)); ?></td>
                                    <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize border-blue-200 bg-blue-50 text-blue-700"><?= e((string) ($fb['status'] ?? 'reviewed')); ?></span></td>
                                    <td>
                                        <?php if ($canDeleteFeedback): ?>
                                            <form class="inline-block" method="post" action="/api/feedbacks/delete?id=<?= (int) $fb['id']; ?>" onsubmit="return confirm('Có chắc không?')">
                                                <?= csrf_input(); ?>
                                                <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-sm text-slate-500">Không có quyền xóa</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>



