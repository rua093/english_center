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
$studentCode = trim((string) (($profileUser['role_profile']['student_code'] ?? '') ?: ($profileUser['student_code'] ?? '')));
$teacherCode = trim((string) (($profileUser['role_profile']['teacher_code'] ?? '') ?: ($profileUser['teacher_code'] ?? '')));
$studentFatherName = trim((string) (($profileUser['role_profile']['student_father_name'] ?? '') ?: ($profileUser['student_father_name'] ?? '')));
$studentFatherPhone = trim((string) (($profileUser['role_profile']['student_father_phone'] ?? '') ?: ($profileUser['student_father_phone'] ?? '')));
$studentMotherName = trim((string) (($profileUser['role_profile']['student_mother_name'] ?? '') ?: ($profileUser['student_mother_name'] ?? '')));
$studentMotherPhone = trim((string) (($profileUser['role_profile']['student_mother_phone'] ?? '') ?: ($profileUser['student_mother_phone'] ?? '')));
$studentSchoolName = trim((string) (($profileUser['role_profile']['student_school_name'] ?? '') ?: ($profileUser['student_school_name'] ?? '')));
$studentCurrentGrade = trim((string) (($profileUser['role_profile']['student_current_grade'] ?? '') ?: ($profileUser['student_current_grade'] ?? '')));
$teacherCertificates = is_array($profileUser['role_profile']['teacher_certificates'] ?? null) ? $profileUser['role_profile']['teacher_certificates'] : [];
$teacherCertificatesCount = count($teacherCertificates);
$profileCode = $role === 'student' ? $studentCode : ($role === 'teacher' ? $teacherCode : '');
$profileCodeLabel = $role === 'student' ? t('profile.student_code') : ($role === 'teacher' ? t('profile.teacher_code') : '');
$studentSubjectCount = (int) ($studentProgress['subject_count'] ?? 0);
$studentAttendancePercent = (int) ($studentProgress['attendance_percent'] ?? 0);
$studentProgressPercent = (int) ($studentProgress['progress_percent'] ?? 0);
$studentCompletedLessons = (int) ($studentProgress['completed_lessons'] ?? 0);
$studentTotalLessons = (int) ($studentProgress['total_lessons'] ?? 0);
$studentProgramScore = trim((string) (($profileUser['role_profile']['student_target_score'] ?? '') ?: ($profileUser['student_target_score'] ?? '')));
$studentProgramScoreLabel = $studentProgramScore !== '' ? $studentProgramScore : t('profile.not_updated');
$teacherIntroVideoUrl = trim((string) ($profileUser['role_profile']['teacher_intro_video_url'] ?? ''));
if ($teacherIntroVideoUrl === '' && isset($profileUser['teacher_intro_video_url'])) {
    $teacherIntroVideoUrl = trim((string) $profileUser['teacher_intro_video_url']);
}
$teacherIntroVideoUrl = $teacherIntroVideoUrl !== '' && function_exists('normalize_public_file_url')
    ? normalize_public_file_url($teacherIntroVideoUrl)
    : $teacherIntroVideoUrl;
$openPasswordModal = !empty($_GET['open_password']);
$isTeacher = $role === 'teacher';
$isStudent = $role === 'student';
$teacherVideoMaxBytes = 64 * 1024 * 1024;

