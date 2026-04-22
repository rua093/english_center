<?php
require_role(['teacher', 'admin']);

$user = auth_user();
$academicModel = new AcademicModel();
$assignments = $academicModel->listAssignments();
$submissions = $academicModel->listSubmissionsForGrading();
$teacherSchedules = (new UserModel())->teacherUpcomingSchedules((int) ($user['id'] ?? 0));

$success = get_flash('success');
$error = get_flash('error');
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 grid gap-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1>Bảng điều khiển giáo viên</h1>
                <p>Quản lý bài tập, chấm điểm theo lớp và lịch dạy của bạn.</p>
            </div>
            <?php if (can_access_page('assignments-academic')): ?>
                <a class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" href="<?= e(page_url('assignments-academic')); ?>">Quản lý bài tập</a>
            <?php endif; ?>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-emerald-200 bg-emerald-50 text-emerald-700">Hàng chờ chấm điểm</span>
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-amber-200 bg-amber-50 text-amber-700">Điều phối bài tập</span>
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-rose-200 bg-rose-50 text-rose-700">Góc nhìn giáo viên</span>
        </div>

        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <?php if (can_access_page('classrooms-academic')): ?>
                    <a href="<?= e(page_url('classrooms-academic')); ?>"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Lớp học</span><span class="text-xs font-bold">Điều phối buổi dạy</span></a>
                <?php endif; ?>
                <?php if (can_access_page('assignments-academic')): ?>
                    <a href="<?= e(page_url('assignments-academic')); ?>"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Học vụ</span><span class="text-xs font-bold">Quản lý bài tập</span></a>
                <?php endif; ?>
                <?php if (can_access_page('dashboard-teacher')): ?>
                    <a href="<?= e(page_url('dashboard-teacher')); ?>"><span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Tổng quan</span><span class="text-xs font-bold">Bảng điều khiển giáo viên</span></a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <div class="grid gap-4 grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3>Bài tập</h3><p class="text-2xl font-extrabold text-blue-700"><?= count($assignments); ?></p></article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3>Bài nộp cần chấm</h3><p class="text-2xl font-extrabold text-blue-700"><?= count($submissions); ?></p></article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3>Bài nộp đã chấm</h3><p class="text-2xl font-extrabold text-blue-700"><?= count(array_filter($submissions, fn($s) => isset($s['score']) && $s['score'] !== null)); ?></p></article>
        </div>

        <div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Bài tập gần đây</h3>
                <?php if (empty($assignments)): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có bài tập nào.</div>
                <?php else: ?>
                    <ul class="m-0 grid list-none gap-2 p-0">
                        <?php foreach (array_slice($assignments, 0, 5) as $a): ?>
                            <li><?= e((string) $a['title']); ?> - <?= e((string) $a['deadline']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Bài nộp cần chấm</h3>
                <?php $pendingSubmissions = array_values(array_filter($submissions, fn($s) => !isset($s['score']) || $s['score'] === null)); ?>
                <?php if (empty($pendingSubmissions)): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Không còn bài nộp cần chấm.</div>
                <?php else: ?>
                    <ul class="m-0 grid list-none gap-2 p-0">
                        <?php foreach (array_slice($pendingSubmissions, 0, 5) as $sub): ?>
                            <li><?= e((string) ($sub['full_name'] ?? ($sub['student_name'] ?? ''))); ?> - <?= e((string) $sub['assignment_title']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Lịch dạy 7 ngày tới</h3>
                <?php if (empty($teacherSchedules)): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lịch dạy trong 7 ngày tới.</div>
                <?php else: ?>
                    <ul class="m-0 grid list-none gap-2 p-0">
                        <?php foreach ($teacherSchedules as $schedule): ?>
                            <li>
                                <strong><?= e((string) $schedule['class_name']); ?></strong>
                                <small>#<?= (int) ($schedule['schedule_id'] ?? 0); ?> | <?= e((string) $schedule['study_date']); ?> | <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?> | <?= e((string) $schedule['room_name']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <hr class="my-4 border-0 border-t border-slate-200">
                <h4>Yêu cầu nghỉ/dời lịch dạy</h4>
                <form class="grid gap-3" method="post" action="/api/teachers/request-leave">
                    <?= csrf_input(); ?>
                    <label>
                        ID lịch dạy
                        <input type="number" name="schedule_id" min="1" required>
                    </label>
                    <label>
                        Ngày đề xuất dời lịch
                        <input type="date" name="new_date" required>
                    </label>
                    <label>
                        Lý do
                        <input type="text" name="reason" placeholder="Ví dụ: Công tác đột xuất" required>
                    </label>
                    <button class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-1.5 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" type="submit">Gửi yêu cầu phê duyệt</button>
                </form>
            </article>
        </div>
    </div>
</section>


