<!doctype html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Nền tảng Trung tâm Anh ngữ</title>
	<meta name="description" content="Nền tảng quản lý trung tâm tiếng Anh: marketing, portal học viên và quản trị vận hành toàn diện.">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
	<?php require_once __DIR__ . '/tailwind_cdn.php'; ?>
</head>
<body class="min-h-screen bg-slate-50 font-sans leading-relaxed text-slate-900">
	<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur" id="top">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6 flex min-h-[68px] items-center justify-between gap-3">
			<div class="flex-none">
				<a class="inline-flex items-center gap-2" href="#top">
					<span class="inline-grid h-9 w-9 place-items-center rounded-xl bg-blue-700 text-sm font-extrabold text-white">EC</span>
					<span class="text-sm font-extrabold text-slate-800">Trung tâm Anh ngữ Nhuệ Minh</span>
				</a>
			</div>

			<nav class="hidden flex-1 items-center justify-center gap-2 lg:flex" aria-label="Menu chính">
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/">Trang chủ</a>
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#khoa-hoc">Khóa học</a>
				<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#giao-vien">Giáo viên</a>
				<div class="relative group">
					<button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700" type="button" aria-haspopup="true">Hệ thống</button>
					<div class="invisible absolute top-full z-50 mt-2 min-w-44 rounded-xl border border-slate-200 bg-white p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
						<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#portal">Cổng học tập</a>
						<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="#quan-tri">Quản trị</a>
					</div>
				</div>
			</nav>

			<div class="hidden items-center gap-2 lg:flex">
				<?php if (is_logged_in()): ?>
					<div class="relative group">
						<button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700" type="button" aria-haspopup="true">Menu</button>
						<div class="invisible absolute right-0 top-full z-50 mt-2 min-w-56 rounded-xl border border-slate-200 bg-white p-2 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
							<?php if (can_access_page('profile')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=profile">Trang cá nhân</a>
							<?php endif; ?>
							<?php if (can_access_page('student-dashboard')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=student-dashboard">Bảng điều khiển học viên</a>
							<?php endif; ?>
							<?php if (can_access_page('teacher-dashboard')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=teacher-dashboard">Bảng điều khiển giáo viên</a>
							<?php endif; ?>
							<?php if (can_access_page('academic-portfolios')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-portfolios">Portfolio</a>
							<?php endif; ?>
							<?php if (can_access_page('admin-dashboard')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin">Quản trị</a>
							<?php endif; ?>
							<?php if (can_access_page('admin-users')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin/users">Quản lý người dùng</a>
							<?php endif; ?>
							<?php if (can_access_page('finance-tuition')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=finance-tuition">Học phí</a>
							<?php endif; ?>
							<?php if (can_access_page('finance-payments')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=finance-payments">Thanh toán</a>
							<?php endif; ?>
							<?php if (can_access_page('manage-feedbacks')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-feedbacks">Đánh giá</a>
							<?php endif; ?>
							<?php if (can_access_page('manage-approvals')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-approvals">Phê duyệt</a>
							<?php endif; ?>
							<?php if (can_access_page('manage-activities')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-activities">Hoạt động</a>
							<?php endif; ?>
							<?php if (can_access_page('manage-bank')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-bank">Ngân hàng</a>
							<?php endif; ?>
							<?php if (can_access_page('academic-classes')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-classes">Lớp học</a>
							<?php endif; ?>
							<?php if (can_access_page('academic-schedules')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-schedules">Lịch học</a>
							<?php endif; ?>
							<?php if (can_access_page('academic-assignments')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-assignments">Bài tập</a>
							<?php endif; ?>
							<?php if (can_access_page('academic-materials')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-materials">Tài liệu</a>
							<?php endif; ?>
							<?php if (can_access_page('academic-submissions')): ?>
								<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-submissions">Chấm điểm</a>
							<?php endif; ?>
							<a class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=logout">Đăng xuất</a>
						</div>
					</div>
					<a href="#lien-he" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-2 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Đăng ký kiểm tra</a>
				<?php else: ?>
					<a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=login">Đăng nhập</a>
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
					<?php if (can_access_page('profile')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=profile">Trang cá nhân</a><?php endif; ?>
					<?php if (can_access_page('student-dashboard')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=student-dashboard">Bảng điều khiển học viên</a><?php endif; ?>
					<?php if (can_access_page('teacher-dashboard')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=teacher-dashboard">Bảng điều khiển giáo viên</a><?php endif; ?>
					<?php if (can_access_page('academic-portfolios')): ?><a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-portfolios">Portfolio</a><?php endif; ?>
					<?php if (can_access_page('admin-dashboard')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin">Quản trị</a>
					<?php endif; ?>
					<?php if (can_access_page('admin-users')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/admin/users">Quản lý người dùng</a>
					<?php endif; ?>
					<?php if (can_access_page('finance-tuition')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=finance-tuition">Học phí</a>
					<?php endif; ?>
					<?php if (can_access_page('finance-payments')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=finance-payments">Thanh toán</a>
					<?php endif; ?>
					<?php if (can_access_page('manage-feedbacks')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-feedbacks">Đánh giá</a>
					<?php endif; ?>
					<?php if (can_access_page('manage-approvals')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-approvals">Phê duyệt</a>
					<?php endif; ?>
					<?php if (can_access_page('manage-activities')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-activities">Hoạt động ngoại khóa</a>
					<?php endif; ?>
					<?php if (can_access_page('manage-bank')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=manage-bank">Tài khoản ngân hàng</a>
					<?php endif; ?>
					<?php if (can_access_page('academic-classes')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-classes">Lớp học</a>
					<?php endif; ?>
					<?php if (can_access_page('academic-schedules')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-schedules">Lịch học</a>
					<?php endif; ?>
					<?php if (can_access_page('academic-assignments')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-assignments">Bài tập</a>
					<?php endif; ?>
					<?php if (can_access_page('academic-materials')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-materials">Tài liệu</a>
					<?php endif; ?>
					<?php if (can_access_page('academic-submissions')): ?>
						<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=academic-submissions">Chấm điểm</a>
					<?php endif; ?>
					<a href="/?page=logout" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700">Đăng xuất</a>
				<?php else: ?>
					<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=login">Đăng nhập</a>
					<a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700" href="/?page=login">Cổng học viên</a>
				<?php endif; ?>
				<a href="#lien-he" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-3 py-2 text-xs font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Đăng ký kiểm tra đầu vào</a>
			</nav>
		</div>
	</header>
