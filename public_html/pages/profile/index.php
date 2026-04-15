<?php
require_login();

$user = auth_user();
$homeWidgets = (new UserModel())->homeWidgetData((int) ($user['id'] ?? 0), (string) ($user['role'] ?? ''));
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 grid gap-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1>Hồ sơ cá nhân</h1>
                <p>Xin chào <?= e((string) ($user['full_name'] ?? '')); ?>. Đây là khu vực theo dõi nhanh tiến độ và lịch cá nhân.</p>
            </div>
            <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-blue-700 transition hover:-translate-y-0.5 hover:bg-slate-100" href="<?= e(page_url('home')); ?>">Về trang chủ</a>
        </div>

        <?php if (($user['role'] ?? '') === 'student' && is_array($studentProgress)): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Tiến độ học tập</h3>
                <p>Đã hoàn thành <strong><?= (int) ($studentProgress['completed_lessons'] ?? 0); ?></strong> / <strong><?= (int) ($studentProgress['total_lessons'] ?? 0); ?></strong> buổi học.</p>
                <progress class="my-2 h-2.5 w-full appearance-none overflow-hidden rounded-full border-0 bg-slate-200" max="100" value="<?= (int) ($studentProgress['progress_percent'] ?? 0); ?>"></progress>
                <p><strong><?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%</strong> lộ trình đã hoàn thành.</p>
            </article>
        <?php endif; ?>

        <?php if (($user['role'] ?? '') === 'teacher'): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3>Lịch dạy 7 ngày tới</h3>
                <?php if (empty($teacherSchedules)): ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lịch dạy trong 7 ngày tới.</div>
                <?php else: ?>
                    <ul class="m-0 grid list-none gap-2 p-0">
                        <?php foreach ($teacherSchedules as $schedule): ?>
                            <li class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <strong><?= e((string) $schedule['class_name']); ?></strong>
                                <small><?= e((string) $schedule['study_date']); ?> | <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?> | <?= e((string) $schedule['room_name']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        <?php endif; ?>
    </div>
</section>


