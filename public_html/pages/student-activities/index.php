<?php
declare(strict_types=1);

require_role(['student', 'admin']);

$studentDashboardActiveTab = 'activities-student';


$activityFilter = strtolower(trim((string) ($_GET['filter'] ?? 'all')));
if (!in_array($activityFilter, ['all', 'registered', 'available'], true)) {
	$activityFilter = 'all';
}

$registeredActivities = [
	[
		'title' => 'Kỹ năng viết CV IT chuẩn Nhật Bản',
		'slug' => 'cv-it',
		'type' => 'Workshop',
		'date' => '25 Tháng 5, 2026',
		'registered' => 45,
		'capacity' => 50,
		'location' => 'Phòng A-203',
		'instructor' => 'Tokyo Career Lab',
		'description' => 'Cùng chuyên gia đến từ Tokyo tìm hiểu cách viết CV chinh phục nhà tuyển dụng IT Nhật Bản.',
		'tagClass' => 'text-indigo-600 bg-indigo-50',
		'progressClass' => 'bg-rose-500',
		'status' => 'registered',
	],
	[
		'title' => 'English Speaking Club',
		'slug' => 'speaking-club',
		'type' => 'Câu lạc bộ',
		'date' => '02 Tháng 6, 2026',
		'registered' => 28,
		'capacity' => 40,
		'location' => 'Sân khấu trung tâm',
		'instructor' => 'Teacher Sarah',
		'description' => 'Thực hành phản xạ giao tiếp qua trò chơi, thảo luận nhóm và mini challenge theo chủ đề hàng tuần.',
		'tagClass' => 'text-blue-600 bg-blue-50',
		'progressClass' => 'bg-blue-500',
		'status' => 'registered',
	],
];

$availableActivities = [
	[
		'title' => 'IELTS Mock Test Day',
		'slug' => 'ielts-mock-test-day',
		'type' => 'Thi thử',
		'date' => '12 Tháng 6, 2026',
		'registered' => 60,
		'capacity' => 60,
		'location' => 'Phòng thi 01',
		'instructor' => 'Academic Team',
		'description' => 'Làm bài thi thử với cấu trúc gần sát đề thật, sau đó nhận phân tích chi tiết từng kỹ năng.',
		'tagClass' => 'text-emerald-600 bg-emerald-50',
		'progressClass' => 'bg-emerald-500',
		'status' => 'available',
	],
	[
		'title' => 'Parent Connect Day',
		'slug' => 'parent-connect-day',
		'type' => 'Gặp gỡ',
		'date' => '18 Tháng 6, 2026',
		'registered' => 32,
		'capacity' => 35,
		'location' => 'Hội trường',
		'instructor' => 'Ban điều hành',
		'description' => 'Buổi trao đổi giữa phụ huynh, học viên và giáo viên về tiến độ học tập, mục tiêu và lộ trình sắp tới.',
		'tagClass' => 'text-amber-600 bg-amber-50',
		'progressClass' => 'bg-amber-500',
		'status' => 'available',
	],
];

