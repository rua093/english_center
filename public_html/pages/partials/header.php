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
    
    <header class="sticky top-0 z-50 w-full border-b-4 border-blue-300 bg-gradient-to-r from-blue-100 via-sky-100 to-blue-200 backdrop-blur-xl shadow-[0_10px_20px_rgba(30,58,138,0.1)] transition-all duration-500" id="top">    
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 flex min-h-[90px] items-center justify-between gap-4">
            
            <div class="flex-none pb-1">
                <a class="inline-flex items-center group" href="#top">
                    <div class="relative flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-2xl px-4 py-2 shadow-[0_5px_0_#93c5fd] border border-white transition-all duration-200 group-hover:-translate-y-1 group-hover:shadow-[0_8px_0_#93c5fd] active:translate-y-1 active:shadow-none">
                        
                        <img src="assets/images/logo_remove.png" alt="Nhuệ Minh Logo" class="h-14 w-auto object-contain">
                        
                    </div>
                </a>
            </div>

            <nav class="hidden flex-1 items-center justify-evenly px-4 lg:px-12 xl:px-20 lg:flex pb-1" aria-label="Menu chính">
                <a class="relative rounded-xl px-6 py-2.5 text-base font-black text-blue-900 bg-white/70 border border-white shadow-[0_4px_0_#93c5fd] transition-all duration-150 hover:-translate-y-1 hover:shadow-[0_6px_0_#93c5fd] hover:bg-white active:translate-y-1 active:shadow-none" href="/">
                    Trang chủ
                </a>
                <a class="relative rounded-xl px-6 py-2.5 text-base font-black text-blue-900 bg-white/70 border border-white shadow-[0_4px_0_#93c5fd] transition-all duration-150 hover:-translate-y-1 hover:shadow-[0_6px_0_#93c5fd] hover:bg-white active:translate-y-1 active:shadow-none" href="#khoa-hoc">
                    Khóa học
                </a>
                <a class="relative rounded-xl px-6 py-2.5 text-base font-black text-blue-900 bg-white/70 border border-white shadow-[0_4px_0_#93c5fd] transition-all duration-150 hover:-translate-y-1 hover:shadow-[0_6px_0_#93c5fd] hover:bg-white active:translate-y-1 active:shadow-none" href="#giao-vien">
                    Giáo viên
                </a>
                
                <div class="relative group">
                    <button class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-base font-black text-blue-900 bg-white/70 border border-white shadow-[0_4px_0_#93c5fd] transition-all duration-150 group-hover:-translate-y-1 group-hover:shadow-[0_6px_0_#93c5fd] group-hover:bg-white active:translate-y-1 active:shadow-none" type="button">
                        Hệ thống
                        <svg class="w-5 h-5 text-blue-500 group-hover:rotate-180 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div class="absolute left-1/2 -translate-x-1/2 top-full pt-5 z-50 w-64 opacity-0 invisible translate-y-3 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300">
                        <div class="rounded-[2rem] border-2 border-blue-200 bg-white/95 backdrop-blur-xl p-3 shadow-[0_10px_30px_rgba(30,58,138,0.2)]">
                            <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-base font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-all" href="#portal">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-500">🎓</span> Cổng học tập
                            </a>
                            <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-base font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-all" href="#quan-tri">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-500">⚙️</span> Quản trị vận hành
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
            <!-- <div class="hidden items-center gap-4 lg:flex"> -->
            <div class="hidden items-center gap-5 lg:flex pb-1">
                <?php if (is_logged_in()): ?>
                    <div class="relative group">
                        <button class="inline-flex items-center gap-3 rounded-xl border border-white bg-white/70 p-2 pr-5 text-base font-black text-blue-900 shadow-[0_4px_0_#93c5fd] hover:-translate-y-1 hover:shadow-[0_6px_0_#93c5fd] hover:bg-white active:translate-y-1 active:shadow-none transition-all duration-150">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center text-sm font-black shadow-inner">
                                <?= substr($user['full_name'] ?? 'U', 0, 1) ?>
                            </div>
                            <span class="max-w-[100px] truncate">Tài khoản</span>
                        </button>
                        <div class="absolute right-0 top-full pt-5 z-50 w-72 opacity-0 invisible translate-y-3 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300">
                            <div class="rounded-[2rem] border-2 border-blue-200 bg-white/95 backdrop-blur-xl p-4 shadow-[0_15px_40px_rgba(30,58,138,0.25)]">
                                <div class="px-2 py-3 border-b border-blue-50 mb-3">
                                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Học viên</p>
                                    <p class="text-lg font-black text-blue-950 truncate"><?= e($user['full_name'] ?? 'Guest') ?></p>
                                </div>
                                <div class="space-y-1">
                                    <?php if (can_access_page('profile')): ?><a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('profile')); ?>">👤 Trang cá nhân</a><?php endif; ?>
                                    <?php if (can_access_page('dashboard-student')): ?><a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('dashboard-student')); ?>">👨‍🎓 Dashboard Học viên</a><?php endif; ?>
                                    <?php if (can_access_page('dashboard-teacher')): ?><a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('dashboard-teacher')); ?>">👨‍🏫 Dashboard Giáo viên</a><?php endif; ?>
                                    <?php if (can_access_page('portfolios-academic')): ?><a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors" href="<?= e(page_url('portfolios-academic')); ?>">🎨 Portfolio</a><?php endif; ?>
                                    <?php if (can_access_page('dashboard-admin')): ?><a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors" href="/admin">🛡️ Quản trị hệ thống</a><?php endif; ?>
                                    <div class="h-px bg-slate-100 my-2"></div>
                                    <a class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-black text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors" href="<?= e(page_url('logout')); ?>">🚪 Đăng xuất</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="relative rounded-xl px-6 py-2.5 text-base font-black text-blue-900 bg-white/70 border border-white shadow-[0_4px_0_#93c5fd] hover:-translate-y-1 hover:shadow-[0_6px_0_#93c5fd] hover:bg-white active:translate-y-1 active:shadow-none transition-all duration-150" href="<?= e(page_url('login')); ?>">Đăng nhập</a>
                <?php endif; ?>
                
                <a href="#lien-he" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-sky-500 px-8 py-3.5 text-base font-black text-white shadow-[0_4px_0_#1e3a8a] border-t border-sky-300 hover:-translate-y-1 hover:shadow-[0_6px_0_#1e3a8a] active:translate-y-1 active:shadow-none transition-all duration-150">
                    ĐĂNG KÝ NGAY
                </a>
            </div>

            <button id="mobile-menu-toggle" class="inline-flex h-12 w-12 flex-col items-center justify-center gap-1.5 rounded-xl border border-white bg-white/80 text-blue-600 shadow-[0_4px_0_#93c5fd] hover:-translate-y-1 hover:shadow-[0_6px_0_#93c5fd] active:translate-y-1 active:shadow-none transition-all duration-150 lg:hidden mb-1" type="button">
                <span class="block h-1 w-6 bg-current rounded-full transition-all"></span>
                <span class="block h-1 w-4 bg-current rounded-full transition-all self-end"></span>
                <span class="block h-1 w-6 bg-current rounded-full transition-all"></span>
            </button>

            <nav id="main-nav" class="absolute right-0 left-0 top-full mt-4 z-50 hidden flex-col gap-1 rounded-b-[2rem] border-t-4 border-blue-300 bg-gradient-to-b from-white/95 to-sky-50/95 backdrop-blur-2xl p-6 shadow-[0_20px_50px_-15px_rgba(30,58,138,0.3)] lg:hidden" aria-label="Menu mobile">
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
                <a href="#lien-he" class="mt-6 inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-sky-400 to-blue-600 px-6 py-4 text-base font-black text-white shadow-[0_4px_0_#0284c7] active:translate-y-1 active:shadow-none transition-all">Đăng ký kiểm tra đầu vào</a>
            </nav>
        </div>
    </header>
    
    <main class="flex-grow">
