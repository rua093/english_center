<!doctype html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nền tảng Trung tâm Anh ngữ</title>
    <meta name="description" content="Nền tảng quản lý Trung tâm Ngoại ngữ: marketing, portal học viên và quản trị vận hành toàn diện.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php require_once __DIR__ . '/tailwind_cdn.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #bae6fd; border-radius: 10px; } 
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-jakarta leading-relaxed text-slate-800 antialiased flex flex-col">
    <?php
    $headerUser = auth_user() ?? [];
    $currentUserRole = (string) ($headerUser['role'] ?? '');
    $isStudentUser = $currentUserRole === 'student';
    $currentPage = resolve_page_slug((string) ($_GET['page'] ?? 'home'));
    $studentDashboardActiveTabValue = (string) ($studentDashboardActiveTab ?? '');
    $isStudentProfileActive = $currentPage === resolve_page_slug('profile');

    $isActivePage = static function (array $pageSlugs) use ($currentPage): bool {
        foreach ($pageSlugs as $pageSlug) {
            if ($currentPage === resolve_page_slug((string) $pageSlug)) {
                return true;
            }
        }

        return false;
    };

    $isStudentPanelTabActive = static function (string $tabSlug) use ($currentPage, $studentDashboardActiveTabValue): bool {
        if ($studentDashboardActiveTabValue !== '' && $studentDashboardActiveTabValue === $tabSlug) {
            return true;
        }

        return $currentPage === resolve_page_slug($tabSlug);
    };
    ?>
    
    <header class="sticky top-0 z-50 w-full bg-white shadow-[0_2px_15px_rgba(0,0,0,0.04)]" id="top">    
        <div class="mx-auto w-full max-w-[1450px] px-4 sm:px-6 flex min-h-[85px] items-center justify-between gap-4">
            
            <div class="flex-none flex items-center overflow-visible">
                <a href="/" class="inline-flex items-center overflow-visible -my-3">
                    <img src="assets/images/logo_remove.png" alt="Logo" class="h-20 md:h-24 w-auto max-w-none object-contain -mb-2">
                </a>
            </div>

            <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex lg:gap-10" aria-label="Menu chính">
                <a class="text-[16px] font-extrabold transition-colors <?= $isActivePage(['home']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="/">
                    Trang chủ
                </a>
                
                <div class="relative group py-6">
                    <a class="inline-flex items-center gap-1.5 text-[16px] font-extrabold transition-colors cursor-pointer <?= $isActivePage(['courses', 'course-detail']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('courses')); ?>">
                        Chương trình học
                        <!-- <i class="fa-solid fa-chevron-down text-[10px] text-slate-800 group-hover:text-[#27318b] transition-transform duration-300 group-hover:rotate-180"></i> -->
                    </a>
                </div>

                <a class="text-[16px] font-extrabold transition-colors <?= $isActivePage(['teacher-introduce', 'teacher-detail']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('teacher-introduce')); ?>">
                    Giáo viên
                </a>
                <a class="text-[16px] font-extrabold transition-colors <?= $isActivePage(['activities-home', 'activities-home-detail']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('activities-home')); ?>">
                    Hoạt động ngoại khoá
                </a>
                <a class="text-[16px] font-extrabold transition-colors <?= $isActivePage(['job-apply']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('job-apply')); ?>">
                    Tuyển dụng
                </a>
               
                
                <div class="relative group py-6">
                    <button class="inline-flex items-center gap-1.5 text-[16px] font-extrabold transition-colors <?= $isActivePage(['register-consultation', 'documents']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" type="button">
                        Hệ thống
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180 <?= $isActivePage(['register-consultation', 'documents']) ? 'text-blue-600' : 'text-slate-800 group-hover:text-blue-600' ?>"></i>
                    </button>
                        <div class="absolute left-1/2 -translate-x-1/2 top-full z-50 w-56 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                        <div class="rounded-xl border border-slate-100 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] py-2">
                            <!-- <a class="block px-5 py-2.5 text-[15px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b] transition-colors" href="#portal">Cổng học tập</a> -->
                            <!-- <a class="block px-5 py-2.5 text-[15px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b] transition-colors" href="#quan-tri">Quản trị vận hành</a> -->
                            <a class="block px-5 py-2.5 text-[15px] font-bold hover:bg-slate-50 transition-colors <?= $isActivePage(['documents']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('documents')); ?>">Tài liệu học tập</a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="hidden items-center gap-6 lg:flex">
                <a href="<?= e(page_url('register-consultation')); ?>" class="group hidden lg:inline-flex items-center gap-3 rounded-full bg-rose-600 px-6 py-3 text-[15px] font-black uppercase text-white transition-all hover:bg-rose-700 hover:shadow-lg">
                    Đăng ký tư vấn
                    <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                </a>
                <?php if (is_logged_in()): ?>
                    <div class="relative group py-6">
                        <button class="inline-flex items-center gap-2.5 text-[16px] font-extrabold transition-colors <?= $isActivePage(['feedback', 'dashboard-student', 'classes-my', 'activities-student', 'admin']) || $isStudentProfileActive ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-red-500 to-green-500 text-white flex items-center justify-center text-sm font-black">
                                <?= substr($user['full_name'] ?? 'U', 0, 1) ?>
                            </div>
                            <span class="max-w-[120px] truncate"><?= e($user['full_name'] ?? 'Tài khoản') ?></span>
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180 <?= $isActivePage(['feedback', 'dashboard-student', 'classes-my', 'activities-student', 'admin']) || $isStudentProfileActive ? 'text-blue-600' : 'text-slate-800 group-hover:text-blue-600' ?>"></i>
                        </button>
                        
                        <div class="absolute right-0 top-full z-50 w-64 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                            <div class="rounded-xl border border-slate-100 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] py-2">
                                <?php if (can_access_page('profile')): ?><a class="block px-5 py-2.5 text-[14px] font-bold text-slate-700 hover:bg-slate-50 hover:text-blue-600" href="<?= e(page_url('profile')); ?>">Trang cá nhân</a><?php endif; ?>
                                <?php if (can_access_page('profile')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isActivePage(['profile']) && isset($_GET['open_password']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('profile', ['open_password' => 1])); ?>">Thay đổi mật khẩu</a><?php endif; ?>
                                    <?php if (can_access_page('feedback')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-emerald-50 <?= $isActivePage(['feedback']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('feedback')); ?>">Đánh giá trung tâm</a><?php endif; ?>
                                <?php if ($isStudentUser): ?>
                                    <?php if (can_access_page('dashboard-student')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentPanelTabActive('dashboard-student') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('dashboard-student')); ?>">Thời khoá biểu</a><?php endif; ?>
                                    <?php if (can_access_page('classes-my')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentPanelTabActive('classes-my') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('classes-my')); ?>">Lớp học của tôi</a><?php endif; ?>
                                    <?php if (can_access_page('activities-student')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentPanelTabActive('activities-student') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('activities-student')); ?>">Ngoại khoá</a><?php endif; ?>
                                <?php endif; ?>
                                <?php if (can_access_page('dashboard-student')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentProfileActive ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('dashboard-student')); ?>">Trang Học viên</a><?php endif; ?>
                                <?php if (can_access_page('admin')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-blue-50 <?= $isActivePage(['admin', 'dashboard-admin', 'users-admin', 'tuition-finance', 'registration-finance', 'promotions-manage', 'payments-finance', 'feedbacks-manage', 'student-leads-manage', 'job-applications-manage', 'approvals-manage', 'activities-manage', 'rooms-manage', 'notifications-manage', 'bank-manage']) ? 'text-blue-600 bg-blue-50/50' : 'text-[#27318b]' ?>" href="<?= e(page_url('admin')); ?>">Quản trị hệ thống</a><?php endif; ?>
                                <div class="h-px bg-slate-100 my-1"></div>
                                <a class="block px-5 py-2.5 text-[14px] font-bold text-rose-600 hover:bg-rose-50" href="<?= e(page_url('logout')); ?>">Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= e(page_url('login')); ?>" class="group inline-flex items-center gap-3 rounded-full bg-red-600 px-7 py-3 text-[15px] font-black uppercase text-white transition-all hover:bg-red-700 hover:shadow-lg">
                        ĐĂNG NHẬP
                        <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                    </a>
                <?php endif; ?>
            </div>

            <button id="mobile-menu-toggle" class="inline-flex h-10 w-10 flex-col items-center justify-center gap-1.5 text-slate-800 hover:text-[#27318b] lg:hidden" type="button">
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
            </button>

            <nav id="main-nav" class="absolute left-0 right-0 top-full z-50 hidden flex-col border-t border-slate-100 bg-white shadow-xl lg:hidden origin-top" aria-label="Menu mobile">
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['home']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="/">Trang chủ</a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['courses', 'course-detail']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('courses')); ?>">Chương trình học</a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['teacher-introduce', 'teacher-detail']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('teacher-introduce')); ?>">Giáo viên</a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['activities-home', 'activities-home-detail']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('activities-home')); ?>">Hoạt động ngoại khoá</a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['job-apply']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('job-apply')); ?>">Tuyển dụng</a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['register-consultation']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('register-consultation')); ?>">Đăng ký tư vấn</a>
                
                <?php if (is_logged_in()): ?>
                    <div class="bg-slate-50 px-6 py-4">
                        <p class="text-[13px] font-bold text-slate-500 uppercase mb-2">Tài khoản: <?= e($user['full_name'] ?? 'Khách') ?></p>
                        <div class="grid gap-2">
                            <a class="text-[15px] font-bold text-slate-800 hover:text-blue-600" href="<?= e(page_url('profile')); ?>">Trang cá nhân</a>
                            <a class="text-[15px] font-bold <?= $isActivePage(['profile']) && isset($_GET['open_password']) ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('profile', ['open_password' => 1])); ?>">Thay đổi mật khẩu</a>
                            <a class="text-[15px] font-bold <?= $isActivePage(['feedback']) ? 'text-blue-600' : 'text-slate-800' ?>" href="<?= e(page_url('feedback')); ?>">Đánh giá trung tâm</a>
                            <?php if ($isStudentUser): ?>
                                <a class="text-[15px] font-bold <?= $isStudentPanelTabActive('dashboard-student') ? 'text-blue-600' : 'text-slate-800' ?>" href="<?= e(page_url('dashboard-student')); ?>">Thời khoá biểu</a>
                                <a class="text-[15px] font-bold <?= $isStudentPanelTabActive('classes-my') ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('classes-my')); ?>">Lớp học của tôi</a>
                                <a class="text-[15px] font-bold <?= $isStudentPanelTabActive('activities-student') ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('activities-student')); ?>">Ngoại khoá</a>
                            <?php endif; ?>
                            <a class="text-[15px] font-bold <?= $isStudentProfileActive ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('dashboard-student')); ?>">Trang Học viên</a>
                            <a class="text-[15px] font-bold text-rose-600" href="<?= e(page_url('logout')); ?>">Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-6">
                        <a href="<?= e(page_url('login')); ?>" class="flex w-full items-center justify-center gap-3 rounded-full bg-red-600 px-6 py-3.5 text-[15px] font-black uppercase text-white hover:bg-red-700 hover:shadow-lg">
                            ĐĂNG NHẬP
                            <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="flex-grow">
