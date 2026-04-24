<?php
require_login();

$authUser = auth_user() ?? [];
$academicModel = new AcademicModel();
$profileUser = (int) ($authUser['id'] ?? 0) > 0
    ? ($academicModel->findActiveUser((int) $authUser['id']) ?? [])
    : [];

$homeWidgets = (new UserModel())->homeWidgetData(
    (int) ($authUser['id'] ?? 0),
    (string) ($authUser['role'] ?? ($profileUser['role_name'] ?? ''))
);
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];

$username = (string) ($profileUser['username'] ?? $authUser['username'] ?? '');
$fullName = (string) ($profileUser['full_name'] ?? $authUser['full_name'] ?? '');
$role = (string) ($profileUser['role_name'] ?? $authUser['role'] ?? '');
$email = (string) ($profileUser['email'] ?? '');
$phone = (string) ($profileUser['phone'] ?? '');
$status = (string) ($profileUser['status'] ?? '');
$createdAt = isset($profileUser['created_at']) && $profileUser['created_at'] !== null
    ? date('d/m/Y', strtotime((string) $profileUser['created_at']))
    : '';
$studentSubjectCount = (int) ($studentProgress['subject_count'] ?? 0);
$studentAttendancePercent = (int) ($studentProgress['attendance_percent'] ?? 0);
$studentProgressPercent = (int) ($studentProgress['progress_percent'] ?? 0);
$studentCompletedLessons = (int) ($studentProgress['completed_lessons'] ?? 0);
$studentTotalLessons = (int) ($studentProgress['total_lessons'] ?? 0);
$studentProgramScore = trim((string) (($profileUser['role_profile']['student_target_score'] ?? '') ?: ($profileUser['student_target_score'] ?? '')));
$studentProgramScoreLabel = $studentProgramScore !== '' ? $studentProgramScore : 'Chưa cập nhật';

$roleDisplay = match($role) {
    'teacher' => 'Giảng viên',
    'admin' => 'Quản trị viên',
    default => 'Học viên',
};

$avatarUrl = trim((string) ($profileUser['avatar'] ?? ''));
if ($avatarUrl === '') {
    $displayNameForAvatar = trim($fullName !== '' ? $fullName : $username);
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($displayNameForAvatar !== '' ? $displayNameForAvatar : 'User') . '&background=10b981&color=fff&size=256&bold=true';
} else if (function_exists('normalize_public_file_url')) {
    $avatarUrl = normalize_public_file_url($avatarUrl);
}

$success = get_flash('success');
$error = get_flash('error');
?>