?>
<section class="min-h-screen bg-[#f8fafc] py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
	<div class="mx-auto w-full max-w-[1800px]">
		<div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
			<aside class="lg:sticky lg:top-24">
				<?php require __DIR__ . '/../student-dashboard/partials/nav.php'; ?>
			</aside>

			<div class="min-w-0 space-y-8">
				<header class="flex flex-col gap-2">
					<div>
			 			<h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Hoạt động <span class="text-blue-600">Ngoại khóa</span></h1>
			 			<p class="mt-2 text-slate-500">Danh sách hoạt động mẫu để chuẩn bị kết nối dữ liệu từ database sau này.</p>
					</div>
				</header>

				<form class="mb-8 max-w-sm" method="get" action="/">
			<input type="hidden" name="page" value="activities-student">
			<label for="activity-filter" class="mb-2 block text-sm font-semibold text-slate-600">Lọc hoạt động</label>
			<div class="relative">
				<select
					id="activity-filter"
					name="filter"
					class="w-full appearance-none rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
					onchange="this.form.submit()"
				>
					<option value="all" <?= $activityFilter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
					<option value="registered" <?= $activityFilter === 'registered' ? 'selected' : ''; ?>>Đã đăng ký</option>
					<option value="available" <?= $activityFilter === 'available' ? 'selected' : ''; ?>>Tham gia ngay</option>
				</select>
				<div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
					<i class="fa-solid fa-chevron-down text-xs"></i>
				</div>
			</div>
		</form>

				<?php
				$shouldShowRegistered = $activityFilter === 'all' || $activityFilter === 'registered';
				$shouldShowAvailable = $activityFilter === 'all' || $activityFilter === 'available';
				?>

				<?php if ($shouldShowRegistered): ?>
				<div class="mb-10">
				<div class="mb-5 flex items-center justify-between gap-4">
					<h2 class="text-2xl font-black text-slate-800">Hoạt động ngoại khóa đã đăng ký tham gia</h2>
					<span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><?= count($registeredActivities); ?> hoạt động</span>
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-stretch">
					<?php foreach ($registeredActivities as $activity): ?>
						<?php $percentage = $activity['capacity'] > 0 ? min(100, (int) round(($activity['registered'] / $activity['capacity']) * 100)) : 0; ?>
						<article class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm group hover:shadow-xl transition-all duration-300 flex flex-col">
							<div class="h-36 bg-gradient-to-br from-blue-900 to-indigo-800 relative overflow-hidden">
								<div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition"></div>
								<div class="absolute top-4 right-4 bg-white/90 backdrop-blur text-blue-900 text-xs font-bold px-3 py-1.5 rounded-full">
									<?= e($activity['date']); ?>
								</div>
							</div>

							<div class="p-5 flex-1 flex flex-col">
								<div class="flex justify-between items-start mb-2">
									<span class="text-[10px] font-bold <?= e($activity['tagClass']); ?> px-2 py-1 rounded uppercase tracking-wider"><?= e($activity['type']); ?></span>
									<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Đã đăng ký</span>
								</div>
								<h3 class="text-base font-bold text-slate-800 mb-2 leading-tight group-hover:text-blue-600 transition"><?= e($activity['title']); ?></h3>
								<p class="text-sm text-slate-500 mb-4 line-clamp-2"><?= e($activity['description']); ?></p>

								<div class="mt-auto space-y-4">
									<div class="grid grid-cols-2 gap-3 text-xs">
										<div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
											<span class="block text-slate-400 font-semibold uppercase tracking-wide">Người phụ trách</span>
											<span class="mt-1 block text-slate-800 font-bold"><?= e($activity['instructor']); ?></span>
										</div>
										<div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
											<span class="block text-slate-400 font-semibold uppercase tracking-wide">Địa điểm</span>
											<span class="mt-1 block text-slate-800 font-bold"><?= e($activity['location']); ?></span>
										</div>
									</div>

									<div>
										<div class="flex justify-between items-center text-xs font-medium mb-3">
											<span class="text-slate-500">Đã đăng ký: <span class="text-slate-800 font-bold"><?= (int) $activity['registered']; ?>/<?= (int) $activity['capacity']; ?></span></span>
											<span class="text-slate-500 font-bold"><?= $percentage; ?>%</span>
										</div>
										<div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
											<div class="h-2 rounded-full <?= e($activity['progressClass']); ?>" style="width: <?= $percentage; ?>%"></div>
										</div>
									</div>

									<a href="<?= e(page_url('activities-details', ['slug' => (string) $activity['slug']])); ?>" class="mt-2 inline-flex w-full shrink-0 items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-center text-sm font-bold leading-none text-white transition-colors hover:bg-blue-600 whitespace-nowrap">
										Xem chi tiết
									</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
				<?php endif; ?>

				<?php if ($shouldShowAvailable): ?>
				<div>
				<div class="mb-5 flex items-center justify-between gap-4">
					<h2 class="text-2xl font-black text-slate-800">Hoạt động ngoại khóa chưa đăng ký</h2>
					<span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"><?= count($availableActivities); ?> hoạt động</span>
				</div>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-stretch">
					<?php foreach ($availableActivities as $activity): ?>
						<?php $percentage = $activity['capacity'] > 0 ? min(100, (int) round(($activity['registered'] / $activity['capacity']) * 100)) : 0; ?>
						<article class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm group hover:shadow-xl transition-all duration-300 flex flex-col">
							<div class="h-36 bg-gradient-to-br from-blue-900 to-indigo-800 relative overflow-hidden">
								<div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition"></div>
								<div class="absolute top-4 right-4 bg-white/90 backdrop-blur text-blue-900 text-xs font-bold px-3 py-1.5 rounded-full">
									<?= e($activity['date']); ?>
								</div>
							</div>

							<div class="p-5 flex-1 flex flex-col">
								<div class="flex justify-between items-start mb-2">
									<span class="text-[10px] font-bold <?= e($activity['tagClass']); ?> px-2 py-1 rounded uppercase tracking-wider"><?= e($activity['type']); ?></span>
									<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cần đăng ký</span>
								</div>
								<h3 class="text-base font-bold text-slate-800 mb-2 leading-tight group-hover:text-blue-600 transition"><?= e($activity['title']); ?></h3>
								<p class="text-sm text-slate-500 mb-4 line-clamp-2"><?= e($activity['description']); ?></p>

								<div class="mt-auto space-y-4">
									<div class="grid grid-cols-2 gap-3 text-xs">
										<div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
											<span class="block text-slate-400 font-semibold uppercase tracking-wide">Người phụ trách</span>
											<span class="mt-1 block text-slate-800 font-bold"><?= e($activity['instructor']); ?></span>
										</div>
										<div class="rounded-2xl bg-slate-50 p-3 border border-slate-100">
											<span class="block text-slate-400 font-semibold uppercase tracking-wide">Địa điểm</span>
											<span class="mt-1 block text-slate-800 font-bold"><?= e($activity['location']); ?></span>
										</div>
									</div>

									<div>
										<div class="flex justify-between items-center text-xs font-medium mb-3">
											<span class="text-slate-500">Đã đăng ký: <span class="text-slate-800 font-bold"><?= (int) $activity['registered']; ?>/<?= (int) $activity['capacity']; ?></span></span>
											<span class="text-slate-500 font-bold"><?= $percentage; ?>%</span>
										</div>
										<div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
											<div class="h-2 rounded-full <?= e($activity['progressClass']); ?>" style="width: <?= $percentage; ?>%"></div>
										</div>
									</div>

									<a href="<?= e(page_url('activities-details', ['slug' => (string) $activity['slug']])); ?>" class="mt-2 inline-flex w-full shrink-0 items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-center text-sm font-bold leading-none text-white transition-colors hover:bg-blue-600 whitespace-nowrap">
										Tham gia ngay
									</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
