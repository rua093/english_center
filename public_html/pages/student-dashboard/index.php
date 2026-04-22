<?php
require_role(['student', 'admin']);
$user = auth_user();
$studentDashboardActiveTab = 'dashboard-student';

// 3. Dữ liệu mẫu (Giả lập có lịch cho hôm nay và ngày mai)
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$dbEvents = [
    ['id' => 1, 'date' => $today, 'title' => 'Java Web (Spring Boot)', 'time' => '18:00 - 20:30', 'teacher' => 'Thầy Nguyễn Văn A', 'room' => 'Lab 01', 'type' => 'blue'],
    ['id' => 2, 'date' => $tomorrow, 'title' => 'Cấu trúc dữ liệu', 'time' => '08:00 - 11:00', 'teacher' => 'Cô Trần Thị B', 'room' => 'P.202', 'type' => 'emerald'],
    ['id' => 3, 'date' => '2026-04-25', 'title' => 'Tiếng Nhật N5', 'time' => '18:00 - 19:30', 'teacher' => 'Sensei Tanaka', 'room' => 'Online', 'type' => 'rose'],
    ['id' => 4, 'date' => '2026-05-02', 'title' => 'Thiết kế hệ thống', 'time' => '13:00 - 15:00', 'teacher' => 'Thầy Lê C', 'room' => 'Hội trường B', 'type' => 'amber']
];

$upcomingAssignments = [
    [
        'title' => 'Security with JWT',
        'class' => 'Java Spring Boot Advanced',
        'deadline' => '20/05/2026',
        'left' => '2 ngày nữa',
        'progress' => 35,
        'tone' => 'rose',
        'icon' => 'fa-triangle-exclamation',
    ],
    [
        'title' => 'Design REST API',
        'class' => 'Java Spring Boot Advanced',
        'deadline' => '22/05/2026',
        'left' => '4 ngày nữa',
        'progress' => 68,
        'tone' => 'amber',
        'icon' => 'fa-bolt',
    ],
    [
        'title' => 'Cấu trúc dữ liệu - Bài luyện tập',
        'class' => 'Data Structures',
        'deadline' => '23/05/2026',
        'left' => '5 ngày nữa',
        'progress' => 52,
        'tone' => 'blue',
        'icon' => 'fa-pen-to-square',
    ],
];
?>
<<<<<<< HEAD
<<<<<<< HEAD