<style>
    .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5); }
    .nav-tab.active { background: #e11d48; color: white; box-shadow: 0 4px 15px rgba(225, 29, 72, 0.25); }
    .nav-tab.inactive { background: transparent; color: #64748b; }
    .nav-tab.inactive:hover { background: #f1f5f9; color: #e11d48; }
    .focus-emerald:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
    /* Anim cho Modal */
    .modal-overlay { transition: opacity 0.3s ease; }
    .modal-content { transition: transform 0.3s ease, opacity 0.3s ease; }
</style>

<section class="min-h-screen bg-slate-50 pb-16">
    <div class="h-[200px] w-full bg-gradient-to-r from-slate-900 via-emerald-900 to-rose-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(#ffffff 2px, transparent 2px); background-size: 20px 20px;"></div>
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-emerald-500/20 rounded-full blur-[60px]"></div>
        <div class="absolute bottom-0 left-0 w-full h-20 bg-gradient-to-t from-slate-50 to-transparent"></div>
        
        <div class="absolute top-4 right-4 lg:right-8 z-20">
            <a class="group flex items-center gap-2 rounded-lg bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 text-xs font-bold text-white shadow-sm transition-all hover:bg-white hover:text-emerald-700" href="/">
                <i class="fa-solid fa-house transition-transform group-hover:-translate-y-0.5"></i> Về trang chủ
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 relative z-10 -mt-20">
        <?php if ($success || $error): ?>
            <div class="mb-6 space-y-3" data-aos="fade-up">
                <?php if ($success): ?>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm">
                        <?= e($success); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 shadow-sm">
                        <?= e($error); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            
            <aside class="lg:col-span-4 space-y-5 lg:sticky lg:top-6" data-aos="fade-right">
                <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/50 text-center relative overflow-hidden">
                    
                    <?php if($status === 'active'): ?>
                        <div class="absolute top-4 right-4 flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border border-emerald-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Hoạt động
                        </div>
                    <?php endif; ?>

                    <div class="relative mx-auto mt-2 h-24 w-24 rounded-full border-[4px] border-white shadow-md bg-slate-100 overflow-hidden">
                        <img id="sidebarAvatar" src="<?= e($avatarUrl) ?>" alt="Avatar" class="h-full w-full rounded-full object-cover" />
                    </div>

                    <button onclick="openAvatarModal()" class="mt-4 inline-flex items-center gap-2 bg-slate-50 hover:bg-emerald-50 text-slate-500 hover:text-emerald-600 text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-xl transition-colors border border-slate-200 hover:border-emerald-200 shadow-sm">
                        <i class="fa-solid fa-camera"></i> Đổi ảnh đại diện
                    </button>

                    <div class="mt-5">
                        <h1 class="text-xl font-black text-slate-800 tracking-tight"><?= e($fullName) ?></h1>
                        <p class="text-[11px] font-bold text-slate-400">@<?= e($username) ?></p>
                        <div class="mt-3">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rose-600 border border-rose-100">
                                <?= $role === 'teacher' ? '<i class="fa-solid fa-chalkboard-user"></i>' : '<i class="fa-solid fa-laptop-code"></i>' ?>
                                <?= e($roleDisplay) ?>
                            </span>
                        </div>
                    </div>

                    <hr class="my-5 border-slate-100" />

                    <div class="space-y-3 text-left">
                        <div class="flex items-center gap-3 text-xs font-medium text-slate-600 group">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors"><i class="fa-solid fa-envelope"></i></div>
                            <div class="truncate">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Email</p>
                                <span class="font-bold text-slate-700"><?= e($email) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-medium text-slate-600 group">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Điện thoại</p>
                                <span class="font-bold text-slate-700"><?= e($phone) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-medium text-slate-600 group">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors"><i class="fa-solid fa-calendar-check"></i></div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Ngày tham gia</p>
                                <span class="font-bold text-slate-700"><?= e($createdAt) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="lg:col-span-8 space-y-5" data-aos="fade-up" data-aos-delay="100">
                
                <div class="glass-panel p-1.5 rounded-xl flex gap-1 w-full md:w-max shadow-sm">
                    <button onclick="switchTab('overview')" id="tab-overview" class="nav-tab active flex-1 md:flex-none px-5 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-chart-pie"></i> Tổng quan
                    </button>
                    <button onclick="switchTab('settings')" id="tab-settings" class="nav-tab inactive flex-1 md:flex-none px-5 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-pen"></i> Cập nhật hồ sơ
                    </button>
                </div>

                <div id="content-overview" class="space-y-5 block animate-fade-in">
                    <?php if ($role === 'student'): ?>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center hover:-translate-y-1 transition-transform">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 mb-2"><i class="fa-solid fa-book-open text-sm"></i></div>
                                <p class="text-xl font-black text-slate-800"><?= (int) $studentSubjectCount; ?></p>
                                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Môn học</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center hover:-translate-y-1 transition-transform">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 mb-2"><i class="fa-solid fa-check-double text-sm"></i></div>
                                <p class="text-xl font-black text-slate-800"><?= (int) $studentAttendancePercent; ?>%</p>
                                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Chuyên cần</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center hover:-translate-y-1 transition-transform">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-500 mb-2"><i class="fa-solid fa-star text-sm"></i></div>
                                <p class="text-lg font-black text-slate-800 leading-tight"><?= e($studentProgramScoreLabel); ?></p>
                                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Điểm chương trình</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center hover:-translate-y-1 transition-transform">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-500 mb-2"><i class="fa-solid fa-award text-sm"></i></div>
                                <p class="text-xl font-black text-slate-800"><?= (int) $studentProgressPercent; ?>%</p>
                                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Tiến độ khóa học</p>
                            </div>
                        </div>

                        <?php if(is_array($studentProgress)): ?>
                        <article class="relative overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 md:p-8 shadow-sm">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-gradient-to-br from-emerald-50 to-lime-50 opacity-50 blur-xl"></div>
                            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="flex-1">
                                    <h3 class="flex items-center gap-3 text-lg font-black text-slate-800">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-200"><i class="fa-solid fa-route text-sm"></i></div>
                                        Tiến độ khóa học
                                    </h3>
                                    <p class="mt-2 text-xs text-slate-500 font-medium">Tiếp tục duy trì ngọn lửa học tập này nhé!</p>
                                    <div class="mt-4 flex items-baseline gap-2 border-t border-slate-50 pt-4">
                                        <span class="text-3xl font-black text-emerald-600 tracking-tighter"><?= (int) $studentCompletedLessons; ?></span>
                                        <span class="text-xs font-bold text-slate-400 uppercase">/ <?= (int) $studentTotalLessons; ?> buổi</span>
                                    </div>
                                </div>
                                <div class="w-full md:w-64 bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Hoàn thành</span>
                                        <span class="text-rose-600 font-black text-lg"><?= (int) $studentProgressPercent; ?>%</span>
                                    </div>
                                    <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200 shadow-inner">
                                        <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-red-500 transition-all duration-1000 relative" style="width: <?= (int) $studentProgressPercent; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($role === 'teacher'): ?>
                        <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                            <h3 class="flex items-center gap-3 text-lg font-black text-slate-800 mb-6">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500 text-white shadow-md shadow-rose-200"><i class="fa-solid fa-calendar-day text-sm"></i></div>
                                Lịch dạy 7 ngày tới
                            </h3>
                            <?php if (empty($teacherSchedules)): ?>
                                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-100 bg-slate-50 py-12 text-center">
                                    <div class="mb-3 rounded-full bg-white p-4 shadow-sm text-slate-300"><i class="fa-regular fa-calendar-xmark text-3xl"></i></div>
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Lịch trống</p>
                                </div>
                            <?php else: ?>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <?php foreach ($teacherSchedules as $schedule): ?>
                                        <div class="group relative rounded-2xl border border-slate-100 bg-slate-50 p-4 transition-all hover:bg-white hover:shadow-md hover:border-emerald-200 cursor-pointer">
                                            <div class="mb-3 flex items-start justify-between">
                                                <div class="rounded-lg bg-emerald-100 px-2 py-1 text-[9px] font-black uppercase text-emerald-700"><?= e((string) $schedule['room_name']); ?></div>
                                                <span class="text-[10px] font-bold text-slate-500 bg-white px-2 py-1 rounded-lg shadow-sm border border-slate-100"><?= e((string) $schedule['study_date']); ?></span>
                                            </div>
                                            <h4 class="text-sm font-black text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-1"><?= e((string) $schedule['class_name']); ?></h4>
                                            <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center gap-2 text-xs font-bold text-slate-500">
                                                <i class="fa-regular fa-clock text-slate-400"></i> <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endif; ?>
                </div>

                <div id="content-settings" class="hidden animate-fade-in">
                    <article class="rounded-3xl border border-slate-100 bg-white p-6 md:p-8 shadow-sm">
                        <div class="mb-6 border-b border-slate-50 pb-4">
                            <h2 class="text-xl font-black text-slate-800">Thông tin cá nhân</h2>
                        </div>

                        <form id="profileUpdateForm" action="/api/index.php?resource=users&method=update" method="POST" class="space-y-5">
                            <?= csrf_input(); ?>
                            <input type="hidden" name="update_mode" value="profile">
                            <div class="grid md:grid-cols-2 gap-5">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email liên hệ *</label>
                                    <div class="relative">
                                        <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="email" name="email" value="<?= e($email) ?>" required class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white text-slate-800 text-sm font-bold border border-slate-200 outline-none focus-emerald transition-all">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Số điện thoại *</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="tel" name="phone" value="<?= e($phone) ?>" required class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white text-slate-800 text-sm font-bold border border-slate-200 outline-none focus-emerald transition-all">
                                    </div>
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-5">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tên đăng nhập</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-user-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                                        <input type="text" value="<?= e($username) ?>" readonly class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-500 text-sm font-bold border border-slate-100 cursor-not-allowed">
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Họ và tên</label>
                                    <div class="relative">
                                        <i class="fa-regular fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="text" value="<?= e($fullName) ?>" readonly class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 text-slate-500 text-sm font-bold border border-slate-100 cursor-not-allowed">
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4 flex gap-3">
                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-black px-6 py-2.5 rounded-xl shadow-md transition-all text-sm flex items-center gap-2">
                                    <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                                </button>
                            </div>
                        </form>

                        <div class="mt-8 pt-6 border-t border-slate-100">
                            <h3 class="text-sm font-black text-rose-600 mb-1"><i class="fa-solid fa-shield-halved"></i> Bảo mật tài khoản</h3>
                            <a href="/change-password" class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 text-xs font-bold px-4 py-2 rounded-lg mt-3 hover:border-rose-500 hover:text-rose-600 transition-colors">
                                Cập nhật mật khẩu mới <i class="fa-solid fa-arrow-right-long"></i>
                            </a>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </div>
</section>

<div id="avatarModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 modal-overlay opacity-0">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 modal-content" id="avatarModalContent">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Đổi ảnh đại diện</h3>
            <button onclick="closeAvatarModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="avatarUpdateForm" action="/api/index.php?resource=users&method=update" method="POST" enctype="multipart/form-data" class="flex flex-col items-center">
                <?= csrf_input(); ?>
                <input type="hidden" name="update_mode" value="avatar">
                <div class="relative h-32 w-32 rounded-full border-4 border-slate-100 mb-6 overflow-hidden bg-slate-50 shadow-inner">
                    <img id="modalAvatarPreview" src="<?= e($avatarUrl) ?>" class="w-full h-full object-cover">
                </div>
                
                <div class="w-full">
                    <label class="group relative w-full border-2 border-dashed border-slate-200 rounded-2xl p-4 flex flex-col items-center justify-center hover:border-emerald-400 hover:bg-emerald-50 transition-colors cursor-pointer bg-slate-50">
                        <input type="file" name="avatar" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-emerald-500 mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-600">Chọn ảnh mới từ máy</p>
                    </label>
                </div>
                
                <button id="avatarSaveButton" type="submit" disabled class="mt-6 w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-3.5 rounded-xl shadow-md transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu hình ảnh
                </button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../notification/confirm_modal.php'; ?>

<script>
    // Xử lý Tabs
    function switchTab(tabName) {
        document.getElementById('tab-overview').className = 'nav-tab flex-1 md:flex-none px-5 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 ' + (tabName === 'overview' ? 'active' : 'inactive');
        document.getElementById('tab-settings').className = 'nav-tab flex-1 md:flex-none px-5 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 ' + (tabName === 'settings' ? 'active' : 'inactive');
        
        document.getElementById('content-overview').style.display = tabName === 'overview' ? 'block' : 'none';
        document.getElementById('content-settings').style.display = tabName === 'settings' ? 'block' : 'none';
    }

    // Xử lý Modal Avatar
    function openAvatarModal() {
        const modal = document.getElementById('avatarModal');
        const content = document.getElementById('avatarModalContent');
        const saveButton = document.getElementById('avatarSaveButton');
        if (saveButton) {
            saveButton.disabled = true;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Trigger animation
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        });
    }

    function closeAvatarModal() {
        const modal = document.getElementById('avatarModal');
        const content = document.getElementById('avatarModalContent');
        
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300); // Đợi CSS transition chạy xong
    }

    // Xem trước ảnh khi vừa chọn file
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // Thay đổi ảnh trong Modal
                document.getElementById('modalAvatarPreview').src = e.target.result;
                
                // Thay đổi luôn ảnh ngoài màn hình Sidebar để người dùng xem thử
                document.getElementById('sidebarAvatar').src = e.target.result;

                const saveButton = document.getElementById('avatarSaveButton');
                if (saveButton) {
                    saveButton.disabled = false;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    const profileUpdateForm = document.getElementById('profileUpdateForm');
    if (profileUpdateForm) {
        profileUpdateForm.addEventListener('submit', function(event) {
            event.preventDefault();
            showConfirm('success', 'Cập nhật hồ sơ?', 'Bạn có chắc muốn lưu các thay đổi thông tin liên hệ này không?', () => profileUpdateForm.submit());
        });
    }

    const avatarUpdateForm = document.getElementById('avatarUpdateForm');
    if (avatarUpdateForm) {
        avatarUpdateForm.addEventListener('submit', function(event) {
            event.preventDefault();
            showConfirm('success', 'Cập nhật ảnh đại diện?', 'Bạn có chắc muốn lưu ảnh đại diện mới này không?', () => avatarUpdateForm.submit());
        });
    }
</script>