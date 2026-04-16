<?php
require_login();

$user = auth_user();
$homeWidgets = (new UserModel())->homeWidgetData((int) ($user['id'] ?? 0), (string) ($user['role'] ?? ''));
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
?>

<section class="min-h-screen bg-[#f8fafc] py-12 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        
        <header class="mb-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                <nav class="mb-2 text-xs font-bold uppercase tracking-widest text-blue-500">Hệ thống quản lý học tập</nav>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                    Hồ sơ <span class="text-blue-600">cá nhân</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium">
                    Chào mừng trở lại, <span class="text-slate-800 font-bold"><?= e((string) ($user['full_name'] ?? 'Bảo')); ?></span>.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a class="group flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-blue-600 hover:text-white hover:border-blue-600" href="<?= e(page_url('home')); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Về trang chủ
                </a>
            </div>
        </header>

        <div class="grid gap-8">
            
            <?php if (($user['role'] ?? '') === 'student' && is_array($studentProgress)): ?>
                <article class="relative overflow-hidden rounded-[2rem] border border-blue-100 bg-white p-8 shadow-2xl shadow-blue-900/5">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-50 opacity-50"></div>
                    
                    <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex-1">
                            <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.846l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.846l-7-3z" /><path d="M6.75 6.75C6.75 7.07 6.48 7.33 6.16 7.33H4.83C4.51 7.33 4.25 7.07 4.25 6.75V5.42C4.25 5.1 4.51 4.84 4.83 4.84H6.16C6.48 4.84 6.75 5.1 6.75 5.42V6.75Z" /></svg>
                                </span>
                                Tiến độ học tập
                            </h3>
                            <p class="mt-4 text-slate-500">Bạn đã đi được một quãng đường tuyệt vời!</p>
                            <div class="mt-2 text-3xl font-black text-blue-600">
                                <?= (int) ($studentProgress['completed_lessons'] ?? 0); ?> <span class="text-sm font-medium text-slate-400">/ <?= (int) ($studentProgress['total_lessons'] ?? 0); ?> buổi học</span>
                            </div>
                        </div>

                        <div class="w-full md:w-64">
                            <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-400">
                                <span>Hoàn thành</span>
                                <span class="text-blue-600"><?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%</span>
                            </div>
                            <div class="h-4 w-full overflow-hidden rounded-full bg-slate-100 p-1 shadow-inner">
                                <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-700 shadow-md transition-all duration-1000" style="width: <?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <?php if (($user['role'] ?? '') === 'teacher'): ?>
                <article class="rounded-[2rem] border border-slate-100 bg-white p-8 shadow-2xl shadow-slate-900/5">
                    <div class="mb-8 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" /></svg>
                            </span>
                            Lịch dạy 7 ngày tới
                        </h3>
                    </div>

                    <?php if (empty($teacherSchedules)): ?>
                        <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-100 bg-slate-50/50 py-16 text-center">
                            <div class="mb-4 rounded-full bg-white p-4 shadow-sm text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="text-sm font-medium text-slate-400 uppercase tracking-widest">Lịch trình trống</p>
                        </div>
                    <?php else: ?>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <?php foreach ($teacherSchedules as $schedule): ?>
                                <div class="group relative rounded-2xl border border-slate-50 bg-slate-50/50 p-5 transition-all hover:bg-white hover:shadow-lg hover:shadow-blue-900/5">
                                    <div class="mb-3 flex items-start justify-between">
                                        <div class="rounded-lg bg-blue-100 px-2.5 py-1 text-[10px] font-black uppercase text-blue-700">
                                            <?= e((string) $schedule['room_name']); ?>
                                        </div>
                                        <span class="text-xs font-bold text-slate-400"><?= e((string) $schedule['study_date']); ?></span>
                                    </div>
                                    <h4 class="text-lg font-black text-slate-800 group-hover:text-blue-600 transition-colors">
                                        <?= e((string) $schedule['class_name']); ?>
                                    </h4>
                                    <div class="mt-4 flex items-center gap-2 text-sm font-medium text-slate-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endif; ?>
        </div>
        
        <footer class="mt-12 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-300">© 2026 B6 Team Platform</p>
        </footer>
    </div>
</section>