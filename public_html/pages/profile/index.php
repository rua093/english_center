<?php
require_login();

$user = auth_user();
$homeWidgets = (new UserModel())->homeWidgetData((int) ($user['id'] ?? 0), (string) ($user['role'] ?? ''));
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];

// Chuẩn bị dữ liệu hiển thị (Có fallback nếu DB chưa có)
$fullName = (string) ($user['full_name'] ?? 'Nguyễn Duy Bảo');
$role = (string) ($user['role'] ?? 'student');
$email = (string) ($user['email'] ?? 'contact@example.com');
$phone = (string) ($user['phone'] ?? '+84 123 456 789');
$address = (string) ($user['address'] ?? 'Dĩ An, Bình Dương');

// Dịch Role sang tiếng Việt
$roleDisplay = match($role) {
    'teacher' => 'Giảng viên',
    'admin' => 'Quản trị viên',
    default => 'Học viên IT',
};

// Tạo Avatar mặc định từ tên nếu user chưa upload ảnh
$avatarUrl = $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=0D8ABC&color=fff&size=256&bold=true';
?>

<section class="min-h-screen bg-[#f1f5f9] pb-12">
    <div class="h-64 w-full bg-gradient-to-r from-blue-900 via-indigo-800 to-blue-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
        
        <div class="absolute top-6 right-6 lg:right-10">
            <a class="group flex items-center gap-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-white hover:text-blue-900" href="<?= e(page_url('home')); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Về trang chủ
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative -mt-24 grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            
            <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-8">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl text-center">
                    <div class="relative mx-auto -mt-16 h-32 w-32 rounded-full border-4 border-white shadow-lg bg-white">
                        <img src="<?= e($avatarUrl) ?>" alt="Avatar" class="h-full w-full rounded-full object-cover" />
                        <button class="absolute bottom-1 right-1 flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white shadow hover:bg-blue-700 transition" title="Đổi ảnh đại diện">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </button>
                    </div>

                    <div class="mt-4">
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight"><?= e($fullName) ?></h1>
                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-700 border border-blue-100">
                            <?= $role === 'teacher' ? '<i class="fa-solid fa-chalkboard-user"></i>' : '<i class="fa-solid fa-laptop-code"></i>' ?>
                            <?= $roleDisplay ?>
                        </span>
                    </div>

                    <hr class="my-6 border-slate-100" />

                    <div class="space-y-4 text-left">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Thông tin liên hệ</h3>
                        
                        <div class="flex items-center gap-3 text-sm font-medium text-slate-600">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <span class="truncate"><?= e($email) ?></span>
                        </div>
                        
                        <div class="flex items-center gap-3 text-sm font-medium text-slate-600">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <span><?= e($phone) ?></span>
                        </div>

                        <div class="flex items-center gap-3 text-sm font-medium text-slate-600">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <span><?= e($address) ?></span>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button class="flex-1 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-blue-600 transition-colors">
                            Chỉnh sửa
                        </button>
                        <button class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-colors" title="Cài đặt bảo mật">
                            <i class="fa-solid fa-gear"></i>
                        </button>
                    </div>
                </div>
            </aside>

            <div class="lg:col-span-8 space-y-6 mt-16 lg:mt-0">
                
                <div class="hidden lg:block mb-8">
                    <h2 class="text-3xl font-black text-white drop-shadow-md">Hồ sơ cá nhân</h2>
                    <p class="text-blue-100 font-medium mt-1 drop-shadow">Quản lý thông tin và tiến trình học tập của bạn.</p>
                </div>

                <?php if (($user['role'] ?? '') === 'student'): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600 mb-2"><i class="fa-solid fa-book"></i></div>
                            <p class="text-2xl font-black text-slate-800">12</p>
                            <p class="text-[10px] font-bold uppercase text-slate-400">Môn đang học</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-2"><i class="fa-solid fa-check-double"></i></div>
                            <p class="text-2xl font-black text-slate-800">95%</p>
                            <p class="text-[10px] font-bold uppercase text-slate-400">Chuyên cần</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-amber-50 text-amber-600 mb-2"><i class="fa-solid fa-star"></i></div>
                            <p class="text-2xl font-black text-slate-800">8.5</p>
                            <p class="text-[10px] font-bold uppercase text-slate-400">Điểm TB</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600 mb-2"><i class="fa-solid fa-award"></i></div>
                            <p class="text-2xl font-black text-slate-800">N5</p>
                            <p class="text-[10px] font-bold uppercase text-slate-400">Cấp độ</p>
                        </div>
                    </div>

                    <?php if(is_array($studentProgress)): ?>
                    <article class="relative overflow-hidden rounded-[2rem] border border-blue-100 bg-white p-8 shadow-xl">
                        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-gradient-to-br from-blue-50 to-indigo-50 opacity-50"></div>
                        
                        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div class="flex-1">
                                <h3 class="flex items-center gap-3 text-xl font-bold text-slate-800">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-200">
                                        <i class="fa-solid fa-route"></i>
                                    </span>
                                    Tiến độ khóa học
                                </h3>
                                <p class="mt-2 text-sm text-slate-500">Bạn đã đi được một quãng đường tuyệt vời! Hãy tiếp tục phát huy nhé.</p>
                                <div class="mt-4 flex items-baseline gap-2">
                                    <span class="text-4xl font-black text-blue-600 tracking-tighter"><?= (int) ($studentProgress['completed_lessons'] ?? 0); ?></span>
                                    <span class="text-sm font-bold text-slate-400 uppercase">/ <?= (int) ($studentProgress['total_lessons'] ?? 0); ?> buổi học</span>
                                </div>
                            </div>

                            <div class="w-full md:w-72 bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                <div class="mb-3 flex items-center justify-between text-xs font-black uppercase tracking-wider text-slate-500">
                                    <span>Hoàn thành</span>
                                    <span class="text-blue-600 text-lg"><?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%</span>
                                </div>
                                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200 shadow-inner">
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 shadow-md transition-all duration-1000" style="width: <?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (($user['role'] ?? '') === 'teacher'): ?>
                    <article class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="flex items-center gap-3 text-xl font-bold text-slate-800">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-200">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </span>
                                Lịch dạy 7 ngày tới
                            </h3>
                        </div>

                        <?php if (empty($teacherSchedules)): ?>
                            <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 py-16 text-center">
                                <div class="mb-4 rounded-full bg-white p-4 shadow-sm text-slate-300">
                                    <i class="fa-regular fa-calendar-xmark text-4xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Lịch trình trống</p>
                            </div>
                        <?php else: ?>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <?php foreach ($teacherSchedules as $schedule): ?>
                                    <div class="group relative rounded-2xl border border-slate-100 bg-slate-50/50 p-5 transition-all hover:bg-white hover:shadow-xl hover:border-blue-100 cursor-pointer">
                                        <div class="mb-3 flex items-start justify-between">
                                            <div class="rounded-md bg-blue-100 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-blue-700">
                                                <?= e((string) $schedule['room_name']); ?>
                                            </div>
                                            <span class="text-xs font-bold text-slate-400 bg-white px-2 py-1 rounded shadow-sm border border-slate-100">
                                                <?= e((string) $schedule['study_date']); ?>
                                            </span>
                                        </div>
                                        <h4 class="text-lg font-black text-slate-800 group-hover:text-blue-600 transition-colors">
                                            <?= e((string) $schedule['class_name']); ?>
                                        </h4>
                                        <div class="mt-4 flex items-center gap-2 text-sm font-bold text-slate-500">
                                            <i class="fa-regular fa-clock text-slate-400"></i>
                                            <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endif; ?>

            </div>
        </div>

        <footer class="mt-16 text-center pb-8">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">© 2026 B6 Team Platform</p>
        </footer>
    </div>
</section>