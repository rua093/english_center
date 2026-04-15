<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e((string) ($adminTitle ?? 'Khu vực quản trị')); ?> | Trung tâm Anh ngữ</title>
    <meta name="description" content="Khu vực điều hành nội bộ cho Admin và Staff.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <?php require_once __DIR__ . '/tailwind_cdn.php'; ?>
</head>
<body class="min-h-screen bg-slate-100 font-sans leading-relaxed text-slate-900">
<?php
$adminUser = auth_user();
$activeModule = (string) ($module ?? '');
$adminNavBaseClass = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
?>
<div class="grid min-h-screen grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)]">
    <aside class="border-b border-slate-200 bg-white p-4 lg:sticky lg:top-0 lg:h-screen lg:border-b-0 lg:border-r">
        <a class="mb-3 inline-flex items-center gap-2 font-extrabold" href="<?= e(page_url('dashboard-admin')); ?>">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-blue-700 text-xs font-extrabold text-white">EC</span>
            <span class="text-sm font-extrabold text-slate-800">Khu vực điều hành</span>
        </a>

        <div class="mb-4 grid gap-0.5 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <strong><?= e((string) ($adminUser['full_name'] ?? '')); ?></strong>
            <small><?= strtoupper((string) ($adminUser['role'] ?? '')); ?></small>
        </div>

        <nav class="grid gap-1" aria-label="Menu quản trị">
            <?php if (can_access_page('dashboard-admin')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'dashboard' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('dashboard-admin')); ?>">Tổng quan</a>
            <?php endif; ?>
            <?php if (can_access_page('tuition-finance')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'tuition' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('tuition-finance')); ?>">Học phí</a>
            <?php endif; ?>
            <?php if (can_access_page('payments-finance')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'payments' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('payments-finance')); ?>">Thanh toán</a>
            <?php endif; ?>
            <?php if (can_access_page('users-admin')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'users' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="/admin/users">Người dùng</a>
            <?php endif; ?>
            <?php if (can_access_page('approvals-manage')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'approvals' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('approvals-manage')); ?>">Phê duyệt</a>
            <?php endif; ?>
            <?php if (can_access_page('feedbacks-manage')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'feedbacks' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('feedbacks-manage')); ?>">Đánh giá</a>
            <?php endif; ?>
            <?php if (can_access_page('activities-manage')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'activities' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('activities-manage')); ?>">Hoạt động</a>
            <?php endif; ?>
            <?php if (can_access_page('bank-manage')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'bank' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('bank-manage')); ?>">Ngân hàng</a>
            <?php endif; ?>
            <?php if (can_access_page('classes-academic')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'classes' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('classes-academic')); ?>">Lớp học</a>
            <?php endif; ?>
            <?php if (can_access_page('schedules-academic')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'schedules' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('schedules-academic')); ?>">Lịch học</a>
            <?php endif; ?>
            <?php if (can_access_page('assignments-academic')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'assignments' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('assignments-academic')); ?>">Bài tập</a>
            <?php endif; ?>
            <?php if (can_access_page('materials-academic')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'materials' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('materials-academic')); ?>">Tài liệu</a>
            <?php endif; ?>
            <?php if (can_access_page('submissions-academic')): ?>
                <a class="<?= $adminNavBaseClass; ?><?= $activeModule === 'submissions' ? ' border-blue-200 bg-blue-50 text-blue-700' : ''; ?>" href="<?= e(page_url('submissions-academic')); ?>">Bài nộp</a>
            <?php endif; ?>
        </nav>

        <div class="mt-4 grid gap-2">
            <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('home')); ?>">Về trang chủ</a>
            <a class="<?= ui_btn_primary_classes('sm'); ?>" href="<?= e(page_url('logout')); ?>">Đăng xuất</a>
        </div>
    </aside>

    <main class="p-4 md:p-6">
        <header class="mb-4 flex items-center justify-between gap-3">
            <h1><?= e((string) ($adminTitle ?? 'Khu vực quản trị')); ?></h1>
            <a class="rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-blue-700" href="<?= e(page_url('profile')); ?>">Hồ sơ</a>
        </header>
        <div class="grid gap-4">