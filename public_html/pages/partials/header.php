<!doctype html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nền tảng Trung tâm Anh ngữ</title>
    <meta name="description" content="Nền tảng quản lý trung tâm tiếng Anh: marketing, portal học viên và quản trị vận hành toàn diện.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php require_once __DIR__ . '/tailwind_cdn.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #bae6fd; border-radius: 10px; } 
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-sans leading-relaxed text-slate-800 antialiased flex flex-col">
    
    <header class="sticky top-0 z-50 w-full bg-white shadow-[0_2px_15px_rgba(0,0,0,0.04)]" id="top">    
        <div class="mx-auto w-full max-w-[1450px] px-4 sm:px-6 flex min-h-[85px] items-center justify-between gap-4">
            
            <div class="flex-none flex items-center overflow-visible">
                <a href="/" class="inline-flex items-center overflow-visible -my-3">
                    <img src="assets/images/logo_remove.png" alt="Logo" class="h-20 md:h-24 w-auto max-w-none object-contain -mb-2">
                </a>
            </div>

            <nav class="hidden flex-1 items-center justify-center gap-8 lg:flex lg:gap-10" aria-label="Menu chính">
                <a class="text-[16px] font-extrabold text-slate-800 hover:text-[#27318b] transition-colors" href="/">
                    Trang chủ
                </a>
                
                <div class="relative group py-6">
                    <a class="inline-flex items-center gap-1.5 text-[16px] font-extrabold text-slate-800 hover:text-[#27318b] transition-colors cursor-pointer" href="#khoa-hoc">
                        Chương trình học
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-800 group-hover:text-[#27318b] transition-transform duration-300 group-hover:rotate-180"></i>
                    </a>
                </div>

                <a class="text-[16px] font-extrabold text-slate-800 hover:text-[#27318b] transition-colors" href="#giao-vien">
                    Giáo viên
                </a>
                
                <div class="relative group py-6">
                    <button class="inline-flex items-center gap-1.5 text-[16px] font-extrabold text-slate-800 hover:text-[#27318b] transition-colors" type="button">
                        Hệ thống
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-800 group-hover:text-[#27318b] transition-transform duration-300 group-hover:rotate-180"></i>
                    </button>
                    <div class="absolute left-1/2 -translate-x-1/2 top-full z-50 w-56 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                        <div class="rounded-xl border border-slate-100 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] py-2">
                            <a class="block px-5 py-2.5 text-[15px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b] transition-colors" href="#portal">Cổng học tập</a>
                            <a class="block px-5 py-2.5 text-[15px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b] transition-colors" href="#quan-tri">Quản trị vận hành</a>
                        </div>
                    </div>
                </div>
            </nav>

			<!-- <div class="hidden items-center gap-2 lg:flex">
				<?php if (is_logged_in()): ?>
					<div class="relative group">
						<button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700" type="button" aria-haspopup="true">Menu</button>
						<div class="invisible absolute right-0 top-full z-50 mt-2 min-w-56 rounded-xl border border-slate-200 bg-white p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
							<?php if (can_access_page('profile')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('profile')); ?>">Trang cá nhân</a>
							<?php endif; ?>
							<?php if (can_access_page('dashboard-student')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('dashboard-student')); ?>">Bảng điều khiển học viên</a>
							<?php endif; ?>
							<?php if (can_access_page('dashboard-teacher')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('dashboard-teacher')); ?>">Bảng điều khiển giáo viên</a>
							<?php endif; ?>
							<?php if (can_access_page('portfolios-academic')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('portfolios-academic')); ?>">Portfolio</a>
							<?php endif; ?>
							<?php if (can_access_page('dashboard-admin')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin">Quản trị</a>
							<?php endif; ?>
							<?php if (can_access_page('users-admin')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin/users">Quản lý người dùng</a>
							<?php endif; ?>
							<?php if (can_access_page('tuition-finance')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('tuition-finance')); ?>">Học phí</a>
							<?php endif; ?>
							<?php if (can_access_page('payments-finance')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('payments-finance')); ?>">Thanh toán</a>
							<?php endif; ?>
							<?php if (can_access_page('feedbacks-manage')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage')); ?>">Đánh giá</a>
							<?php endif; ?>
							<?php if (can_access_page('student-leads-manage')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('student-leads-manage')); ?>">Lead học viên</a>
							<?php endif; ?>
							<?php if (can_access_page('job-applications-manage')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('job-applications-manage')); ?>">Ứng tuyển giáo viên</a>
							<?php endif; ?>
							<?php if (can_access_page('approvals-manage')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('approvals-manage')); ?>">Phê duyệt</a>
							<?php endif; ?>
							<?php if (can_access_page('activities-manage')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('activities-manage')); ?>">Hoạt động</a>
							<?php endif; ?>
							<?php if (can_access_page('bank-manage')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('bank-manage')); ?>">Ngân hàng</a>
							<?php endif; ?>
							<?php if (can_access_page('courses-academic')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('courses-academic')); ?>">Khóa học</a>
							<?php endif; ?>
							<?php if (can_access_page('roadmaps-academic')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('roadmaps-academic')); ?>">Roadmap khóa học</a>
							<?php endif; ?>
							<?php if (can_access_page('classes-academic')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('classes-academic')); ?>">Lớp học</a>
							<?php endif; ?>
							<?php if (can_access_page('schedules-academic')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('schedules-academic')); ?>">Lịch dạy</a>
							<?php endif; ?>
							<?php if (can_access_page('assignments-academic')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('assignments-academic')); ?>">Bài tập</a>
							<?php endif; ?>
							<?php if (can_access_page('materials-academic')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('materials-academic')); ?>">Tài liệu</a>
							<?php endif; ?>
							<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('logout')); ?>">Đăng xuất</a>
						</div>
					</div>
					<a href="#lien-he" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-2 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Đăng ký kiểm tra</a>
				<?php else: ?>
					<a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('login')); ?>">Đăng nhập</a>
					<a href="#lien-he" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-2 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Đăng ký kiểm tra</a>
				<?php endif; ?>
			</div>

			<button id="mobile-menu-toggle" class="inline-flex h-10 w-10 flex-col items-center justify-center gap-1 rounded-lg border border-slate-200 bg-white lg:hidden" type="button" aria-label="Mở menu" aria-expanded="false">
				<span class="block h-0.5 w-4 bg-slate-700"></span>
				<span class="block h-0.5 w-4 bg-slate-700"></span>
				<span class="block h-0.5 w-4 bg-slate-700"></span>
			</button>
			<nav id="main-nav" class="absolute right-4 top-[74px] z-40 hidden w-[min(92vw,360px)] flex-col gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-lg" aria-label="Menu mobile">
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/">Trang chủ</a>
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#khoa-hoc">Khóa học</a>
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#giao-vien">Giáo viên</a>
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#portal">Cổng học tập</a>
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#quan-tri">Quản trị</a>
				<?php if (is_logged_in()): ?>
					<?php if (can_access_page('profile')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('profile')); ?>">Trang cá nhân</a><?php endif; ?>
					<?php if (can_access_page('dashboard-student')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('dashboard-student')); ?>">Bảng điều khiển học viên</a><?php endif; ?>
					<?php if (can_access_page('dashboard-teacher')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('dashboard-teacher')); ?>">Bảng điều khiển giáo viên</a><?php endif; ?>
					<?php if (can_access_page('portfolios-academic')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('portfolios-academic')); ?>">Portfolio</a><?php endif; ?>
					<?php if (can_access_page('dashboard-admin')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin">Quản trị</a>
					<?php endif; ?>
					<?php if (can_access_page('users-admin')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin/users">Quản lý người dùng</a>
					<?php endif; ?>
					<?php if (can_access_page('tuition-finance')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('tuition-finance')); ?>">Học phí</a>
					<?php endif; ?>
					<?php if (can_access_page('payments-finance')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('payments-finance')); ?>">Thanh toán</a>
					<?php endif; ?>
					<?php if (can_access_page('feedbacks-manage')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage')); ?>">Đánh giá</a>
					<?php endif; ?>
					<?php if (can_access_page('student-leads-manage')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('student-leads-manage')); ?>">Lead học viên</a>
					<?php endif; ?>
					<?php if (can_access_page('job-applications-manage')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('job-applications-manage')); ?>">Ứng tuyển giáo viên</a>
					<?php endif; ?>
					<?php if (can_access_page('approvals-manage')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('approvals-manage')); ?>">Phê duyệt</a>
					<?php endif; ?>
					<?php if (can_access_page('activities-manage')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('activities-manage')); ?>">Hoạt động ngoại khóa</a>
					<?php endif; ?>
					<?php if (can_access_page('bank-manage')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('bank-manage')); ?>">Tài khoản ngân hàng</a>
					<?php endif; ?>
					<?php if (can_access_page('courses-academic')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('courses-academic')); ?>">Khóa học</a>
					<?php endif; ?>
					<?php if (can_access_page('roadmaps-academic')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('roadmaps-academic')); ?>">Roadmap khóa học</a>
					<?php endif; ?>
					<?php if (can_access_page('classes-academic')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('classes-academic')); ?>">Lớp học</a>
					<?php endif; ?>
					<?php if (can_access_page('schedules-academic')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('schedules-academic')); ?>">Lịch dạy</a>
					<?php endif; ?>
					<?php if (can_access_page('assignments-academic')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('assignments-academic')); ?>">Bài tập</a>
					<?php endif; ?>
					<?php if (can_access_page('materials-academic')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('materials-academic')); ?>">Tài liệu</a>
					<?php endif; ?>
					<a href="<?= e(page_url('logout')); ?>" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700">Đăng xuất</a>
				<?php else: ?>
					<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('login')); ?>">Đăng nhập</a>
					<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="<?= e(page_url('login')); ?>">Cổng học viên</a>
				<?php endif; ?>
				<a href="#lien-he" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-2 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Đăng ký kiểm tra đầu vào</a>
			</nav>
		</div>
	</header> -->
            <!-- <div class="hidden items-center gap-4 lg:flex"> -->
            <div class="hidden items-center gap-5 lg:flex pb-1">
            <div class="hidden items-center gap-6 lg:flex">
                <?php if (is_logged_in()): ?>
                    <div class="relative group py-6">
                        <button class="inline-flex items-center gap-2.5 text-[16px] font-extrabold text-slate-800 hover:text-[#27318b] transition-colors">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-red-500 to-green-500 text-white flex items-center justify-center text-sm font-black">
                                <?= substr($user['full_name'] ?? 'U', 0, 1) ?>
                            </div>
                            <span class="max-w-[120px] truncate"><?= e($user['full_name'] ?? 'Tài khoản') ?></span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-800 group-hover:text-[#27318b] transition-transform duration-300 group-hover:rotate-180"></i>
                        </button>
                        
                        <div class="absolute right-0 top-full z-50 w-64 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-200">
                            <div class="rounded-xl border border-slate-100 bg-white shadow-[0_10px_30px_rgba(0,0,0,0.08)] py-2">
                                <?php if (can_access_page('profile')): ?><a class="block px-5 py-2.5 text-[14px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b]" href="<?= e(page_url('profile')); ?>">Trang cá nhân</a><?php endif; ?>
                                <?php if (can_access_page('dashboard-student')): ?><a class="block px-5 py-2.5 text-[14px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b]" href="<?= e(page_url('dashboard-student')); ?>">Dashboard Học viên</a><?php endif; ?>
                                <?php if (can_access_page('dashboard-teacher')): ?><a class="block px-5 py-2.5 text-[14px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b]" href="<?= e(page_url('dashboard-teacher')); ?>">Dashboard Giáo viên</a><?php endif; ?>
                                <?php if (can_access_page('portfolios-academic')): ?><a class="block px-5 py-2.5 text-[14px] font-bold text-slate-700 hover:bg-slate-50 hover:text-[#27318b]" href="<?= e(page_url('portfolios-academic')); ?>">Portfolio</a><?php endif; ?>
                                <?php if (can_access_page('dashboard-admin')): ?><a class="block px-5 py-2.5 text-[14px] font-bold text-[#27318b] bg-blue-50/50 hover:bg-blue-50" href="/admin">Quản trị hệ thống</a><?php endif; ?>
                                <div class="h-px bg-slate-100 my-1"></div>
                                <a class="block px-5 py-2.5 text-[14px] font-bold text-rose-600 hover:bg-rose-50" href="<?= e(page_url('logout')); ?>">Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="text-[16px] font-extrabold text-slate-800 hover:text-[#27318b] transition-colors" href="<?= e(page_url('login')); ?>">Đăng nhập</a>
                <?php endif; ?>
                
                <a href="<?= e(page_url('register')); ?>" class="group inline-flex items-center gap-3 rounded-full bg-red-600 px-7 py-3 text-[15px] font-black uppercase text-white transition-all hover:bg-red-700 hover:shadow-lg">
                    ĐĂNG KÝ NGAY
                    <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                </a>
            </div>

            <button id="mobile-menu-toggle" class="inline-flex h-10 w-10 flex-col items-center justify-center gap-1.5 text-slate-800 hover:text-[#27318b] lg:hidden" type="button">
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
            </button>

            <nav id="main-nav" class="absolute left-0 right-0 top-full z-50 hidden flex-col border-t border-slate-100 bg-white shadow-xl lg:hidden origin-top" aria-label="Menu mobile">
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold text-slate-800 hover:bg-slate-50" href="/">Trang chủ</a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold text-slate-800 hover:bg-slate-50" href="#khoa-hoc">Chương trình học</a>
                <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold text-slate-800 hover:bg-slate-50" href="#giao-vien">Giáo viên</a>
                
                <?php if (is_logged_in()): ?>
                    <div class="bg-slate-50 px-6 py-4">
                        <p class="text-[13px] font-bold text-slate-500 uppercase mb-2">Tài khoản: <?= e($user['full_name'] ?? 'Guest') ?></p>
                        <div class="grid gap-2">
                            <a class="text-[15px] font-bold text-[#27318b]" href="<?= e(page_url('profile')); ?>">Trang cá nhân</a>
                            <a class="text-[15px] font-bold text-rose-600" href="<?= e(page_url('logout')); ?>">Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="block border-b border-slate-50 px-6 py-4 text-[15px] font-bold text-slate-800 hover:bg-slate-50" href="<?= e(page_url('login')); ?>">Đăng nhập</a>
                    <div class="p-6">
                        <a href="<?= e(page_url('register')); ?>" class="flex w-full items-center justify-center gap-3 rounded-full bg-red-600 px-6 py-3.5 text-[15px] font-black uppercase text-white hover:bg-red-700">
                            ĐĂNG KÝ NGAY
                            <span class="w-2.5 h-2.5 rounded-full bg-white/90"></span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="flex-grow">
