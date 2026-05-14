<!doctype html>
<html lang="<?= e(current_locale()); ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('app.title')); ?></title>
    <meta name="description" content="<?= e(t('app.description')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php require_once __DIR__ . '/tailwind_cdn.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #bae6fd; border-radius: 10px; } 

        @keyframes headerBellShake {
            0%, 100% { transform: rotate(0deg) translateX(0); }
            15% { transform: rotate(-10deg) translateX(-1px); }
            30% { transform: rotate(12deg) translateX(1px); }
            45% { transform: rotate(-8deg) translateX(-1px); }
            60% { transform: rotate(10deg) translateX(1px); }
            75% { transform: rotate(-4deg) translateX(0); }
        }

        @keyframes headerBellGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.25); }
            70% { box-shadow: 0 0 0 8px rgba(244, 63, 94, 0); }
        }

        .header-bell-alert {
            animation: headerBellShake 1.8s ease-in-out infinite, headerBellGlow 2.2s ease-out infinite;
            transform-origin: top center;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-jakarta leading-relaxed text-slate-800 antialiased flex flex-col">
    <?php
    require_once __DIR__ . '/../../models/AcademicModel.php';

    $headerUser = auth_user() ?? [];
    $currentUserRole = (string) ($headerUser['role'] ?? '');
    $isStudentUser = $currentUserRole === 'student';
    $currentPage = resolve_page_slug((string) ($_GET['page'] ?? 'home'));
    $studentDashboardActiveTabValue = (string) ($studentDashboardActiveTab ?? '');
    $isStudentProfileActive = $currentPage === resolve_page_slug('profile');
    $headerUnreadNotificationCount = 0;
    $headerRecentNotifications = [];

    if (is_logged_in() && (int) ($headerUser['id'] ?? 0) > 0) {
        try {
            $headerNotificationModel = new AcademicModel();
            $headerUnreadNotificationCount = $headerNotificationModel->countUnreadNotifications((int) $headerUser['id']);
            $headerRecentNotifications = $headerNotificationModel->listNotificationDropdownItems((int) $headerUser['id'], 5);
        } catch (Throwable) {
            $headerUnreadNotificationCount = 0;
            $headerRecentNotifications = [];
        }
    }
    $currentLocale = current_locale();

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
        <div class="mx-auto w-full max-w-[1450px] px-4 sm:px-6 flex min-h-[85px] items-center justify-between gap-3">
            
            <div class="flex-none flex items-center h-full">
    <a href="/" class="flex items-center justify-center">
        <img src="assets/images/logo_remove.png" 
             alt="Logo" 
             class="h-14 md:h-16 w-auto object-contain transition-transform hover:scale-105">
    </a>
</div>

            <nav class="hidden min-w-0 flex-1 items-center justify-center gap-6 lg:flex lg:gap-8" aria-label="Menu chính">
                <a class="whitespace-nowrap text-[16px] font-extrabold transition-colors <?= $isActivePage(['home']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="/">
                    <?= e(t('nav.home')); ?>
                </a>
                
                <div class="relative group py-6">
                    <a class="inline-flex items-center gap-1.5 text-[16px] font-extrabold transition-colors cursor-pointer <?= $isActivePage(['courses', 'course-detail']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('courses')); ?>">
                        <?= e(t('nav.courses')); ?>
                        <!-- <i class="fa-solid fa-chevron-down text-[10px] text-slate-800 group-hover:text-[#27318b] transition-transform duration-300 group-hover:rotate-180"></i> -->
                    </a>
                </div>

                <a class="whitespace-nowrap text-[16px] font-extrabold transition-colors <?= $isActivePage(['teacher-introduce', 'teacher-detail']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('teacher-introduce')); ?>">
                    <?= e(t('nav.teachers')); ?>
                </a>
                <a class="whitespace-nowrap text-[16px] font-extrabold transition-colors <?= $isActivePage(['activities-home', 'activities-home-detail']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('activities-home')); ?>">
                    <?= e(t('nav.activities')); ?>
                </a>
                <a class="whitespace-nowrap text-[16px] font-extrabold transition-colors <?= $isActivePage(['job-apply']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('job-apply')); ?>">
                    <?= e(t('nav.jobs')); ?>
                </a>
               
                
                <div class="relative group py-6">
                    <button class="inline-flex items-center gap-1.5 whitespace-nowrap text-[16px] font-extrabold transition-colors <?= $isActivePage(['documents', 'home']) ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>" type="button">
                        <?= e(t('nav.system')); ?>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180 <?= $isActivePage(['documents', 'home']) ? 'text-blue-600' : 'text-slate-800 group-hover:text-blue-600' ?>"></i>
                    </button>
                        <div class="absolute left-1/2 -translate-x-1/2 top-full z-50 w-56 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                        <div class="rounded-xl border border-slate-100 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] py-2">
                            <!-- <a class="block px-5 py-2.5 text-[15px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b] transition-colors" href="#portal">Cổng học tập</a> -->
                            <!-- <a class="block px-5 py-2.5 text-[15px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b] transition-colors" href="#quan-tri">Quản trị vận hành</a> -->
                            <a class="block px-5 py-2.5 text-[15px] font-bold hover:bg-slate-50 transition-colors <?= $isActivePage(['documents']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('documents')); ?>"><?= e(t('nav.documents')); ?></a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="hidden min-w-0 flex-none items-center gap-4 lg:gap-5 lg:flex">
                <div class="inline-flex rounded-full border border-slate-200 bg-white p-1 text-xs font-black shadow-sm" aria-label="<?= e(t('locale.language')); ?>">
                    <a class="rounded-full px-2.5 py-1 <?= $currentLocale === 'vi' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-blue-700'; ?>" href="<?= e(localized_current_url('vi')); ?>" title="<?= e(t('locale.switch_to', ['language' => 'Tiếng Việt'])); ?>"><?= e(t('locale.vi')); ?></a>
                    <a class="rounded-full px-2.5 py-1 <?= $currentLocale === 'en' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-blue-700'; ?>" href="<?= e(localized_current_url('en')); ?>" title="<?= e(t('locale.switch_to', ['language' => 'English'])); ?>"><?= e(t('locale.en')); ?></a>
                </div>
                <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="group hidden lg:inline-flex items-center gap-3 rounded-full bg-rose-600 px-6 py-3 text-[15px] font-black uppercase text-white transition-all hover:bg-rose-700 hover:shadow-lg">
                    <?= e(t('nav.consultation')); ?>
                    <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                </a>
                <?php if (is_logged_in()): ?>
                    <div class="relative group py-4">
                        <button type="button" class="<?= $headerUnreadNotificationCount > 0 ? 'header-bell-alert ' : ''; ?>relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-gradient-to-br from-white to-slate-50 text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-600 hover:shadow-md" aria-label="Thông báo" aria-haspopup="true" aria-expanded="false" title="Thông báo">
                            <span class="absolute inset-0 rounded-full bg-blue-50/70 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                            <span class="absolute inset-[3px] rounded-full bg-gradient-to-br from-rose-500/8 via-white to-blue-500/8"></span>
                            <i class="fa-solid fa-bell relative z-10 text-[16px] <?= $headerUnreadNotificationCount > 0 ? 'text-rose-600' : 'text-slate-700'; ?>"></i>
                            <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[10px] font-black leading-none text-white shadow-sm shadow-rose-600/25 <?= $headerUnreadNotificationCount > 0 ? '' : 'hidden'; ?>"><?= e((string) ($headerUnreadNotificationCount > 99 ? '99+' : $headerUnreadNotificationCount)); ?></span>
                            <?php if ($headerUnreadNotificationCount > 0): ?>
                                <span class="absolute -inset-1 rounded-full border border-rose-400/30 animate-pulse"></span>
                            <?php endif; ?>
                        </button>

                        <div class="absolute right-0 top-full z-50 mt-3 w-80 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.12)]">
                                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                                    <div class="flex items-center gap-2 text-sm font-black text-slate-900">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-red-500 text-white shadow-sm shadow-rose-500/20">
                                            <i class="fa-solid fa-bell text-[13px]"></i>
                                        </span>
                                        Thông báo
                                    </div>
                                    <div class="mt-1 text-xs font-medium text-slate-500">
                                        <?= e($headerUnreadNotificationCount > 0
                                            ? ('Có ' . ($headerUnreadNotificationCount > 99 ? '99+' : $headerUnreadNotificationCount) . ' thông báo chưa đọc')
                                            : 'Bạn đã xem hết thông báo'); ?>
                                    </div>
                                </div>

                                <div class="max-h-80 overflow-y-auto custom-scrollbar">
                                    <?php if ($headerRecentNotifications === []): ?>
                                        <div class="px-5 py-6 text-sm text-slate-500">Chưa có thông báo nào.</div>
                                    <?php else: ?>
                                        <?php foreach ($headerRecentNotifications as $notification): ?>
                                            <?php
                                            $notificationId = (int) ($notification['id'] ?? 0);
                                            $notificationTitle = trim((string) ($notification['title'] ?? 'Thông báo hệ thống'));
                                            $notificationMessage = trim((string) ($notification['message'] ?? ''));
                                            if ($notificationMessage !== '') {
                                                if (function_exists('bbcode_truncate_plain_text')) {
                                                    $notificationMessage = bbcode_truncate_plain_text($notificationMessage, 120);
                                                } elseif (function_exists('mb_strimwidth')) {
                                                    $notificationMessage = mb_strimwidth(strip_tags($notificationMessage), 0, 120, '...');
                                                } elseif (strlen($notificationMessage) > 120) {
                                                    $notificationMessage = substr(strip_tags($notificationMessage), 0, 117) . '...';
                                                }
                                            }
                                            $notificationActionUrl = page_url('student-notification', ['highlight_notification_id' => $notificationId]);
                                            $notificationIsRead = (int) ($notification['is_read'] ?? 0) === 1;
                                            ?>
                                            <a href="<?= e($notificationActionUrl); ?>" data-notification-id="<?= $notificationId; ?>" onclick="try { sessionStorage.setItem('studentNotificationHighlightId', '<?= $notificationId; ?>'); } catch (error) {}" class="block border-b border-slate-50 px-5 py-4 transition-colors hover:bg-slate-50 <?= $notificationIsRead ? 'text-slate-700' : 'bg-blue-50/40 text-slate-900'; ?>">
                                                <div class="flex items-start gap-3">
                                                    <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full <?= $notificationIsRead ? 'bg-slate-300' : 'bg-rose-500'; ?>"></span>
                                                    <div class="min-w-0">
                                                        <div class="text-sm leading-snug <?= $notificationIsRead ? 'font-semibold' : 'font-black'; ?>"><?= e($notificationTitle); ?></div>
                                                        <?php if ($notificationMessage !== ''): ?>
                                                            <div class="mt-1 text-xs leading-relaxed <?= $notificationIsRead ? 'font-normal text-slate-500' : 'font-semibold text-slate-700'; ?>"><?= e($notificationMessage); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative group py-4">
                        <button class="inline-flex flex-none items-center gap-2.5 whitespace-nowrap text-[16px] font-extrabold transition-colors <?= $isActivePage(['feedback', 'dashboard-student', 'classes-my', 'activities-student', 'admin']) || $isStudentProfileActive ? 'text-blue-600' : 'text-slate-800 hover:text-blue-600' ?>">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-red-500 to-green-500 text-white flex items-center justify-center text-sm font-black">
                                <?= substr($headerUser['full_name'] ?? 'U', 0, 1) ?>
                            </div>
                            <span class="text-left">Tài khoản</span>
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180 <?= $isActivePage(['feedback', 'dashboard-student', 'classes-my', 'activities-student', 'admin']) || $isStudentProfileActive ? 'text-blue-600' : 'text-slate-800 group-hover:text-blue-600' ?>"></i>
                        </button>
                        
                        <div class="absolute right-0 top-full z-50 w-64 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                            <div class="rounded-xl border border-slate-100 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] py-2">
                                <?php if (can_access_page('profile')): ?><a class="block px-5 py-2.5 text-[14px] font-bold text-slate-700 hover:bg-slate-50 hover:text-blue-600" href="<?= e(page_url('profile')); ?>"><?= e(t('nav.profile')); ?></a><?php endif; ?>
                                <?php if (can_access_page('profile')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isActivePage(['profile']) && isset($_GET['open_password']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('profile', ['open_password' => 1])); ?>"><?= e(t('nav.change_password')); ?></a><?php endif; ?>
                                    <?php if (can_access_page('feedback')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-emerald-50 <?= $isActivePage(['feedback']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800 hover:text-blue-600' ?>" href="<?= e(page_url('feedback')); ?>"><?= e(t('nav.feedback')); ?></a><?php endif; ?>
                                <?php if ($isStudentUser): ?>
                                    <?php if (can_access_page('dashboard-student')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentPanelTabActive('dashboard-student') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.schedule')); ?></a><?php endif; ?>
                                    <?php if (can_access_page('classes-my')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentPanelTabActive('classes-my') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('classes-my')); ?>"><?= e(t('nav.my_classes')); ?></a><?php endif; ?>
                                    <?php if (can_access_page('activities-student')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentPanelTabActive('activities-student') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('activities-student')); ?>"><?= e(t('nav.student_activities')); ?></a><?php endif; ?>
                                <?php endif; ?>
                                <?php if (can_access_page('dashboard-student')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-slate-50 <?= $isStudentProfileActive ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.student_page')); ?></a><?php endif; ?>
                                <?php if (can_access_page('admin')): ?><a class="block px-5 py-2.5 text-[14px] font-bold hover:bg-blue-50 <?= $isActivePage(['admin', 'dashboard-admin', 'users-admin', 'tuition-finance', 'registration-finance', 'promotions-manage', 'payments-finance', 'feedbacks-manage', 'student-leads-manage', 'job-applications-manage', 'approvals-manage', 'activities-manage', 'rooms-manage', 'notifications-manage', 'bank-manage']) ? 'text-blue-600 bg-blue-50/50' : 'text-[#27318b]' ?>" href="<?= e(page_url('admin')); ?>"><?= e(t('nav.admin')); ?></a><?php endif; ?>
                                <div class="h-px bg-slate-100 my-1"></div>
                                <a class="block px-5 py-2.5 text-[14px] font-bold text-rose-600 hover:bg-rose-50" href="<?= e(page_url('logout')); ?>"><?= e(t('nav.logout')); ?></a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= e(page_url('login')); ?>" class="group inline-flex items-center justify-center gap-3 whitespace-nowrap rounded-full bg-red-600 px-7 py-3 text-[15px] font-black uppercase text-white transition-all hover:bg-red-700 hover:shadow-lg">
                        <?= e(t('nav.login')); ?>
                        <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                    </a>
                <?php endif; ?>
            </div>

            <button id="mobile-menu-toggle" class="inline-flex h-10 w-10 flex-col items-center justify-center gap-1.5 text-slate-800 hover:text-[#27318b] lg:hidden" type="button">
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
            </button>

            <nav id="main-nav" class="absolute left-0 right-0 top-full z-50 hidden flex-col border-t border-slate-100 bg-white shadow-xl lg:hidden origin-top" aria-label="<?= e(t('nav.mobile')); ?>">
                <div class="flex items-center gap-2 border-b border-slate-50 px-6 py-4 text-xs font-black" aria-label="<?= e(t('locale.language')); ?>">
                    <a class="rounded-full px-3 py-1.5 <?= $currentLocale === 'vi' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'; ?>" href="<?= e(localized_current_url('vi')); ?>"><?= e(t('locale.vi')); ?></a>
                    <a class="rounded-full px-3 py-1.5 <?= $currentLocale === 'en' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'; ?>" href="<?= e(localized_current_url('en')); ?>"><?= e(t('locale.en')); ?></a>
                </div>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['home']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="/"><?= e(t('nav.home')); ?></a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['courses', 'course-detail']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('courses')); ?>"><?= e(t('nav.courses')); ?></a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['teacher-introduce', 'teacher-detail']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('teacher-introduce')); ?>"><?= e(t('nav.teachers')); ?></a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['activities-home', 'activities-home-detail']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('activities-home')); ?>"><?= e(t('nav.activities')); ?></a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['job-apply']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('job-apply')); ?>"><?= e(t('nav.jobs')); ?></a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold hover:bg-slate-50 <?= $isActivePage(['home']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-800' ?>" href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>"><?= e(t('nav.consultation')); ?></a>
                
                <?php if (is_logged_in()): ?>
                    <div class="bg-slate-50 px-6 py-4">
                        <p class="text-[13px] font-bold text-slate-500 uppercase mb-2"><?= e(t('nav.account', ['name' => (string) ($headerUser['full_name'] ?? 'Khách')])); ?></p>
                        <div class="grid gap-2">
                            <a class="text-[15px] font-bold text-slate-800 hover:text-blue-600" href="<?= e(page_url('profile')); ?>"><?= e(t('nav.profile')); ?></a>
                            <a class="text-[15px] font-bold <?= $isActivePage(['profile']) && isset($_GET['open_password']) ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('profile', ['open_password' => 1])); ?>"><?= e(t('nav.change_password')); ?></a>
                            <a class="text-[15px] font-bold <?= $isActivePage(['feedback']) ? 'text-blue-600' : 'text-slate-800' ?>" href="<?= e(page_url('feedback')); ?>"><?= e(t('nav.feedback')); ?></a>
                            <?php if ($isStudentUser): ?>
                                <a class="text-[15px] font-bold <?= $isStudentPanelTabActive('dashboard-student') ? 'text-blue-600' : 'text-slate-800' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.schedule')); ?></a>
                                <a class="text-[15px] font-bold <?= $isStudentPanelTabActive('classes-my') ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('classes-my')); ?>"><?= e(t('nav.my_classes')); ?></a>
                                <a class="text-[15px] font-bold <?= $isStudentPanelTabActive('activities-student') ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('activities-student')); ?>"><?= e(t('nav.student_activities')); ?></a>
                            <?php endif; ?>
                            <a class="text-[15px] font-bold <?= $isStudentProfileActive ? 'text-blue-600' : 'text-[#27318b]' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.student_page')); ?></a>
                            <a class="text-[15px] font-bold text-rose-600" href="<?= e(page_url('logout')); ?>"><?= e(t('nav.logout')); ?></a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-6">
                        <a href="<?= e(page_url('login')); ?>" class="flex w-full items-center justify-center gap-3 rounded-full bg-red-600 px-6 py-3.5 text-[15px] font-black uppercase text-white hover:bg-red-700 hover:shadow-lg">
                            <?= e(t('nav.login')); ?>
                            <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="flex-grow">
