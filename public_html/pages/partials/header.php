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
    $headerUserDisplayName = trim((string) ($headerUser['full_name'] ?? ''));
    $headerUserAvatarUrl = normalize_public_file_url((string) ($headerUser['avatar'] ?? ''));

    if ($headerUserAvatarUrl === '' && is_logged_in() && (int) ($headerUser['id'] ?? 0) > 0) {
        try {
            $headerAvatarStmt = Database::connection()->prepare('SELECT avatar FROM users WHERE id = :id LIMIT 1');
            $headerAvatarStmt->execute(['id' => (int) $headerUser['id']]);
            $headerAvatarRow = $headerAvatarStmt->fetch();
            $headerUserAvatarUrl = normalize_public_file_url((string) ($headerAvatarRow['avatar'] ?? ''));
        } catch (Throwable) {
            $headerUserAvatarUrl = '';
        }
    }

    $headerUserInitial = 'U';
    if ($headerUserDisplayName !== '') {
        $headerUserInitial = function_exists('mb_substr')
            ? mb_substr($headerUserDisplayName, 0, 1)
            : substr($headerUserDisplayName, 0, 1);
    }
    if (function_exists('mb_strtoupper')) {
        $headerUserInitial = mb_strtoupper($headerUserInitial);
    } else {
        $headerUserInitial = strtoupper($headerUserInitial);
    }

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
    
    <header class="sticky top-0 z-50 w-full bg-white shadow-[0_2px_15px_rgba(0,0,0,0.03)]" id="top">    
        <div class="mx-auto flex min-h-[85px] w-full max-w-[1450px] items-center justify-between gap-5 px-4 sm:px-6">
            
            <div class="flex-none flex items-center h-full">
                <a href="/" class="flex items-center justify-center rounded-2xl px-2 py-1.5 transition-transform duration-300 hover:scale-[1.02]">
                    <img src="assets/images/logo_remove.png" 
                         alt="Logo" 
                         class="h-[3.8rem] md:h-[4.1rem] w-auto object-contain">
                </a>
            </div>

            <nav class="hidden min-w-0 flex-1 items-center justify-center gap-6 xl:gap-8 lg:flex" aria-label="Menu chính">
                
                <a class="relative py-2 text-[14px] xl:text-[15px] font-semibold whitespace-nowrap transition-colors duration-300 <?= $isActivePage(['home']) ? 'text-blue-600' : 'text-slate-700 hover:text-blue-600' ?> group" href="/">
                    <?= e(t('nav.home')); ?>
                    <span class="absolute bottom-0 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-blue-600 transition-all duration-300 group-hover:w-full <?= $isActivePage(['home']) ? 'w-full' : '' ?>"></span>
                </a>
                
                <a class="relative py-2 text-[14px] xl:text-[15px] font-semibold whitespace-nowrap transition-colors duration-300 <?= $isActivePage(['courses', 'course-detail']) ? 'text-blue-600' : 'text-slate-700 hover:text-blue-600' ?> group" href="<?= e(page_url('courses')); ?>">
                    <?= e(t('nav.courses')); ?>
                    <span class="absolute bottom-0 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-blue-600 transition-all duration-300 group-hover:w-full <?= $isActivePage(['courses', 'course-detail']) ? 'w-full' : '' ?>"></span>
                </a>

                <!-- Cambridge Qualifications Dropdown -->
                <div class="relative group py-2">
                    <a class="relative py-2 text-[14px] xl:text-[15px] font-semibold whitespace-nowrap transition-colors duration-300 inline-flex items-center gap-1 <?= $isActivePage(['cambridge', 'cambridge-starters', 'cambridge-movers', 'cambridge-flyers', 'cambridge-ket', 'cambridge-pet']) ? 'text-blue-600' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('cambridge')); ?>">
                        <span><?= e(t('nav.cambridge')); ?></span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        <span class="absolute bottom-0 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-blue-600 transition-all duration-300 group-hover:w-full <?= $isActivePage(['cambridge', 'cambridge-starters', 'cambridge-movers', 'cambridge-flyers', 'cambridge-ket', 'cambridge-pet']) ? 'w-full' : '' ?>"></span>
                    </a>

                    <!-- Dropdown Panel -->
                    <div class="absolute left-1/2 -translate-x-1/2 top-full pt-2 w-72 invisible opacity-0 translate-y-2 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200 z-50 pointer-events-none group-hover:pointer-events-auto">
                        <div class="rounded-2xl bg-white p-2 shadow-2xl border border-slate-100 ring-1 ring-slate-900/5">
                            <a href="<?= e(page_url('cambridge')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-blue-600 bg-blue-50/70 hover:bg-blue-100 transition">
                                <span class="text-base">🎓</span>
                                <span><?= e(t('nav.cambridge.overview')); ?></span>
                            </a>
                            <div class="my-1 border-t border-slate-100"></div>
                            <a href="<?= e(page_url('cambridge-starters')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-100 text-amber-600 font-bold text-[10px]">PreA1</span>
                                <span><?= e(t('nav.cambridge.starters')); ?></span>
                            </a>
                            <a href="<?= e(page_url('cambridge-movers')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 font-bold text-[10px]">A1</span>
                                <span><?= e(t('nav.cambridge.movers')); ?></span>
                            </a>
                            <a href="<?= e(page_url('cambridge-flyers')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-sky-100 text-sky-600 font-bold text-[10px]">A2</span>
                                <span><?= e(t('nav.cambridge.flyers')); ?></span>
                            </a>
                            <a href="<?= e(page_url('cambridge-ket')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 font-bold text-[10px]">A2</span>
                                <span><?= e(t('nav.cambridge.ket')); ?></span>
                            </a>
                            <a href="<?= e(page_url('cambridge-pet')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-purple-50 hover:text-purple-700 transition">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-purple-100 text-purple-600 font-bold text-[10px]">B1</span>
                                <span><?= e(t('nav.cambridge.pet')); ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Discover / Khám phá Dropdown -->
                <div class="relative group py-2">
                    <button type="button" class="relative py-2 text-[14px] xl:text-[15px] font-semibold whitespace-nowrap transition-colors duration-300 inline-flex items-center gap-1 <?= $isActivePage(['teacher-introduce', 'teacher-detail', 'activities-home', 'activities-home-detail', 'gallery', 'documents']) ? 'text-blue-600' : 'text-slate-700 hover:text-blue-600' ?>">
                        <span><?= e(t('nav.discover')); ?></span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        <span class="absolute bottom-0 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-blue-600 transition-all duration-300 group-hover:w-full <?= $isActivePage(['teacher-introduce', 'teacher-detail', 'activities-home', 'activities-home-detail', 'gallery', 'documents']) ? 'w-full' : '' ?>"></span>
                    </button>

                    <!-- Dropdown Panel -->
                    <div class="absolute right-0 xl:left-1/2 xl:-translate-x-1/2 top-full pt-2 w-64 invisible opacity-0 translate-y-2 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200 z-50 pointer-events-none group-hover:pointer-events-auto">
                        <div class="rounded-2xl bg-white p-2 shadow-2xl border border-slate-100 ring-1 ring-slate-900/5">
                            <a href="<?= e(page_url('teacher-introduce')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600 text-sm">👨‍🏫</span>
                                <span><?= e(t('nav.teachers')); ?></span>
                            </a>
                            <a href="<?= e(page_url('activities-home')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-rose-50 hover:text-rose-600 transition">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-600 text-sm">🎈</span>
                                <span><?= e(t('nav.activities')); ?></span>
                            </a>
                            <a href="<?= e(page_url('gallery')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 transition">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 text-sm">📸</span>
                                <span><?= e(t('nav.gallery')); ?></span>
                            </a>
                            <a href="<?= e(page_url('documents')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-amber-50 hover:text-amber-600 transition">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-600 text-sm">📄</span>
                                <span><?= e(t('nav.documents')); ?></span>
                            </a>
                            <a href="<?= e(page_url('faq')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-purple-50 hover:text-purple-600 transition">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 text-purple-600 text-sm">❓</span>
                                <span>Hỏi Đáp (FAQ)</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tuyển dụng Standalone -->
                <a class="relative py-2 text-[14px] xl:text-[15px] font-semibold whitespace-nowrap transition-colors duration-300 <?= $isActivePage(['job-apply']) ? 'text-blue-600' : 'text-slate-700 hover:text-blue-600' ?> group" href="<?= e(page_url('job-apply')); ?>">
                    <?= e(t('nav.jobs')); ?>
                    <span class="absolute bottom-0 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-blue-600 transition-all duration-300 group-hover:w-full <?= $isActivePage(['job-apply']) ? 'w-full' : '' ?>"></span>
                </a>
            </nav>

            <div class="hidden min-w-0 flex-none items-center gap-4 xl:gap-5 lg:flex">
                
                <div class="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-white p-1 text-[13px] font-bold shadow-[0_4px_14px_rgba(15,23,42,0.08)]" aria-label="<?= e(t('locale.language')); ?>">
                    <a class="rounded-full px-3.5 py-1.5 transition-all <?= $currentLocale === 'vi' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>" href="<?= e(localized_current_url('vi')); ?>" title="<?= e(t('locale.switch_to', ['language' => 'Tiếng Việt'])); ?>">VI</a>
                    <a class="rounded-full px-3.5 py-1.5 transition-all <?= $currentLocale === 'en' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>" href="<?= e(localized_current_url('en')); ?>" title="<?= e(t('locale.switch_to', ['language' => 'English'])); ?>">EN</a>
                </div>

                <a href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>" class="group hidden lg:inline-flex items-center gap-2 rounded-full bg-rose-500 px-6 py-2.5 text-[13px] font-bold uppercase text-white transition-all duration-300 hover:bg-rose-600 hover:shadow-[0_8px_20px_-6px_rgba(225,29,72,0.5)] hover:-translate-y-0.5">
                    <?= e(t('nav.consultation')); ?>
                    <i class="fa-solid fa-arrow-right text-[12px] transition-transform duration-300 group-hover:translate-x-1"></i>
                </a>

                <?php if (is_logged_in()): ?>
                    <div class="flex items-center gap-3 border-l border-slate-200 pl-4">
                        <div class="relative group py-4">
                            <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-600 shadow-[0_4px_14px_rgba(15,23,42,0.08)] transition-all duration-300 hover:-translate-y-0.5 hover:border-blue-300 hover:text-blue-600 hover:shadow-md" aria-label="Thông báo" aria-haspopup="true" aria-expanded="false" title="Thông báo">
                                <span class="absolute inset-0 rounded-full bg-gradient-to-br from-blue-50 via-white to-rose-50 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                                <i class="fa-solid fa-bell relative z-10 text-[15px] <?= $headerUnreadNotificationCount > 0 ? 'text-rose-500' : 'text-slate-600'; ?>"></i>
                                
                                <?php if ($headerUnreadNotificationCount > 0): ?>
                                    <span class="absolute -right-1 -top-1 inline-flex min-w-[20px] h-[20px] items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-black leading-none text-white shadow-sm"><?= e((string) ($headerUnreadNotificationCount > 99 ? '99+' : $headerUnreadNotificationCount)); ?></span>
                                    <span class="absolute -right-1 -top-1 w-[20px] h-[20px] rounded-full bg-rose-400 animate-ping opacity-75"></span>
                                <?php endif; ?>
                            </button>

                            <div class="absolute right-0 top-full z-50 mt-1 w-80 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                                <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.12)]">
                                    <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4">
                                        <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
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
                                            <div class="px-5 py-6 text-sm text-slate-500 text-center">Chưa có thông báo nào.</div>
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
                                                <a href="<?= e($notificationActionUrl); ?>" data-notification-id="<?= $notificationId; ?>" onclick="try { sessionStorage.setItem('studentNotificationHighlightId', '<?= $notificationId; ?>'); } catch (error) {}" class="block border-b border-slate-50 px-5 py-4 transition-colors hover:bg-slate-50 <?= $notificationIsRead ? 'text-slate-600' : 'bg-blue-50/30 text-slate-900'; ?>">
                                                    <div class="flex items-start gap-3">
                                                        <span class="mt-1.5 inline-flex h-2 w-2 shrink-0 rounded-full <?= $notificationIsRead ? 'bg-slate-300' : 'bg-rose-500'; ?>"></span>
                                                        <div class="min-w-0">
                                                            <div class="text-[13px] leading-snug <?= $notificationIsRead ? 'font-medium' : 'font-bold'; ?>"><?= e($notificationTitle); ?></div>
                                                            <?php if ($notificationMessage !== ''): ?>
                                                                <div class="mt-1 text-xs leading-relaxed <?= $notificationIsRead ? 'text-slate-400' : 'font-medium text-slate-600'; ?>"><?= e($notificationMessage); ?></div>
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
                            <button class="inline-flex flex-none items-center gap-2.5 whitespace-nowrap text-[14px] font-semibold transition-colors <?= $isActivePage(['feedback', 'dashboard-student', 'classes-my', 'activities-student', 'admin']) || $isStudentProfileActive ? 'text-blue-600' : 'text-slate-700 hover:text-blue-600' ?>">
                                <?php if ($headerUserAvatarUrl !== ''): ?>
                                    <img src="<?= e($headerUserAvatarUrl); ?>" alt="<?= e($headerUserDisplayName !== '' ? $headerUserDisplayName : 'User avatar'); ?>" class="h-9 w-9 rounded-full object-cover border border-slate-200 shadow-sm">
                                <?php else: ?>
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 shadow-sm text-white flex items-center justify-center text-sm font-bold">
                                        <?= e($headerUserInitial); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="text-left hidden xl:block"><?= e(t('nav.account_short')); ?></span>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180 <?= $isActivePage(['feedback', 'dashboard-student', 'classes-my', 'activities-student', 'admin']) || $isStudentProfileActive ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-600' ?>"></i>
                            </button>
                            
                            <div class="absolute right-0 top-full z-50 w-60 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                                <div class="rounded-xl border border-slate-100 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] py-2">
                                    <?php if (can_access_page('profile')): ?><a class="block px-5 py-2.5 text-[14px] font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('profile')); ?>"><?= e(t('nav.profile')); ?></a><?php endif; ?>
                                    <?php if (can_access_page('profile')): ?><a class="block px-5 py-2.5 text-[14px] font-medium transition-colors hover:bg-slate-50 <?= $isActivePage(['profile']) && isset($_GET['open_password']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('profile', ['open_password' => 1])); ?>"><?= e(t('nav.change_password')); ?></a><?php endif; ?>
                                    <?php if (can_access_page('feedback')): ?><a class="block px-5 py-2.5 text-[14px] font-medium transition-colors hover:bg-slate-50 <?= $isActivePage(['feedback']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('feedback')); ?>"><?= e(t('nav.feedback')); ?></a><?php endif; ?>
                                    
                                    <?php if ($isStudentUser): ?>
                                        <div class="h-px bg-slate-100 my-1"></div>
                                        <?php if (can_access_page('dashboard-student')): ?><a class="block px-5 py-2.5 text-[14px] font-medium transition-colors hover:bg-slate-50 <?= $isStudentPanelTabActive('dashboard-student') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.schedule')); ?></a><?php endif; ?>
                                        <?php if (can_access_page('classes-my')): ?><a class="block px-5 py-2.5 text-[14px] font-medium transition-colors hover:bg-slate-50 <?= $isStudentPanelTabActive('classes-my') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('classes-my')); ?>"><?= e(t('nav.my_classes')); ?></a><?php endif; ?>
                                        <?php if (can_access_page('activities-student')): ?><a class="block px-5 py-2.5 text-[14px] font-medium transition-colors hover:bg-slate-50 <?= $isStudentPanelTabActive('activities-student') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('activities-student')); ?>"><?= e(t('nav.student_activities')); ?></a><?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (can_access_page('dashboard-student')): ?><a class="block px-5 py-2.5 text-[14px] font-medium transition-colors hover:bg-slate-50 <?= $isStudentProfileActive ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.student_page')); ?></a><?php endif; ?>
                                    <?php if (can_access_page('admin')): ?><a class="block px-5 py-2.5 text-[14px] font-medium transition-colors hover:bg-slate-50 <?= $isActivePage(['admin', 'dashboard-admin', 'custom-ui-admin', 'users-admin', 'tuition-finance', 'registration-finance', 'promotions-manage', 'payments-finance', 'feedbacks-manage', 'student-leads-manage', 'job-applications-manage', 'approvals-manage', 'activities-manage', 'rooms-manage', 'notifications-manage', 'bank-manage']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700 hover:text-blue-600' ?>" href="<?= e(page_url('admin')); ?>"><?= e(t('nav.admin')); ?></a><?php endif; ?>
                                    
                                    <div class="h-px bg-slate-100 my-1"></div>
                                    <a class="block px-5 py-2.5 text-[14px] font-medium text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors" href="<?= e(page_url('logout')); ?>"><?= e(t('nav.logout')); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= e(page_url('login')); ?>" class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full bg-slate-800 px-6 py-2.5 text-[13px] font-bold uppercase text-white transition-all hover:bg-slate-900 hover:shadow-lg">
                        <?= e(t('nav.login')); ?>
                    </a>
                <?php endif; ?>
            </div>

            <button id="mobile-menu-toggle" class="inline-flex h-10 w-10 flex-col items-center justify-center gap-1.5 text-slate-800 hover:text-blue-600 lg:hidden" type="button">
                <span class="block h-[2px] w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-[2px] w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-[2px] w-6 bg-current rounded-full transition-all"></span>
            </button>

            <nav id="main-nav" class="absolute left-0 right-0 top-full z-50 hidden flex-col border-t border-slate-100 bg-white shadow-xl lg:hidden origin-top" aria-label="<?= e(t('nav.mobile')); ?>">
                <div class="flex items-center justify-center gap-2 border-b border-slate-50 px-6 py-4 text-[13px] font-bold" aria-label="<?= e(t('locale.language')); ?>">
                    <a class="rounded-full px-4 py-1.5 <?= $currentLocale === 'vi' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'; ?>" href="<?= e(localized_current_url('vi')); ?>"><?= e(t('locale.vi')); ?></a>
                    <a class="rounded-full px-4 py-1.5 <?= $currentLocale === 'en' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'; ?>" href="<?= e(localized_current_url('en')); ?>"><?= e(t('locale.en')); ?></a>
                </div>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-semibold hover:bg-slate-50 <?= $isActivePage(['home']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700' ?>" href="/"><?= e(t('nav.home')); ?></a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-semibold hover:bg-slate-50 <?= $isActivePage(['courses', 'course-detail']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700' ?>" href="<?= e(page_url('courses')); ?>"><?= e(t('nav.courses')); ?></a>
                
                <!-- Mobile Cambridge Submenu -->
                <div class="border-b border-slate-50 bg-slate-50/60 px-6 py-3">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2"><?= e(t('nav.cambridge')); ?></p>
                    <div class="grid gap-2 pl-2">
                        <a href="<?= e(page_url('cambridge')); ?>" class="text-[13px] font-semibold text-blue-600 hover:underline">🎓 <?= e(t('nav.cambridge.overview')); ?></a>
                        <a href="<?= e(page_url('cambridge-starters')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">🟡 <?= e(t('nav.cambridge.starters')); ?></a>
                        <a href="<?= e(page_url('cambridge-movers')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">🟢 <?= e(t('nav.cambridge.movers')); ?></a>
                        <a href="<?= e(page_url('cambridge-flyers')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">🔵 <?= e(t('nav.cambridge.flyers')); ?></a>
                        <a href="<?= e(page_url('cambridge-ket')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">🟣 <?= e(t('nav.cambridge.ket')); ?></a>
                        <a href="<?= e(page_url('cambridge-pet')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">🔴 <?= e(t('nav.cambridge.pet')); ?></a>
                    </div>
                </div>

                <!-- Mobile Discover Submenu -->
                <div class="border-b border-slate-50 bg-slate-50/40 px-6 py-3">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2"><?= e(t('nav.discover')); ?></p>
                    <div class="grid gap-2.5 pl-2">
                        <a href="<?= e(page_url('teacher-introduce')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">👨‍🏫 <?= e(t('nav.teachers')); ?></a>
                        <a href="<?= e(page_url('activities-home')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">🎈 <?= e(t('nav.activities')); ?></a>
                        <a href="<?= e(page_url('gallery')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">📸 <?= e(t('nav.gallery')); ?></a>
                        <a href="<?= e(page_url('documents')); ?>" class="text-[13px] font-medium text-slate-700 hover:text-blue-600">📄 <?= e(t('nav.documents')); ?></a>
                    </div>
                </div>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-semibold hover:bg-slate-50 <?= $isActivePage(['job-apply']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700' ?>" href="<?= e(page_url('job-apply')); ?>"><?= e(t('nav.jobs')); ?></a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-semibold hover:bg-slate-50 <?= $isActivePage(['home']) ? 'text-blue-600 bg-blue-50/50' : 'text-slate-700' ?>" href="<?= e(page_url('home') . '#dang-ky-tu-van'); ?>"><?= e(t('nav.consultation')); ?></a>
                
                <?php if (is_logged_in()): ?>
                    <div class="bg-slate-50 px-6 py-5">
                        <p class="text-[12px] font-bold text-slate-400 uppercase tracking-wider mb-3"><?= e(t('nav.account', ['name' => (string) ($headerUser['full_name'] ?? 'Khách')])); ?></p>
                        <div class="grid gap-3">
                            <a class="text-[14px] font-semibold text-slate-700 hover:text-blue-600" href="<?= e(page_url('profile')); ?>"><?= e(t('nav.profile')); ?></a>
                            <a class="text-[14px] font-semibold <?= $isActivePage(['profile']) && isset($_GET['open_password']) ? 'text-blue-600' : 'text-slate-700' ?>" href="<?= e(page_url('profile', ['open_password' => 1])); ?>"><?= e(t('nav.change_password')); ?></a>
                            <a class="text-[14px] font-semibold <?= $isActivePage(['feedback']) ? 'text-blue-600' : 'text-slate-700' ?>" href="<?= e(page_url('feedback')); ?>"><?= e(t('nav.feedback')); ?></a>
                            <?php if ($isStudentUser): ?>
                                <a class="text-[14px] font-semibold <?= $isStudentPanelTabActive('dashboard-student') ? 'text-blue-600' : 'text-slate-700' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.schedule')); ?></a>
                                <a class="text-[14px] font-semibold <?= $isStudentPanelTabActive('classes-my') ? 'text-blue-600' : 'text-slate-700' ?>" href="<?= e(page_url('classes-my')); ?>"><?= e(t('nav.my_classes')); ?></a>
                                <a class="text-[14px] font-semibold <?= $isStudentPanelTabActive('activities-student') ? 'text-blue-600' : 'text-slate-700' ?>" href="<?= e(page_url('activities-student')); ?>"><?= e(t('nav.student_activities')); ?></a>
                            <?php endif; ?>
                            <a class="text-[14px] font-semibold <?= $isStudentProfileActive ? 'text-blue-600' : 'text-slate-700' ?>" href="<?= e(page_url('dashboard-student')); ?>"><?= e(t('nav.student_page')); ?></a>
                            <div class="h-px bg-slate-200 my-1 w-full"></div>
                            <a class="text-[14px] font-semibold text-rose-500" href="<?= e(page_url('logout')); ?>"><?= e(t('nav.logout')); ?></a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-6">
                        <a href="<?= e(page_url('login')); ?>" class="flex w-full items-center justify-center gap-2 rounded-full bg-slate-800 px-6 py-3.5 text-[14px] font-bold uppercase text-white hover:bg-slate-900 shadow-md">
                            <?= e(t('nav.login')); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="flex-grow">
