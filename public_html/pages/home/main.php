<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
?>

<main>
	<section class="py-10 md:py-14">
		<div class="mx-auto w-full max-w-4xl px-4 sm:px-6">
			<p class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Nền tảng giáo dục hiện đại</p>
			<h1>Học tiếng Anh hiệu quả hơn với một hệ thống quản lý đồng bộ</h1>
			<p>
				Từ tuyển sinh, xếp lớp đến theo dõi tiến độ học tập và thanh toán học phí, tất cả được tối ưu trong một nền tảng duy nhất.
			</p>
			<div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
				<a class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" href="#lien-he">Đăng ký kiểm tra đầu vào</a>
				<a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-blue-700 transition hover:-translate-y-0.5 hover:bg-slate-100" href="#khoa-hoc">Xem khóa học</a>
			</div>
		</div>
	</section>

	<?php if ($homeUser && (string) ($homeUser['role'] ?? '') === 'student' && is_array($studentProgress)): ?>
		<section class="py-10 md:py-14" aria-label="Widget học viên">
			<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
				<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h2>Tiến độ học tập của bạn</h2>
					<p>Đã hoàn thành <strong><?= (int) ($studentProgress['completed_lessons'] ?? 0); ?></strong> / <strong><?= (int) ($studentProgress['total_lessons'] ?? 0); ?></strong> buổi học.</p>
					<progress class="my-2 h-2.5 w-full appearance-none overflow-hidden rounded-full border-0 bg-slate-200" max="100" value="<?= (int) ($studentProgress['progress_percent'] ?? 0); ?>"></progress>
					<p><strong><?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%</strong> lộ trình đã hoàn thành.</p>
					<a class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" href="<?= e(page_url('profile')); ?>">Xem hồ sơ học tập</a>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ($homeUser && (string) ($homeUser['role'] ?? '') === 'teacher'): ?>
		<section class="py-10 md:py-14" aria-label="Widget giáo viên">
			<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
				<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h2>Lịch dạy 7 ngày tới</h2>
					<?php if (empty($teacherSchedules)): ?>
						<div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lịch dạy trong 7 ngày tới.</div>
					<?php else: ?>
						<ul class="m-0 grid list-none gap-2 p-0">
							<?php foreach ($teacherSchedules as $schedule): ?>
								<li class="rounded-xl border border-slate-200 bg-slate-50 p-3">
									<strong><?= e((string) $schedule['class_name']); ?></strong>
									<small><?= e((string) $schedule['study_date']); ?> | <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?> | <?= e((string) $schedule['room_name']); ?></small>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<a class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800" href="<?= e(page_url('profile')); ?>">Xem trang cá nhân</a>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="py-10 md:py-14" aria-label="Uy tín và đối tác">
		<div class="mx-auto grid w-full max-w-6xl gap-4 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
			<div class="rounded-xl border border-slate-200 bg-white p-4 text-center"><strong class="block text-2xl font-extrabold text-blue-700">1.200+</strong><span class="text-sm text-slate-500">Học viên đang theo học</span></div>
			<div class="rounded-xl border border-slate-200 bg-white p-4 text-center"><strong class="block text-2xl font-extrabold text-blue-700">95%</strong><span class="text-sm text-slate-500">Đánh giá hài lòng từ phụ huynh</span></div>
			<div class="rounded-xl border border-slate-200 bg-white p-4 text-center"><strong class="block text-2xl font-extrabold text-blue-700">60+</strong><span class="text-sm text-slate-500">Giáo viên và trợ giảng đồng hành</span></div>
			<div class="rounded-xl border border-slate-200 bg-white p-4 text-center"><strong class="block text-2xl font-extrabold text-blue-700">20+</strong><span class="text-sm text-slate-500">Đối tác học thuật và doanh nghiệp</span></div>
		</div>
	</section>

	<section class="py-10 md:py-14" id="portal">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
			<div class="mb-4 grid gap-1">
				<h2>Lợi ích nổi bật cho học viên và trung tâm</h2>
				<p>Thay vì phức tạp hóa bằng kỹ thuật, hệ thống tập trung vào trải nghiệm sử dụng dễ hiểu và dễ quản lý.</p>
			</div>
			<div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>Học tập thông minh</h3>
					<p>Theo dõi lịch học, bài tập, nhắc hạn và phản hồi từ giáo viên theo thời gian thực.</p>
				</article>
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>Quản lý dễ dàng</h3>
					<p>Giáo vụ và quản trị có thể xếp lớp, xếp lịch, duyệt quy trình và theo dõi vận hành trên một màn hình.</p>
				</article>
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>Thanh toán tiện lợi</h3>
					<p>Cập nhật học phí, giao dịch và tài khoản ngân hàng rõ ràng, minh bạch và nhanh gọn.</p>
				</article>
			</div>
		</div>
	</section>

	<section class="py-10 md:py-14" id="khoa-hoc">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
			<div class="mb-4 grid gap-1">
				<h2>Lộ trình học phù hợp từng mục tiêu</h2>
				<p>Từ giao tiếp đến IELTS chuyên sâu, tất cả khóa học đều có lộ trình rõ ràng và đánh giá định kỳ.</p>
			</div>
			<div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>Giao tiếp Level 1</h3>
					<p>Phát triển phản xạ nghe nói và tự tin giao tiếp sau 8-12 tuần.</p>
					<p class="text-2xl font-extrabold text-blue-700">3.200.000d</p>
				</article>
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>IELTS Foundation</h3>
					<p>Củng cố nền tảng 4 kỹ năng trước khi bước vào giai đoạn luyện đề.</p>
					<p class="text-2xl font-extrabold text-blue-700">5.800.000d</p>
				</article>
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>IELTS Intensive 6.5+</h3>
					<p>Tối ưu chiến lược làm bài và nâng điểm nhanh với lộ trình cá nhân hóa.</p>
					<p class="text-2xl font-extrabold text-blue-700">8.900.000d</p>
				</article>
			</div>
		</div>
	</section>

	<section class="py-10 md:py-14 bg-white/70 backdrop-blur-sm" id="giao-vien">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
			<div class="mb-4 grid gap-1">
				<h2>Đội ngũ giáo viên đồng hành sát sao</h2>
				<p>Hồ sơ minh bạch, kinh nghiệm thực chiến và phương pháp dạy học hiện đại.</p>
			</div>
			<div class="grid gap-4 grid-cols-1 md:grid-cols-2">
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>Nguyen Minh Anh</h3>
					<p>IELTS 8.5, TESOL, hơn 7 năm luyện thi và huấn luyện speaking.</p>
				</article>
				<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<h3>Tran Duc Quang</h3>
					<p>CELTA, IELTS 8.0, chuyên giao tiếp phản xạ và học qua tình huống thực tế.</p>
				</article>
			</div>
		</div>
	</section>

	<section class="py-10 md:py-14" id="quan-tri">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
			<div class="mb-4 grid gap-1">
				<h2>Hệ thống đa vai trò gọn gàng</h2>
				<p>Chuyển đổi nhanh giữa các vai trò để xem đúng giao diện và chức năng phù hợp.</p>
			</div>

			<div class="mb-3 flex flex-wrap gap-2" role="tablist" aria-label="Vai trò hệ thống" data-role-switcher>
				<button class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700" data-role="student" type="button" aria-selected="true">Học viên</button>
				<button class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-role="teacher" type="button">Giáo viên</button>
				<button class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-role="staff" type="button">Giáo vụ</button>
				<button class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700" data-role="admin" type="button">Admin</button>
			</div>

			<div class="grid gap-3" data-role-panels>
				<article class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm" data-role="student">
					<h3>Học viên</h3>
					<p>Theo dõi lịch học, tiến độ, bài tập và học phí trong một không gian duy nhất.</p>
				</article>
				<article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-role="teacher">
					<h3>Giáo viên</h3>
					<p>Quản lý bài tập, chấm điểm nhanh và theo dõi kết quả học viên theo lớp.</p>
				</article>
				<article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-role="staff">
					<h3>Giáo vụ</h3>
					<p>Điều phối lớp học, lịch học, quy trình phê duyệt và hoạt động nội bộ.</p>
				</article>
				<article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-role="admin">
					<h3>Admin</h3>
					<p>Quản trị toàn bộ hệ thống, phân quyền chi tiết và theo dõi hiệu quả vận hành.</p>
				</article>
			</div>
		</div>
	</section>

	<section class="py-10 md:py-14 bg-slate-900 text-slate-100" id="lien-he">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6 text-center">
			<h2>Sẵn sàng nâng cấp lộ trình học tiếng Anh?</h2>
			<p>Để lại thông tin để được tư vấn lộ trình và đặt lịch kiểm tra phù hợp với mục tiêu của bạn.</p>
			<form class="mx-auto mt-6 grid max-w-4xl gap-3 md:grid-cols-4" action="#" method="post">
				<?= csrf_input(); ?>
				<label class="grid gap-1 text-left text-sm">
					Họ và tên
					<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="full_name" placeholder="Nguyễn Văn A" required>
				</label>
				<label class="grid gap-1 text-left text-sm">
					Số điện thoại
					<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="tel" name="phone" placeholder="09xxxxxxxx" required>
				</label>
				<label class="grid gap-1 text-left text-sm">
					Mục tiêu
					<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="target" placeholder="IELTS 6.5 trong 6 tháng">
				</label>
				<button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Đăng ký ngay</button>
			</form>
		</div>
	</section>
</main>