<section class="relative min-h-screen overflow-hidden bg-[#f8fafc] py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
    <div class="absolute inset-0 z-0 opacity-[0.08] pointer-events-none" style="background-image: radial-gradient(#1e3a8a 2px, transparent 2px); background-size: 30px 30px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-80 bg-gradient-to-b from-blue-100/50 via-cyan-50/20 to-transparent pointer-events-none"></div>
    <div class="absolute -right-24 top-32 z-0 h-72 w-72 rounded-full bg-blue-200/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-28 bottom-20 z-0 h-80 w-80 rounded-full bg-cyan-200/30 blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-[1800px]">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] md:items-start">
            <aside class="self-start md:sticky md:top-24">
                <?php require __DIR__ . '/partials/nav.php'; ?>
            </aside>
            <div class="min-w-0">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.9fr)] 2xl:grid-cols-[minmax(0,1.55fr)_minmax(380px,0.92fr)] md:items-start">
                    <article class="relative overflow-hidden rounded-[2rem] border border-blue-100 bg-gradient-to-br from-white via-blue-50 to-cyan-50 p-5 shadow-2xl transition-all md:p-6">
                    <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-blue-200/40 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-cyan-200/35 blur-3xl"></div>

                    <div class="relative z-10 flex w-full flex-col gap-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-400">Lịch học</p>
                                <h3 id="calendar-title" class="mt-1 text-2xl md:text-3xl font-black text-slate-800 tracking-tight"></h3>
                            </div>
                            <div class="flex gap-1 rounded-2xl border border-white/70 bg-white p-1.5 shadow-sm">
                                <button onclick="changeDate(-1)" class="rounded-xl bg-white px-3 py-2 text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">&larr;</button>
                                <button onclick="resetToToday()" class="rounded-xl bg-white px-4 py-2 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Hôm nay</button>
                                <button onclick="changeDate(1)" class="rounded-xl bg-white px-3 py-2 text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">&rarr;</button>
                            </div>
                        </div>
                        <div class="flex rounded-2xl border border-white/70 bg-white p-1.5 shadow-sm">
                            <button id="btn-view-month" onclick="setView('month')" class="px-5 py-2 text-xs font-black uppercase rounded-xl transition-all duration-300">Tháng</button>
                            <button id="btn-view-week" onclick="setView('week')" class="px-5 py-2 text-xs font-black uppercase rounded-xl transition-all duration-300">Tuần</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-px rounded-[1.75rem] border border-blue-100 bg-white/90 overflow-hidden shadow-[0_10px_30px_rgba(37,99,235,0.08)]">
                        <?php 
                        $weekdays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
                        foreach ($weekdays as $day): ?>
                            <div class="bg-gradient-to-b from-blue-100 via-white to-cyan-100 py-5 text-center text-xs md:text-sm font-black text-blue-700 uppercase tracking-[0.28em] border-b border-blue-200/80 shadow-[inset_0_-1px_0_rgba(255,255,255,0.95)]">
                                <span class="inline-flex items-center justify-center rounded-full bg-white/90 px-3 py-1.5 shadow-sm ring-1 ring-blue-200/90 ring-offset-1 ring-offset-blue-50"><?= $day ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div id="calendar-grid" class="contents"></div>
                    </div>
                    </div>
                    </article>

                    <aside class="space-y-6 self-start md:sticky md:top-24">
                        <article class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                            Sắp diễn ra (48h)
                        </h3>
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded-full uppercase"><?= date('d/m') ?></span>
                    </div>
                    
                    <div id="upcoming-list" class="space-y-4">
                        </div>
                </article>

                        <article class="relative overflow-hidden rounded-3xl border border-rose-100 bg-gradient-to-br from-rose-50 via-white to-amber-50 p-6 shadow-2xl shadow-rose-100/50">
                    <div class="pointer-events-none absolute -top-16 right-0 h-40 w-40 rounded-full bg-rose-200/40 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-12 left-10 h-32 w-32 rounded-full bg-amber-200/40 blur-3xl"></div>

                    <div class="relative z-10 mb-5 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.35em] text-rose-500">Nhắc nhở gấp</p>
                            <h3 class="mt-2 text-xl font-black text-slate-900">Bài tập sắp đến hạn nộp</h3>
                            <p class="mt-1 text-sm text-slate-500">Đừng để quá hạn, các bài dưới đây cần xử lý sớm.</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-600 text-white shadow-lg shadow-rose-200 animate-pulse">
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                    </div>

                    <div class="relative z-10 space-y-4">
                        <div class="rounded-2xl bg-slate-950 px-4 py-4 text-white shadow-lg shadow-slate-900/10">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-rose-300">Còn lại</p>
                                    <p class="mt-1 text-2xl font-black leading-none">3 bài tập</p>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rose-200">48h tới</span>
                            </div>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                <div class="h-2 w-2/3 rounded-full bg-gradient-to-r from-rose-400 via-amber-300 to-cyan-300 animate-pulse"></div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <?php foreach ($upcomingAssignments as $index => $assignment): ?>
                                <div class="group relative overflow-hidden rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                                    <div class="absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100" style="background: linear-gradient(135deg, rgba(255,255,255,0.65), rgba(255,255,255,0));"></div>
                                    <div class="relative z-10 flex items-start gap-4">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-100 text-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-600 shadow-inner">
                                            <i class="fa-solid <?= e($assignment['icon']); ?>"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <h4 class="truncate text-sm font-black text-slate-800"><?= e($assignment['title']); ?></h4>
                                                    <p class="mt-1 text-xs font-medium text-slate-500"><?= e($assignment['class']); ?></p>
                                                </div>
                                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest <?= $assignment['tone'] === 'rose' ? 'bg-rose-100 text-rose-700' : ($assignment['tone'] === 'amber' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') ?>"><?= e($assignment['left']); ?></span>
                                            </div>

                                            <div class="mt-3">
                                                <div class="mb-1 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                                    <span>Hoàn thành</span>
                                                    <span><?= (int) $assignment['progress']; ?>%</span>
                                                </div>
                                                <div class="h-2 rounded-full bg-slate-100">
                                                    <div class="h-2 rounded-full bg-gradient-to-r from-<?= e($assignment['tone']); ?>-500 to-amber-400 shadow-[0_0_20px_rgba(251,146,60,0.25)]" style="width: <?= (int) $assignment['progress']; ?>%"></div>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between gap-3 text-[11px] font-semibold text-slate-500">
                                                <span>Deadline: <strong class="text-slate-700"><?= e($assignment['deadline']); ?></strong></span>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-1 text-slate-500">
                                                    <span class="h-2 w-2 rounded-full bg-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-500 animate-pulse"></span>
                                                    <?= $index === 0 ? 'Ưu tiên cao' : 'Đang theo dõi'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                        </article>

                        <div class="bg-blue-600 rounded-3xl p-6 text-white shadow-lg shadow-blue-200 overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-blue-100 text-xs font-bold uppercase tracking-widest mb-1">Trạng thái học phí</p>
                        <h4 class="text-xl font-black mb-4">Đã hoàn tất 100%</h4>
                        <div class="w-full bg-blue-500 rounded-full h-1.5 mb-2"><div class="bg-white h-1.5 rounded-full" style="width: 100%"></div></div>
                        <p class="text-[10px] text-blue-100 font-medium italic">* Tuyệt vời! Bạn không có nợ đọng học phí.</p>
                    </div>
                    <svg class="absolute -bottom-4 -right-4 w-24 h-24 text-blue-500 opacity-50 transform rotate-12 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                    </aside>
                </div>
=======
<section class="min-h-screen bg-[#f8fafc] py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        
        <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-blue-900 tracking-tight">
                    Bảng điều khiển <span class="text-blue-600">Học viên</span>
                </h1>
                <p class="mt-1 text-slate-500 font-medium">
                    Chào mừng trở lại, <span class="text-blue-700 font-bold"><?= e($user['full_name']); ?></span>! 👋
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a class="flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200" href="<?= e(page_url('logout')); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Đăng xuất
                </a>
            </div>
        </header>
=======
>>>>>>> c6aecb1 (feat: view)

<section class="relative min-h-screen overflow-hidden bg-[#f8fafc] py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
    <div class="absolute inset-0 z-0 opacity-[0.08] pointer-events-none" style="background-image: radial-gradient(#1e3a8a 2px, transparent 2px); background-size: 30px 30px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-80 bg-gradient-to-b from-blue-100/50 via-cyan-50/20 to-transparent pointer-events-none"></div>
    <div class="absolute -right-24 top-32 z-0 h-72 w-72 rounded-full bg-blue-200/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-28 bottom-20 z-0 h-80 w-80 rounded-full bg-cyan-200/30 blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-[1800px]">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
            <aside class="lg:sticky lg:top-24">
                <?php require __DIR__ . '/partials/nav.php'; ?>
            </aside>

            <div class="min-w-0 space-y-8">
                <header class="flex flex-col gap-2">
                    <div>
                        <h1 class="text-3xl font-extrabold text-blue-900 tracking-tight">
                            Hệ thống <span class="text-blue-600">Quản lý Học tập</span>
                        </h1>
                        <p class="mt-1 text-slate-500 font-medium tracking-tight">Chào mừng trở lại, <span class="text-blue-700 font-bold"><?= e($user['full_name']); ?></span></p>
                    </div>
                    <p class="text-sm font-medium text-slate-400">Lịch học, bài tập và các thông báo quan trọng của bạn được gom ở một nơi.</p>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                <article class="relative overflow-hidden rounded-[2rem] border border-blue-100 bg-gradient-to-br from-white via-blue-50 to-cyan-50 p-5 shadow-2xl transition-all md:p-6">
                    <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-blue-200/40 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-cyan-200/35 blur-3xl"></div>

                    <div class="relative z-10 flex w-full flex-col gap-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-400">Lịch học</p>
                                <h3 id="calendar-title" class="mt-1 text-2xl md:text-3xl font-black text-slate-800 tracking-tight"></h3>
                            </div>
                            <div class="flex gap-1 rounded-2xl border border-white/70 bg-white/85 p-1.5 shadow-sm backdrop-blur">
                                <button onclick="changeDate(-1)" class="rounded-xl bg-white px-3 py-2 text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">&larr;</button>
                                <button onclick="resetToToday()" class="rounded-xl bg-white px-4 py-2 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Hôm nay</button>
                                <button onclick="changeDate(1)" class="rounded-xl bg-white px-3 py-2 text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">&rarr;</button>
                            </div>
                        </div>
                        <div class="flex rounded-2xl border border-white/70 bg-white/85 p-1.5 shadow-sm backdrop-blur">
                            <button id="btn-view-month" onclick="setView('month')" class="px-5 py-2 text-xs font-black uppercase rounded-xl transition-all duration-300">Tháng</button>
                            <button id="btn-view-week" onclick="setView('week')" class="px-5 py-2 text-xs font-black uppercase rounded-xl transition-all duration-300">Tuần</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-px rounded-[1.75rem] border border-blue-100 bg-white/80 overflow-hidden shadow-[0_10px_30px_rgba(37,99,235,0.08)] backdrop-blur-sm">
                        <?php 
                        $weekdays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
                        foreach ($weekdays as $day): ?>
                            <div class="bg-gradient-to-b from-blue-100 via-white to-cyan-100 py-5 text-center text-xs md:text-sm font-black text-blue-700 uppercase tracking-[0.28em] border-b border-blue-200/80 shadow-[inset_0_-1px_0_rgba(255,255,255,0.95)]">
                                <span class="inline-flex items-center justify-center rounded-full bg-white/90 px-3 py-1.5 shadow-sm ring-1 ring-blue-200/90 ring-offset-1 ring-offset-blue-50"><?= $day ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div id="calendar-grid" class="contents"></div>
                    </div>
                    </div>
                </article>
                    </div>

                    <div class="space-y-6">
                <article class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                            Sắp diễn ra (48h)
                        </h3>
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded-full uppercase"><?= date('d/m') ?></span>
                    </div>
                    
                    <div id="upcoming-list" class="space-y-4">
                        </div>
                </article>

                <article class="relative overflow-hidden rounded-3xl border border-rose-100 bg-gradient-to-br from-rose-50 via-white to-amber-50 p-6 shadow-2xl shadow-rose-100/50">
                    <div class="pointer-events-none absolute -top-16 right-0 h-40 w-40 rounded-full bg-rose-200/40 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-12 left-10 h-32 w-32 rounded-full bg-amber-200/40 blur-3xl"></div>

                    <div class="relative z-10 mb-5 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.35em] text-rose-500">Nhắc nhở gấp</p>
                            <h3 class="mt-2 text-xl font-black text-slate-900">Bài tập sắp đến hạn nộp</h3>
                            <p class="mt-1 text-sm text-slate-500">Đừng để quá hạn, các bài dưới đây cần xử lý sớm.</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-600 text-white shadow-lg shadow-rose-200 animate-pulse">
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                    </div>

                    <div class="relative z-10 space-y-4">
                        <div class="rounded-2xl bg-slate-950 px-4 py-4 text-white shadow-lg shadow-slate-900/10">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-rose-300">Còn lại</p>
                                    <p class="mt-1 text-2xl font-black leading-none">3 bài tập</p>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rose-200">48h tới</span>
                            </div>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                <div class="h-2 w-2/3 rounded-full bg-gradient-to-r from-rose-400 via-amber-300 to-cyan-300 animate-pulse"></div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <?php foreach ($upcomingAssignments as $index => $assignment): ?>
                                <div class="group relative overflow-hidden rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                                    <div class="absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100" style="background: linear-gradient(135deg, rgba(255,255,255,0.65), rgba(255,255,255,0));"></div>
                                    <div class="relative z-10 flex items-start gap-4">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-100 text-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-600 shadow-inner">
                                            <i class="fa-solid <?= e($assignment['icon']); ?>"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <h4 class="truncate text-sm font-black text-slate-800"><?= e($assignment['title']); ?></h4>
                                                    <p class="mt-1 text-xs font-medium text-slate-500"><?= e($assignment['class']); ?></p>
                                                </div>
                                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest <?= $assignment['tone'] === 'rose' ? 'bg-rose-100 text-rose-700' : ($assignment['tone'] === 'amber' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') ?>"><?= e($assignment['left']); ?></span>
                                            </div>

                                            <div class="mt-3">
                                                <div class="mb-1 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                                    <span>Hoàn thành</span>
                                                    <span><?= (int) $assignment['progress']; ?>%</span>
                                                </div>
                                                <div class="h-2 rounded-full bg-slate-100">
                                                    <div class="h-2 rounded-full bg-gradient-to-r from-<?= e($assignment['tone']); ?>-500 to-amber-400 shadow-[0_0_20px_rgba(251,146,60,0.25)]" style="width: <?= (int) $assignment['progress']; ?>%"></div>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between gap-3 text-[11px] font-semibold text-slate-500">
                                                <span>Deadline: <strong class="text-slate-700"><?= e($assignment['deadline']); ?></strong></span>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-1 text-slate-500">
                                                    <span class="h-2 w-2 rounded-full bg-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-500 animate-pulse"></span>
                                                    <?= $index === 0 ? 'Ưu tiên cao' : 'Đang theo dõi'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>

<<<<<<< HEAD
>>>>>>> 8f0c019 (style: view ..)
=======
                <div class="bg-blue-600 rounded-3xl p-6 text-white shadow-lg shadow-blue-200 overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-blue-100 text-xs font-bold uppercase tracking-widest mb-1">Trạng thái học phí</p>
                        <h4 class="text-xl font-black mb-4">Đã hoàn tất 100%</h4>
                        <div class="w-full bg-blue-500 rounded-full h-1.5 mb-2"><div class="bg-white h-1.5 rounded-full" style="width: 100%"></div></div>
                        <p class="text-[10px] text-blue-100 font-medium italic">* Tuyệt vời! Bạn không có nợ đọng học phí.</p>
                    </div>
                    <svg class="absolute -bottom-4 -right-4 w-24 h-24 text-blue-500 opacity-50 transform rotate-12 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                </div>
                    </div>
                </div>
>>>>>>> c6aecb1 (feat: view)
            </div>
        </div>
    </div>

    <div id="event-tooltip" class="fixed hidden z-[9999] w-72 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200 p-5 pointer-events-none transition-all duration-200 opacity-0 scale-95 translate-y-2">
        <div class="flex items-start gap-3 mb-3">
            <div id="tooltip-color" class="w-1.5 h-10 rounded-full"></div>
            <div>
                <h4 id="tooltip-title" class="font-black text-slate-800 text-base leading-tight"></h4>
                <p id="tooltip-time" class="text-blue-600 text-xs font-bold mt-1"></p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Giảng viên</p>
                <p id="tooltip-teacher" class="text-xs font-bold text-slate-700"></p>
            </div>
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Vị trí</p>
                <p id="tooltip-room" class="text-xs font-bold text-slate-700"></p>
            </div>
        </div>
    </div>
</section>