$roleDisplay = match($role) {
    'teacher' => t('profile.role_teacher'),
    'admin' => t('profile.role_admin'),
    default => t('profile.role_student'),
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
    .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.6); }
    .nav-tab { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .nav-tab.active { background: #e11d48; color: white; box-shadow: 0 8px 15px -5px rgba(225, 29, 72, 0.4); transform: translateY(-2px); }
    .nav-tab.inactive { background: transparent; color: #64748b; border: 1px solid transparent; }
    .nav-tab.inactive:hover { background: rgba(225, 29, 72, 0.05); color: #e11d48; border-color: rgba(225, 29, 72, 0.1); }
    .input-modern:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); outline: none; }
    
    .modal-overlay { transition: opacity 0.3s ease; }
    .modal-content { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease; }
</style>

<section class="min-h-screen bg-[#f8fafc] pb-24 font-jakarta relative overflow-hidden">
    
    <div class="absolute top-0 left-0 w-full h-[320px] overflow-hidden -z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-100/80 via-emerald-100/60 to-teal-50"></div>
        <div class="absolute -top-32 -right-20 w-[500px] h-[500px] bg-rose-400/30 rounded-full blur-[80px] animate-pulse" style="animation-duration: 8s;"></div>
        <div class="absolute top-10 -left-20 w-[450px] h-[450px] bg-emerald-400/30 rounded-full blur-[80px] animate-pulse" style="animation-duration: 10s;"></div>
        <div class="absolute inset-0 opacity-[0.25]" style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-[#f8fafc] to-transparent"></div>
    </div>

    <div class="relative z-10 pt-6 px-4 sm:px-8 max-w-7xl mx-auto flex justify-end">
        <a class="group inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur-md border border-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-white hover:text-rose-600 hover:shadow-md" href="/">
            <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> <?= e(t('profile.back')); ?>
        </a>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10 mt-6">
        
        <?php if ($success || $error): ?>
            <div class="mb-8 max-w-3xl mx-auto" data-aos="fade-down">
                <?php if ($success): ?>
                    <div class="rounded-2xl border-l-4 border-l-emerald-500 bg-emerald-50/90 backdrop-blur-sm p-4 shadow-sm flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-sm"><i class="fa-solid fa-check"></i></div>
                        <p class="text-sm font-bold text-emerald-800"><?= e($success); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="rounded-2xl border-l-4 border-l-rose-500 bg-rose-50/90 backdrop-blur-sm p-4 shadow-sm flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-sm"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <p class="text-sm font-bold text-rose-800"><?= e($error); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
            
            <aside class="lg:col-span-4 lg:sticky lg:top-8" data-aos="fade-right">
                <div class="glass-card rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-slate-200/50 text-center relative overflow-hidden">
                    
                    <?php if($status === 'active'): ?>
                        <div class="absolute top-5 right-5 flex items-center gap-2 bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)] animate-pulse"></span> <?= e(t('profile.active')); ?>
                        </div>
                    <?php endif; ?>

                    <div class="relative mx-auto mt-6 h-36 w-36 md:h-40 md:w-40">
                        <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-emerald-400 to-rose-400 animate-spin-slow blur-md opacity-50"></div>
                        <div class="relative h-full w-full rounded-full border-[5px] border-white shadow-xl overflow-hidden bg-slate-100">
                            <img id="sidebarAvatar" src="<?= e($avatarUrl) ?>" alt="Avatar" class="h-full w-full object-cover" />
                        </div>
                        <button onclick="openAvatarModal()" class="absolute bottom-1 right-1 w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-600 shadow-lg border border-slate-100 hover:text-emerald-600 hover:scale-110 transition-all z-10" title="<?= e(t('profile.change_avatar')); ?>">
                            <i class="fa-solid fa-camera text-sm"></i>
                        </button>
                    </div>

                    <div class="mt-6">
                        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight"><?= e($fullName) ?></h1>
                        <p class="text-sm font-bold text-slate-500 mt-1">@<?= e($username) ?></p>
                        <div class="mt-4">
                            <span class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-2 text-xs font-black uppercase tracking-widest text-rose-600 border border-rose-100 shadow-sm">
                                <?= $role === 'teacher' ? '<i class="fa-solid fa-chalkboard-user"></i>' : '<i class="fa-solid fa-laptop-code"></i>' ?>
                                <?= e($roleDisplay) ?>
                            </span>
                        </div>
                        <div class="mt-4 space-y-4 text-left">
                            <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100/60 text-slate-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shadow-sm">
                                    <i class="fa-solid fa-id-badge text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e($profileCodeLabel !== '' ? $profileCodeLabel : t('profile.account_code')) ?></p>
                                    <span class="font-bold text-slate-700 truncate block text-sm"><?= e($profileCode !== '' ? $profileCode : ('#' . (string) ($profileUser['id'] ?? $authUser['id'] ?? '---'))) ?></span>
                                </div>
                            </div>

                            <?php if ($role === 'student'): ?>
                                <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50/60 text-emerald-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shadow-sm">
                                        <i class="fa-solid fa-people-roof text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.father_name')); ?></p>
                                        <span class="font-bold text-slate-700 truncate block text-sm"><?= e($studentFatherName !== '' ? $studentFatherName : t('profile.not_updated')) ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50/60 text-blue-600 group-hover:bg-blue-50 group-hover:text-blue-700 transition-all shadow-sm">
                                        <i class="fa-solid fa-phone text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.father_phone')); ?></p>
                                        <span class="font-bold text-slate-700 truncate block text-sm"><?= e($studentFatherPhone !== '' ? $studentFatherPhone : t('profile.not_updated')) ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-fuchsia-50/60 text-fuchsia-600 group-hover:bg-fuchsia-50 group-hover:text-fuchsia-700 transition-all shadow-sm">
                                        <i class="fa-solid fa-user-group text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.mother_name')); ?></p>
                                        <span class="font-bold text-slate-700 truncate block text-sm"><?= e($studentMotherName !== '' ? $studentMotherName : t('profile.not_updated')) ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-50/60 text-cyan-600 group-hover:bg-cyan-50 group-hover:text-cyan-700 transition-all shadow-sm">
                                        <i class="fa-solid fa-mobile-screen-button text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.mother_phone')); ?></p>
                                        <span class="font-bold text-slate-700 truncate block text-sm"><?= e($studentMotherPhone !== '' ? $studentMotherPhone : t('profile.not_updated')) ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50/60 text-amber-600 group-hover:bg-amber-50 group-hover:text-amber-700 transition-all shadow-sm">
                                        <i class="fa-solid fa-school text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.school')); ?></p>
                                        <span class="font-bold text-slate-700 truncate block text-sm"><?= e($studentSchoolName !== '' ? $studentSchoolName : t('profile.not_updated')) ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-50/60 text-rose-600 group-hover:bg-rose-50 group-hover:text-rose-700 transition-all shadow-sm">
                                        <i class="fa-solid fa-layer-group text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.grade')); ?></p>
                                        <span class="font-bold text-slate-700 truncate block text-sm"><?= e($studentCurrentGrade !== '' ? $studentCurrentGrade : t('profile.not_updated')) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($role === 'teacher'): ?>
                                <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50/60 text-emerald-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shadow-sm">
                                        <i class="fa-solid fa-certificate text-lg"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.certificates')); ?></p>
                                        <span class="font-bold text-slate-700 truncate block text-sm"><?= e(t('profile.certificate_count', ['count' => (string) $teacherCertificatesCount])); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="w-full h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent my-8"></div>

                    <div class="space-y-4 text-left">
                        <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100/60 text-slate-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-envelope text-lg"></i></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Email</p>
                                <span class="font-bold text-slate-700 truncate block text-sm"><?= e($email) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100/60 text-slate-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-phone text-lg"></i></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.phone')); ?></p>
                                <span class="font-bold text-slate-700 truncate block text-sm"><?= e($phone) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-sm font-medium text-slate-600 group">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100/60 text-slate-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-calendar-check text-lg"></i></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= e(t('profile.join_date')); ?></p>
                                <span class="font-bold text-slate-700 truncate block text-sm"><?= e($createdAt) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="lg:col-span-8 space-y-6" data-aos="fade-up" data-aos-delay="100">
                
                <?php if (!$isTeacher): ?>
                <div class="bg-white/80 backdrop-blur-md p-2 rounded-2xl flex flex-wrap gap-2 w-full md:w-max shadow-sm border border-slate-200/60">
                    <button onclick="switchTab('overview')" id="tab-overview" class="nav-tab active flex-1 md:flex-none px-6 py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2">
                        <i class="fa-solid fa-chart-pie"></i> <?= e(t('profile.overview')); ?>
                    </button>
                    <button onclick="switchTab('settings')" id="tab-settings" class="nav-tab inactive flex-1 md:flex-none px-6 py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-pen"></i> <?= e(t('profile.update_profile')); ?>
                    </button>
                </div>

                <div id="content-overview" class="space-y-6 block animate-fade-in">
                    <?php if ($role === 'student'): ?>
                        <article class="rounded-[1.5rem] border border-slate-200/60 bg-white p-4 md:p-5 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-[0.9rem] bg-blue-500 text-white shadow-lg shadow-blue-500/30">
                                    <i class="fa-solid fa-layer-group text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-800"><?= e(t('profile.student_shortcuts')); ?></h3>
                                    <p class="text-xs font-medium text-slate-500 mt-1"><?= e(t('profile.student_shortcuts_copy')); ?></p>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3">
                                <a href="<?= e(page_url('dashboard-student')); ?>" class="group rounded-[1.25rem] border border-slate-200/70 bg-slate-50 p-4 text-left transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md">
                                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20 transition-transform group-hover:scale-110">
                                        <i class="fa-solid fa-calendar-days text-sm"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-500"><?= e(t('profile.add_schedule')); ?></p>
                                    <h4 class="mt-1.5 text-sm font-black text-slate-800 group-hover:text-blue-700"><?= e(t('profile.schedule')); ?></h4>
                                </a>

                                <a href="<?= e(page_url('classes-my')); ?>" class="group rounded-[1.25rem] border border-slate-200/70 bg-slate-50 p-4 text-left transition-all hover:-translate-y-1 hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-md">
                                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 transition-transform group-hover:scale-110">
                                        <i class="fa-solid fa-book-open text-sm"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500"><?= e(t('profile.my_classes')); ?></p>
                                    <h4 class="mt-1.5 text-sm font-black text-slate-800 group-hover:text-emerald-700"><?= e(t('profile.open_class_list')); ?></h4>
                                </a>

                                <a href="<?= e(page_url('activities-student')); ?>" class="group rounded-[1.25rem] border border-slate-200/70 bg-slate-50 p-4 text-left transition-all hover:-translate-y-1 hover:border-rose-300 hover:bg-rose-50 hover:shadow-md">
                                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-600 text-white shadow-lg shadow-rose-600/20 transition-transform group-hover:scale-110">
                                        <i class="fa-solid fa-people-group text-sm"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-rose-500"><?= e(t('profile.extracurricular')); ?></p>
                                    <h4 class="mt-1.5 text-sm font-black text-slate-800 group-hover:text-rose-700"><?= e(t('profile.view_activities')); ?></h4>
                                </a>
                            </div>
                        </article>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="rounded-[1.5rem] border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 mb-4 group-hover:scale-110 transition-transform"><i class="fa-solid fa-book-open text-xl"></i></div>
                                <p class="text-3xl font-black text-slate-800"><?= (int) $studentSubjectCount; ?></p>
                                <p class="text-xs font-black uppercase tracking-widest text-slate-400 mt-1"><?= e(t('profile.subjects')); ?></p>
                            </div>
                            <div class="rounded-[1.5rem] border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 mb-4 group-hover:scale-110 transition-transform"><i class="fa-solid fa-check-double text-xl"></i></div>
                                <p class="text-3xl font-black text-slate-800"><?= (int) $studentAttendancePercent; ?>%</p>
                                <p class="text-xs font-black uppercase tracking-widest text-slate-400 mt-1"><?= e(t('profile.attendance')); ?></p>
                            </div>
                            <div class="rounded-[1.5rem] border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 mb-4 group-hover:scale-110 transition-transform"><i class="fa-solid fa-star text-xl"></i></div>
                                <p class="text-xl font-black text-slate-800 leading-tight flex items-center justify-center h-9"><?= e($studentProgramScoreLabel); ?></p>
                                <p class="text-xs font-black uppercase tracking-widest text-slate-400 mt-1"><?= e(t('profile.target')); ?></p>
                            </div>
                            <div class="rounded-[1.5rem] border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all text-center group">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-500 mb-4 group-hover:scale-110 transition-transform"><i class="fa-solid fa-award text-xl"></i></div>
                                <p class="text-3xl font-black text-slate-800"><?= (int) $studentProgressPercent; ?>%</p>
                                <p class="text-xs font-black uppercase tracking-widest text-slate-400 mt-1"><?= e(t('profile.progress')); ?></p>
                            </div>
                        </div>

                        <?php if(is_array($studentProgress)): ?>
                        <article class="relative overflow-hidden rounded-[2rem] border border-slate-200/60 bg-white p-8 md:p-10 shadow-sm">
                            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gradient-to-br from-emerald-100/60 to-lime-100/60 blur-3xl pointer-events-none"></div>
                            
                            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-8 z-10">
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-3">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-[1rem] bg-emerald-500 text-white shadow-lg shadow-emerald-500/30">
                                            <i class="fa-solid fa-route text-xl"></i>
                                        </div>
                                        <h3 class="text-2xl font-black text-slate-800"><?= e(t('profile.course_progress')); ?></h3>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium max-w-md mt-2"><?= e(t('profile.course_progress_copy')); ?></p>
                                    
                                    <div class="mt-6 flex items-baseline gap-2">
                                        <span class="text-5xl font-black text-emerald-600 tracking-tighter"><?= (int) $studentCompletedLessons; ?></span>
                                        <span class="text-sm font-black text-slate-400 uppercase"><?= e(t('profile.lesson_total', ['count' => (string) $studentTotalLessons])); ?></span>
                                    </div>
                                </div>

                                <div class="w-full lg:w-80 bg-slate-50 p-6 rounded-[1.5rem] border border-slate-200/50">
                                    <div class="mb-4 flex items-center justify-between">
                                        <span class="text-xs font-black uppercase tracking-widest text-slate-500"><?= e(t('profile.completed')); ?></span>
                                        <span class="text-rose-600 font-black text-2xl"><?= (int) $studentProgressPercent; ?>%</span>
                                    </div>
                                    <div class="h-4 w-full overflow-hidden rounded-full bg-slate-200 shadow-inner">
                                        <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-rose-400 relative transition-all duration-1000" style="width: <?= (int) $studentProgressPercent; ?>%">
                                            <div class="absolute inset-0 bg-white/20 w-full h-full animate-[shimmer_2s_infinite]"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($role === 'teacher'): ?>
                        <article class="rounded-[2rem] border border-slate-200/60 bg-white p-8 md:p-10 shadow-sm">
                            <div class="flex items-center gap-4 mb-8">
                                <div class="flex h-14 w-14 items-center justify-center rounded-[1rem] bg-rose-500 text-white shadow-lg shadow-rose-500/30">
                                    <i class="fa-solid fa-calendar-day text-xl"></i>
                                </div>
                                <h3 class="text-2xl font-black text-slate-800"><?= e(t('profile.next_7_days_schedule')); ?></h3>
                            </div>
                            
                            <?php if (empty($teacherSchedules)): ?>
                                <div class="flex flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-slate-200 bg-slate-50 py-16 text-center">
                                    <div class="mb-4 rounded-full bg-white p-6 shadow-sm text-slate-300">
                                        <i class="fa-regular fa-calendar-xmark text-4xl"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-500 uppercase tracking-widest"><?= e(t('profile.empty_schedule')); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <?php foreach ($teacherSchedules as $schedule): ?>
                                        <div class="group relative rounded-2xl border border-slate-200/60 bg-white shadow-sm p-5 transition-all hover:shadow-lg hover:border-emerald-300 hover:-translate-y-1 cursor-pointer">
                                            <div class="mb-4 flex items-start justify-between gap-3">
                                                <div class="rounded-xl bg-emerald-50 px-3 py-2 text-left border border-emerald-100">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600"><?= e(t('profile.room')); ?></p>
                                                    <p class="mt-1 text-sm font-black text-emerald-800"><?= e((string) $schedule['room_name']); ?></p>
                                                </div>
                                                <div class="rounded-xl bg-slate-100 px-3 py-2 text-right">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500"><?= e(t('profile.study_date')); ?></p>
                                                    <p class="mt-1 text-sm font-black text-slate-700"><?= e((string) $schedule['study_date']); ?></p>
                                                </div>
                                            </div>
                                            <div class="space-y-4">
                                                <div>
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400"><?= e(t('profile.class_name')); ?></p>
                                                    <h4 class="mt-1 text-base font-black text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-1">
                                                        <?= e((string) $schedule['class_name']); ?>
                                                    </h4>
                                                </div>
                                                <div class="pt-4 border-t border-slate-100 flex items-center gap-3 text-sm font-bold text-slate-500">
                                                    <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors"><i class="fa-regular fa-clock"></i></div>
                                                    <div>
                                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400"><?= e(t('profile.study_time')); ?></p>
                                                        <p class="mt-1 text-sm font-black text-slate-700"><?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div id="content-settings" class="<?= ($isTeacher || $isStudent) ? 'block' : 'hidden'; ?> animate-fade-in">
                    <article class="rounded-[2rem] border border-slate-200/60 bg-white p-8 md:p-10 shadow-sm">
                        <div class="mb-8 border-b border-slate-100 pb-6 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-[1rem] bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl"><i class="fa-solid fa-user-pen"></i></div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-800"><?= e(t('profile.update_profile')); ?></h2>
                                <p class="text-sm font-medium text-slate-500 mt-1"><?= e(t('profile.update_profile_copy')); ?></p>
                            </div>
                        </div>

                        <form id="profileUpdateForm" action="/api/index.php?resource=users&method=update" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <?= csrf_input(); ?>
                            <input type="hidden" name="update_mode" value="profile">
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.contact_email')); ?> <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="email" name="email" value="<?= e($email) ?>" required class="input-modern w-full pl-11 pr-4 py-4 rounded-2xl bg-slate-50 text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.phone_number')); ?> <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                        <input type="tel" inputmode="numeric" pattern="[0-9]*" name="phone" value="<?= e($phone) ?>" required class="input-modern w-full pl-11 pr-4 py-4 rounded-2xl bg-slate-50 text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1"><?= e(t('profile.username')); ?> <span class="text-rose-500 lowercase"><?= e(t('profile.fixed')); ?></span></label>
                                    <div class="relative">
                                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                                        <input type="text" value="<?= e($username) ?>" readonly class="w-full pl-11 pr-4 py-4 rounded-2xl bg-slate-100 text-slate-400 text-sm font-bold border border-slate-200 cursor-not-allowed">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1"><?= e(t('profile.full_name')); ?> <span class="text-rose-500 lowercase"><?= e(t('profile.contact_admin')); ?></span></label>
                                    <div class="relative">
                                        <i class="fa-regular fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                                        <input type="text" value="<?= e($fullName) ?>" readonly class="w-full pl-11 pr-4 py-4 rounded-2xl bg-slate-100 text-slate-400 text-sm font-bold border border-slate-200 cursor-not-allowed">
                                    </div>
                                </div>
                            </div>

                            <?php if ($isStudent): ?>
                            <div class="rounded-[1.75rem] border border-slate-200/70 bg-slate-50 p-5 md:p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center"><i class="fa-solid fa-people-roof"></i></div>
                                    <div>
                                        <h3 class="text-base font-black text-slate-800"><?= e(t('profile.parent_info')); ?></h3>
                                        <p class="text-xs font-medium text-slate-500 mt-1"><?= e(t('profile.parent_info_copy')); ?></p>
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.father_name')); ?></label>
                                        <input type="text" name="student_father_name" value="<?= e($studentFatherName) ?>" class="input-modern w-full px-4 py-4 rounded-2xl bg-white text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.father_phone')); ?></label>
                                        <input type="tel" inputmode="numeric" pattern="[0-9]*" name="student_father_phone" value="<?= e($studentFatherPhone) ?>" class="input-modern w-full px-4 py-4 rounded-2xl bg-white text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.father_id')); ?></label>
                                        <input type="text" name="student_father_id_card" value="<?= e((string) (($profileUser['role_profile']['student_father_id_card'] ?? '') ?: ($profileUser['student_father_id_card'] ?? ''))) ?>" class="input-modern w-full px-4 py-4 rounded-2xl bg-white text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.mother_name')); ?></label>
                                        <input type="text" name="student_mother_name" value="<?= e($studentMotherName) ?>" class="input-modern w-full px-4 py-4 rounded-2xl bg-white text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.mother_phone')); ?></label>
                                        <input type="tel" inputmode="numeric" pattern="[0-9]*" name="student_mother_phone" value="<?= e($studentMotherPhone) ?>" class="input-modern w-full px-4 py-4 rounded-2xl bg-white text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.mother_id')); ?></label>
                                        <input type="text" name="student_mother_id_card" value="<?= e((string) (($profileUser['role_profile']['student_mother_id_card'] ?? '') ?: ($profileUser['student_mother_id_card'] ?? ''))) ?>" class="input-modern w-full px-4 py-4 rounded-2xl bg-white text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                                    </div>
                                </div>

                                <div class="mt-6 space-y-2">
                                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.parent_social_links')); ?></label>
                                    <textarea name="student_parent_social_links" rows="4" class="input-modern w-full px-4 py-4 rounded-2xl bg-white text-slate-800 text-sm font-bold border border-slate-200 transition-all" placeholder='<?= e(t('profile.parent_social_placeholder')); ?>'><?= e((string) (($profileUser['role_profile']['student_parent_social_links'] ?? '') ?: ($profileUser['student_parent_social_links'] ?? ''))) ?></textarea>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($isTeacher): ?>
                            <div class="rounded-[1.75rem] border border-slate-200/70 bg-slate-50 p-5 md:p-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center"><i class="fa-solid fa-video"></i></div>
                                    <div>
                                        <h3 class="text-base font-black text-slate-800"><?= e(t('profile.teacher_intro_video')); ?></h3>
                                        <p class="text-xs font-medium text-slate-500 mt-1"><?= e(t('profile.teacher_intro_video_copy')); ?></p>
                                    </div>
                                </div>

                                <input type="hidden" name="teacher_intro_video_url_hidden" value="<?= e($teacherIntroVideoUrl) ?>">

                                <div id="teacherVideoPreviewWrap" class="mb-4 <?= $teacherIntroVideoUrl !== '' ? '' : 'hidden'; ?> overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-sm">
                                    <video id="teacherVideoPreview" class="w-full max-h-72 bg-black" controls playsinline preload="metadata" <?= $teacherIntroVideoUrl !== '' ? '' : 'muted'; ?>>
                                        <source id="teacherVideoPreviewSource" src="<?= e($teacherIntroVideoUrl) ?>">
                                    </video>
                                </div>
                                <div id="teacherVideoEmptyState" class="mb-4 <?= $teacherIntroVideoUrl !== '' ? 'hidden' : ''; ?> rounded-[1.25rem] border border-dashed border-slate-300 bg-white px-4 py-8 text-center text-sm font-medium text-slate-500">
                                    <?= e(t('profile.no_intro_video')); ?>
                                </div>

                                <label class="group relative flex flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-slate-300 bg-white p-6 text-center transition-all hover:border-rose-500 hover:bg-rose-50 cursor-pointer">
                                    <input id="teacherIntroVideoInput" type="file" name="teacher_intro_video_file" accept="video/*" class="absolute inset-0 z-10 cursor-pointer opacity-0" onchange="previewTeacherIntroVideo(this, <?= (int) $teacherVideoMaxBytes; ?>)">
                                    <div id="teacherVideoUploadIcon" class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                                    </div>
                                    <p id="teacherVideoUploadTitle" class="text-sm font-black text-slate-700"><?= e(t('profile.upload_new_video')); ?></p>
                                    <p id="teacherVideoUploadMeta" class="mt-1 text-xs font-medium text-slate-400"><?= e(t('profile.video_upload_meta')); ?></p>
                                </label>
                            </div>
                            <?php endif; ?>

                            <div class="pt-4">
                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-black px-8 py-4 rounded-2xl shadow-lg shadow-rose-600/20 transition-all hover:-translate-y-1 text-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                                    <i class="fa-solid fa-floppy-disk"></i> <?= e(t('profile.save_profile_changes')); ?>
                                </button>
                            </div>
                        </form>

                        <div class="mt-10 pt-8 border-t border-slate-100 bg-slate-50/50 -mx-8 -mb-8 p-8 rounded-b-[2rem]">
                            <h3 class="text-base font-black text-slate-800 mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-shield-halved text-rose-500"></i> <?= e(t('profile.account_security')); ?>
                            </h3>
                            <p class="text-sm text-slate-500 font-medium mb-5"><?= e(t('profile.account_security_copy')); ?></p>
                            <button type="button" onclick="openPasswordModal()" class="inline-flex items-center gap-2 border border-slate-200 bg-white text-slate-600 text-sm font-bold px-6 py-3 rounded-xl hover:border-emerald-500 hover:text-emerald-600 shadow-sm transition-all">
                                <?= e(t('profile.update_new_password')); ?> <i class="fa-solid fa-arrow-right-long"></i>
                            </button>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </div>
</section>

<div id="avatarModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 modal-overlay opacity-0">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-[420px] overflow-hidden transform scale-95 modal-content border border-slate-100" id="avatarModalContent">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-image text-emerald-500"></i> <?= e(t('profile.change_avatar')); ?>
            </h3>
            <button onclick="closeAvatarModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors shadow-sm border border-slate-200 text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div class="p-8">
            <form id="avatarUpdateForm" action="/api/index.php?resource=users&method=update" method="POST" enctype="multipart/form-data" class="flex flex-col items-center">
                <?= csrf_input(); ?>
                <input type="hidden" name="update_mode" value="avatar">
                
                <div class="relative h-40 w-40 rounded-full border-[6px] border-slate-100 mb-8 overflow-hidden bg-slate-50 shadow-inner group">
                    <img id="modalAvatarPreview" src="<?= e($avatarUrl) ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
                
                <div class="w-full">
                    <label class="group relative w-full border-2 border-dashed border-slate-300 rounded-[1.5rem] p-6 flex flex-col items-center justify-center hover:border-emerald-500 hover:bg-emerald-50 transition-colors cursor-pointer bg-slate-50">
                        <input type="file" name="avatar" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                        <div class="w-12 h-12 rounded-full bg-white shadow-sm border border-slate-100 flex items-center justify-center text-emerald-500 mb-3 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700"><?= e(t('profile.upload_image')); ?></p>
                        <p class="text-xs font-bold text-slate-400 mt-1 uppercase">PNG, JPG (< 2MB)</p>
                    </label>
                </div>
                
                <button id="avatarSaveButton" type="submit" disabled class="mt-6 w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-rose-600/20 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-floppy-disk"></i> <?= e(t('profile.save_changes')); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<div id="passwordModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 modal-overlay opacity-0">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-[420px] overflow-hidden transform scale-95 modal-content border border-slate-100" id="passwordModalContent">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-key text-emerald-500"></i> <?= e(t('profile.change_password')); ?>
            </h3>
            <button onclick="closePasswordModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors shadow-sm border border-slate-200 text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-8">
            <form id="passwordUpdateForm" action="/api/index.php?resource=users&method=update" method="POST" class="space-y-5">
                <?= csrf_input(); ?>
                <input type="hidden" name="update_mode" value="password">

                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.current_password')); ?> *</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="current_password" name="current_password" required class="input-modern w-full pl-11 pr-12 py-4 rounded-2xl bg-slate-50 text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                        <button type="button" onclick="togglePasswordField('current_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 transition-colors" aria-label="<?= e(t('profile.toggle_current_password')); ?>">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.new_password')); ?> *</label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="new_password" name="new_password" required minlength="6" class="input-modern w-full pl-11 pr-12 py-4 rounded-2xl bg-slate-50 text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                        <button type="button" onclick="togglePasswordField('new_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 transition-colors" aria-label="<?= e(t('profile.toggle_new_password')); ?>">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1"><?= e(t('profile.confirm_password')); ?> *</label>
                    <div class="relative">
                        <i class="fa-solid fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="input-modern w-full pl-11 pr-12 py-4 rounded-2xl bg-slate-50 text-slate-800 text-sm font-bold border border-slate-200 transition-all">
                        <button type="button" onclick="togglePasswordField('confirm_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 transition-colors" aria-label="<?= e(t('profile.toggle_confirm_password')); ?>">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="mt-2 w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-rose-600/20 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> <?= e(t('profile.update_password')); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../notification/confirm_modal.php'; ?>

<style>
    @keyframes shimmer { 100% { transform: translateX(100%); } }
</style>

<script>
    const profileI18n = {
        uploadNewVideo: <?= json_encode(t('profile.upload_new_video'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        videoUploadMeta: <?= json_encode(t('profile.video_upload_meta'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        videoTooLarge: <?= json_encode(t('profile.video_too_large'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        selectedPrefix: <?= json_encode(t('profile.selected_prefix'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        saveVideoHint: <?= json_encode(t('profile.save_video_hint'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        confirmProfileTitle: <?= json_encode(t('profile.confirm_profile_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        confirmProfileMessage: <?= json_encode(t('profile.confirm_profile_message'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        confirmAvatarTitle: <?= json_encode(t('profile.confirm_avatar_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        confirmAvatarMessage: <?= json_encode(t('profile.confirm_avatar_message'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        currentPasswordRequired: <?= json_encode(t('profile.current_password_required'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        passwordMinLength: <?= json_encode(t('profile.password_min_length'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        passwordMismatch: <?= json_encode(t('profile.password_mismatch'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        confirmPasswordTitle: <?= json_encode(t('profile.confirm_password_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        confirmPasswordMessage: <?= json_encode(t('profile.confirm_password_message'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    };

    function switchTab(tabName) {
        const tabOverview = document.getElementById('tab-overview');
        const contentOverview = document.getElementById('content-overview');
        if (!tabOverview || !contentOverview) {
            return;
        }
        document.getElementById('tab-overview').className = 'nav-tab flex-1 md:flex-none px-6 py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2 ' + (tabName === 'overview' ? 'active' : 'inactive');
        document.getElementById('tab-settings').className = 'nav-tab flex-1 md:flex-none px-6 py-3 rounded-xl text-sm font-bold flex items-center justify-center gap-2 ' + (tabName === 'settings' ? 'active' : 'inactive');
        
        document.getElementById('content-overview').style.display = tabName === 'overview' ? 'block' : 'none';
        document.getElementById('content-settings').style.display = tabName === 'settings' ? 'block' : 'none';
    }

    function openAvatarModal() {
        const modal = document.getElementById('avatarModal');
        const content = document.getElementById('avatarModalContent');
        const saveButton = document.getElementById('avatarSaveButton');
        if (saveButton) saveButton.disabled = true;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        });
    }

    function openPasswordModal() {
        const modal = document.getElementById('passwordModal');
        const content = document.getElementById('passwordModalContent');
        if (!modal || !content) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        });
    }

    const shouldOpenPasswordModal = <?= $openPasswordModal ? 'true' : 'false' ?>;
    if (shouldOpenPasswordModal) {
        openPasswordModal();
        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('open_password');
            window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
        }
    }

    function closePasswordModal() {
        const modal = document.getElementById('passwordModal');
        const content = document.getElementById('passwordModalContent');
        if (!modal || !content) return;

        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
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
        }, 300);
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('modalAvatarPreview').src = e.target.result;
                document.getElementById('sidebarAvatar').src = e.target.result;
                const saveButton = document.getElementById('avatarSaveButton');
                if (saveButton) saveButton.disabled = false;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewTeacherIntroVideo(input, maxBytes) {
        const previewWrap = document.getElementById('teacherVideoPreviewWrap');
        const preview = document.getElementById('teacherVideoPreview');
        const previewSource = document.getElementById('teacherVideoPreviewSource');
        const emptyState = document.getElementById('teacherVideoEmptyState');
        const uploadTitle = document.getElementById('teacherVideoUploadTitle');
        const uploadMeta = document.getElementById('teacherVideoUploadMeta');

        if (!input || !input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];
        if (typeof maxBytes === 'number' && maxBytes > 0 && file.size > maxBytes) {
            input.value = '';
            if (uploadTitle) {
                uploadTitle.textContent = profileI18n.uploadNewVideo;
            }
            if (uploadMeta) {
                uploadMeta.textContent = profileI18n.videoUploadMeta;
            }
            alert(profileI18n.videoTooLarge);
            return;
        }

        const objectUrl = URL.createObjectURL(file);

        if (previewSource && preview) {
            previewSource.src = objectUrl;
            preview.load();
            previewWrap?.classList.remove('hidden');
        }

        if (emptyState) {
            emptyState.classList.add('hidden');
        }

        if (uploadTitle) {
            uploadTitle.textContent = profileI18n.selectedPrefix + file.name;
        }

        if (uploadMeta) {
            const fileSizeMb = (file.size / (1024 * 1024)).toFixed(2);
            uploadMeta.textContent = fileSizeMb + ' MB - ' + profileI18n.saveVideoHint;
        }
    }

    function togglePasswordField(fieldId, button) {
        const input = document.getElementById(fieldId);
        const icon = button ? button.querySelector('i') : null;
        if (!input || !icon) return;

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    }

    const profileUpdateForm = document.getElementById('profileUpdateForm');
    if (profileUpdateForm) {
        profileUpdateForm.addEventListener('submit', function(event) {
            event.preventDefault();
            if(typeof showConfirm === 'function') {
                showConfirm('success', profileI18n.confirmProfileTitle, profileI18n.confirmProfileMessage, () => profileUpdateForm.submit());
            } else {
                profileUpdateForm.submit();
            }
        });
    }

    const avatarUpdateForm = document.getElementById('avatarUpdateForm');
    if (avatarUpdateForm) {
        avatarUpdateForm.addEventListener('submit', function(event) {
            event.preventDefault();
            if(typeof showConfirm === 'function') {
                showConfirm('success', profileI18n.confirmAvatarTitle, profileI18n.confirmAvatarMessage, () => avatarUpdateForm.submit());
            } else {
                avatarUpdateForm.submit();
            }
        });
    }

    const passwordUpdateForm = document.getElementById('passwordUpdateForm');
    if (passwordUpdateForm) {
        const currentPasswordInput = passwordUpdateForm.querySelector('input[name="current_password"]');
        const newPasswordInput = passwordUpdateForm.querySelector('input[name="new_password"]');
        const confirmPasswordInput = passwordUpdateForm.querySelector('input[name="confirm_password"]');

        const clearPasswordValidity = () => {
            if (newPasswordInput) {
                newPasswordInput.setCustomValidity('');
            }
            if (confirmPasswordInput) {
                confirmPasswordInput.setCustomValidity('');
            }
        };

        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', clearPasswordValidity);
        }
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', clearPasswordValidity);
        }

        passwordUpdateForm.addEventListener('submit', function(event) {
            event.preventDefault();

            clearPasswordValidity();

            const currentPassword = currentPasswordInput ? currentPasswordInput.value.trim() : '';
            const newPassword = newPasswordInput ? newPasswordInput.value : '';
            const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

            if (currentPassword === '') {
                if (currentPasswordInput) {
                    currentPasswordInput.setCustomValidity(profileI18n.currentPasswordRequired);
                    currentPasswordInput.reportValidity();
                    currentPasswordInput.focus();
                }
                return;
            }

            if (newPassword.length < 6) {
                if (newPasswordInput) {
                    newPasswordInput.setCustomValidity(profileI18n.passwordMinLength);
                    newPasswordInput.reportValidity();
                    newPasswordInput.focus();
                }
                return;
            }

            if (newPassword !== confirmPassword) {
                if (confirmPasswordInput) {
                    confirmPasswordInput.setCustomValidity(profileI18n.passwordMismatch);
                    confirmPasswordInput.reportValidity();
                    confirmPasswordInput.focus();
                }
                return;
            }

            if (typeof showConfirm === 'function') {
                showConfirm('success', profileI18n.confirmPasswordTitle, profileI18n.confirmPasswordMessage, () => passwordUpdateForm.submit());
            } else {
                passwordUpdateForm.submit();
            }
        });
    }
</script>
