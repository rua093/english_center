<?php
require_role(['student', 'admin']);

$user = auth_user();
$model = new UserModel();
$dashboard = $model->studentDashboard((int) ($user['id'] ?? 0));
$submissionNotice = get_flash('submission_notice');
$success = get_flash('success');
$error = get_flash('error');
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 grid gap-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1>Bảng điều khiển học viên</h1>
                <p>Xin chào <?= e($user['full_name']); ?> | Theo dõi học tập và học phí theo thời gian thực.</p>
            </div>
            <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-blue-700 transition hover:bg-slate-100" href="/?page=logout">Đăng xuất</a>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-emerald-200 bg-emerald-50 text-emerald-700">Lộ trình cá nhân</span>
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-amber-200 bg-amber-50 text-amber-700">Tải lên bài làm</span>
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-rose-200 bg-rose-50 text-rose-700">Học phí thời gian thực</span>
        </div>

        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <?php if (can_access_page('academic-portfolios')): ?>
                    <a href="/?page=academic-portfolios"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Portfolio</span><span class="text-xs font-bold">Quản lý học liệu</span></a>
                <?php endif; ?>
                <?php if (can_access_page('student-dashboard')): ?>
                    <a href="/?page=student-dashboard"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Học tập</span><span class="text-xs font-bold">Tiến độ học tập</span></a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($submissionNotice)): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($submissionNotice); ?></div>
        <?php endif; ?>

        <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Chuyên cần</h3>
                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><span>Đi học</span><strong><?= (int) ($dashboard['attendance_summary']['present_count'] ?? 0); ?></strong></div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><span>Đi muộn</span><strong><?= (int) ($dashboard['attendance_summary']['late_count'] ?? 0); ?></strong></div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><span>Vắng</span><strong><?= (int) ($dashboard['attendance_summary']['absent_count'] ?? 0); ?></strong></div>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Học phí hiện tại</h3>
                <?php if (!empty($dashboard['tuition'])): ?>
                    <p>Tổng học phí: <strong><?= format_money((float) $dashboard['tuition']['total_amount']); ?></strong></p>
                    <p>Đã đóng: <strong><?= format_money((float) $dashboard['tuition']['amount_paid']); ?></strong></p>
                    <p>Trạng thái: <strong><?= e((string) $dashboard['tuition']['status']); ?></strong></p>
                    <p>Chế độ đóng: <strong><?= e((string) $dashboard['tuition']['payment_plan']); ?></strong></p>
                <?php else: ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có hóa đơn học phí.</div>
                <?php endif; ?>
            </article>
        </div>

        <div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Lịch học sắp tới</h3>
                <?php if (empty($dashboard['upcoming_schedules'])): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lịch học sắp tới.</div>
                <?php else: ?>
                    <ul class="m-0 grid list-none gap-2 p-0">
                        <?php foreach ($dashboard['upcoming_schedules'] as $schedule): ?>
                            <li>
                                <strong><?= e((string) $schedule['class_name']); ?></strong><br>
                                <?= e((string) $schedule['study_date']); ?> |
                                <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?><br>
                                <?= e((string) ($schedule['room_name'] ?? 'N/A')); ?> |
                                GV: <?= e((string) $schedule['teacher_name']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Bài tập và kết quả</h3>
                <?php if (empty($dashboard['assignments'])): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có bài tập nào.</div>
                <?php else: ?>
                    <ul class="m-0 grid list-none gap-2 p-0">
                        <?php foreach ($dashboard['assignments'] as $assignment): ?>
                            <li>
                                <strong><?= e((string) $assignment['title']); ?></strong><br>
                                Hạn nộp: <?= e((string) $assignment['deadline']); ?><br>
                                <?php if (!empty($assignment['submitted_at'])): ?>
                                    Đã nộp: <?= e((string) $assignment['submitted_at']); ?> |
                                    Điểm: <?= e((string) ($assignment['score'] ?? 'Chưa chấm')); ?>
                                <?php else: ?>
                                    Chưa nộp bài
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <hr class="my-4 border-0 border-t border-slate-200">
                <h4>Nộp bài tập</h4>
                <form class="grid gap-3" method="post" action="/api/assignments/submit" enctype="multipart/form-data">
                    <?= csrf_input(); ?>
                    <label>
                        ID bài tập
                        <input type="number" name="assignment_id" min="1" required>
                    </label>
                    <label>
                        File bài làm (tải lên)
                        <input type="file" name="submission_file" accept=".pdf,.doc,.docx,.jpg,.png">
                    </label>
                    <label>
                        Hoặc đường dẫn file bài làm
                        <input type="text" name="file_url" placeholder="/assets/uploads/my-homework.docx">
                    </label>
                    <button class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-1.5 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" type="submit">Gửi bài</button>
                </form>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Thông báo mới</h3>
                <?php if (empty($dashboard['notifications'])): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Không có thông báo mới.</div>
                <?php else: ?>
                    <ul class="m-0 grid list-none gap-2 p-0">
                        <?php foreach ($dashboard['notifications'] as $notification): ?>
                            <li>
                                <strong><?= e((string) $notification['title']); ?></strong><br>
                                <?= e((string) $notification['message']); ?><br>
                                <small><?= e((string) $notification['created_at']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($dashboard['tuition'])): ?>
                    <hr class="my-4 border-0 border-t border-slate-200">
                    <h4>Cập nhật đóng học phí</h4>
                    <form class="grid gap-3" method="post" action="/api/tuitions/update">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="tuition_id" value="<?= (int) $dashboard['tuition']['id']; ?>">
                        <label>
                            Số tiền đóng thêm
                            <input type="number" step="1000" min="1" name="amount" required>
                        </label>
                        <button class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-1.5 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" type="submit">Xác nhận đóng phí</button>
                    </form>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>


