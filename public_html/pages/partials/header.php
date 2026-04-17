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
        /* Nâng cấp style active cho menu đồng bộ với thiết kế mới */
        .nav-link-active { @apply text-blue-600 bg-white shadow-[0_2px_15px_-3px_rgba(14,165,233,0.15)]; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #bae6fd; border-radius: 10px; } /* Đổi thanh cuộn sang màu xanh nhạt */
    </style>
</head>
<body class="min-h-screen bg-slate-50 font-sans leading-relaxed text-slate-800 antialiased flex flex-col">
    <header class="sticky top-0 z-50 border-b border-white/40 bg-gradient-to-r from-blue-200/100 via-sky-200/100 to-blue-300/100 backdrop-blur-2xl shadow-[0_10px_30px_-10px_rgba(14,165,233,0.25)] transition-all duration-500" id="top">    
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 flex min-h-[85px] items-center justify-between gap-4">
            
            <div class="flex-none">
                <a class="inline-flex items-center gap-3.5 group" href="#top">
                    <div class="relative">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-[1.2rem] bg-gradient-to-br from-sky-400 to-blue-600 text-lg font-display font-black text-white shadow-lg shadow-sky-500/30 transition-all duration-300 group-hover:scale-110 group-hover:-rotate-3">EC</span>
                        <div class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-emerald-400 border-2 border-white shadow-sm"></div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-display font-black text-slate-800 leading-none tracking-tight group-hover:text-blue-600 transition-colors duration-300">NHUỆ MINH</span>
                        <span class="text-[10px] font-extrabold text-sky-500 tracking-[0.15em] uppercase mt-1">Language Center</span>
                    </div>
                </a>
            </div>

            <nav class="hidden flex-1 items-center justify-center gap-2 lg:flex" aria-label="Menu chính">
                <a class="relative rounded-full px-5 py-2.5 text-base font-bold text-slate-600 transition-all duration-300 hover:bg-white hover:text-blue-600 hover:shadow-[0_2px_15px_-3px_rgba(14,165,233,0.15)]" href="/">
                    Trang chủ
                </a>
                <a class="relative rounded-full px-5 py-2.5 text-base font-bold text-slate-600 transition-all duration-300 hover:bg-white hover:text-blue-600 hover:shadow-[0_2px_15px_-3px_rgba(14,165,233,0.15)]" href="#khoa-hoc">
                    Khóa học
                </a>
                <a class="relative rounded-full px-5 py-2.5 text-base font-bold text-slate-600 transition-all duration-300 hover:bg-white hover:text-blue-600 hover:shadow-[0_2px_15px_-3px_rgba(14,165,233,0.15)]" href="#giao-vien">
                    Giáo viên
                </a>
                
                <div class="relative group">
                    <button class="inline-flex items-center gap-1.5 rounded-full px-5 py-2.5 text-base font-bold text-slate-600 transition-all duration-300 group-hover:bg-white group-hover:text-blue-600 group-hover:shadow-[0_2px_15px_-3px_rgba(14,165,233,0.15)]" type="button">
                        Hệ thống
                        <svg class="w-4 h-4 text-sky-400 group-hover:rotate-180 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div class="absolute left-1/2 -translate-x-1/2 top-full pt-3 z-50 w-64 opacity-0 invisible translate-y-3 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300">
                        <div class="rounded-[2rem] border border-white bg-white/95 backdrop-blur-xl p-3 shadow-[0_20px_40px_-15px_rgba(14,165,233,0.2)]">
                            <a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-3 text-base font-bold text-slate-600 hover:bg-sky-50 hover:text-blue-600 transition-all duration-300" href="#portal">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100/50 text-blue-500">🎓</span> Cổng học tập
                            </a>
                            <a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-3 text-base font-bold text-slate-600 hover:bg-sky-50 hover:text-blue-600 transition-all duration-300" href="#quan-tri">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100/50 text-blue-500">⚙️</span> Quản trị vận hành
                            </a>
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
            <div class="hidden items-center gap-4 lg:flex">
                <?php if (is_logged_in()): ?>
                    <div class="relative group">
                        <button class="inline-flex items-center gap-3 rounded-full border border-white bg-white/60 p-1.5 pr-4 text-base font-bold text-slate-700 hover:border-sky-200 hover:bg-white hover:shadow-[0_4px_20px_-5px_rgba(14,165,233,0.2)] transition-all duration-300">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center text-xs font-black shadow-inner">
                                <?= substr($user['full_name'] ?? 'U', 0, 1) ?>
                            </div>
                            <span class="max-w-[100px] truncate">Tài khoản</span>
                        </button>
                        <div class="absolute right-0 top-full pt-3 z-50 w-72 opacity-0 invisible translate-y-3 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300">
                            <div class="rounded-[2.5rem] border border-white bg-white/95 backdrop-blur-xl p-3 shadow-[0_20px_40px_-15px_rgba(14,165,233,0.2)] max-h-[75vh] overflow-y-auto custom-scrollbar">
                                <div class="px-5 py-3 border-b border-sky-50 mb-2 bg-sky-50/50 rounded-2xl">
                                    <p class="text-[10px] font-black text-sky-500 uppercase tracking-[0.2em]">Xin chào,</p>
                                    <p class="text-base font-black text-blue-950 truncate"><?= e($user['full_name'] ?? 'Học viên') ?></p>
                                </div>
                                <div class="space-y-1">
                                    <?php if (can_access_page('profile')): ?><a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-2.5 text-base font-bold text-slate-600 hover:bg-sky-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('profile')); ?>">👤 Trang cá nhân</a><?php endif; ?>
                                    <?php if (can_access_page('dashboard-student')): ?><a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-2.5 text-base font-bold text-slate-600 hover:bg-sky-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('dashboard-student')); ?>">👨‍🎓 Dashboard Học viên</a><?php endif; ?>
                                    <?php if (can_access_page('dashboard-teacher')): ?><a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-2.5 text-base font-bold text-slate-600 hover:bg-sky-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('dashboard-teacher')); ?>">👨‍🏫 Dashboard Giáo viên</a><?php endif; ?>
                                    <?php if (can_access_page('portfolios-academic')): ?><a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-2.5 text-base font-bold text-slate-600 hover:bg-sky-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('portfolios-academic')); ?>">🎨 Portfolio</a><?php endif; ?>
                                    <?php if (can_access_page('dashboard-admin')): ?><a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-2.5 text-base font-bold text-blue-600 bg-sky-50/80 hover:bg-sky-100 transition-colors" href="/admin">🛡️ Quản trị hệ thống</a><?php endif; ?>
                                    <div class="h-px bg-slate-100 my-2"></div>
                                    <a class="flex items-center gap-3 rounded-[1.2rem] px-4 py-2.5 text-base font-black text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors" href="<?= e(page_url('logout')); ?>">🚪 Đăng xuất</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="relative rounded-full px-5 py-2.5 text-base font-bold text-slate-600 transition-all duration-300 hover:bg-white hover:text-blue-600 hover:shadow-[0_2px_15px_-3px_rgba(14,165,233,0.15)]" href="<?= e(page_url('login')); ?>">Đăng nhập</a>
                <?php endif; ?>
                
                <a href="#lien-he" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-400 to-blue-600 px-7 py-3 text-base font-black text-white shadow-[0_10px_20px_rgba(14,165,233,0.2)] transition-all duration-300 hover:scale-105 hover:shadow-[0_15px_25px_rgba(14,165,233,0.4)] active:scale-95">
                    ĐĂNG KÝ NGAY
                </a>
            </div>

            <button id="mobile-menu-toggle" class="inline-flex h-12 w-12 flex-col items-center justify-center gap-1.5 rounded-[1.2rem] border border-white bg-white/60 text-blue-600 hover:bg-white hover:shadow-[0_4px_20px_-5px_rgba(14,165,233,0.2)] transition-all duration-300 lg:hidden shadow-sm" type="button">
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-0.5 w-4 bg-current rounded-full transition-all self-end"></span>
                <span class="block h-0.5 w-6 bg-current rounded-full transition-all"></span>
            </button>

            <nav id="main-nav" class="absolute right-4 left-4 top-[95px] z-50 hidden flex-col gap-1 rounded-[2.5rem] border border-white bg-gradient-to-b from-white/95 to-sky-50/95 backdrop-blur-2xl p-6 shadow-[0_20px_50px_-15px_rgba(14,165,233,0.3)] lg:hidden" aria-label="Menu mobile">
                <a class="rounded-2xl px-5 py-4 text-base font-bold text-slate-700 hover:bg-sky-50 hover:text-blue-600 transition-all" href="/">Trang chủ</a>
                <a class="rounded-2xl px-5 py-4 text-base font-bold text-slate-700 hover:bg-sky-50 hover:text-blue-600 transition-all" href="#khoa-hoc">Khóa học</a>
                <a class="rounded-2xl px-5 py-4 text-base font-bold text-slate-700 hover:bg-sky-50 hover:text-blue-600 transition-all" href="#giao-vien">Giáo viên</a>
                <div class="h-px bg-sky-100/50 my-4"></div>
                <?php if (is_logged_in()): ?>
                    <a class="rounded-2xl px-5 py-4 text-base font-bold text-blue-600 bg-sky-50" href="<?= e(page_url('profile')); ?>">👤 Trang cá nhân</a>
                    <a href="<?= e(page_url('logout')); ?>" class="mt-2 rounded-2xl px-5 py-4 text-base font-bold text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors">🚪 Đăng xuất</a>
                <?php else: ?>
                    <a class="rounded-2xl px-5 py-4 text-base font-bold text-slate-700 hover:bg-sky-50 hover:text-blue-600 transition-all" href="<?= e(page_url('login')); ?>">Đăng nhập hệ thống</a>
                <?php endif; ?>
                <a href="#lien-he" class="mt-6 inline-flex items-center justify-center rounded-full bg-gradient-to-r from-sky-400 to-blue-600 px-6 py-4 text-base font-black text-white shadow-[0_10px_20px_rgba(14,165,233,0.2)]">Đăng ký kiểm tra đầu vào</a>
            </nav>
        </div>
    </header>
    
    <main class="flex-grow">
