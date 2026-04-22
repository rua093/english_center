<?php
declare(strict_types=1);

require_role(['student', 'admin']);

$studentDashboardActiveTab = 'activities-student';
$activityData = require __DIR__ . '/data.php';
$activitySlug = strtolower(trim((string) ($_GET['slug'] ?? '')));
if ($activitySlug === '' || !isset($activityData[$activitySlug])) {
	$activitySlug = array_key_first($activityData) ?: '';
}
$activity = $activityData[$activitySlug] ?? null;
if (!is_array($activity)) {
	http_response_code(404);
	echo '404 Not Found';
	exit;
}

$registeredCount = (int) $activity['registered'];
$capacity = (int) $activity['capacity'];
$percentage = $capacity > 0 ? min(100, (int) round(($registeredCount / $capacity) * 100)) : 0;
$isRegistered = (($activity['status'] ?? '') === 'registered');
?>
<section class="min-h-screen bg-[#f8fafc] py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
	<div class="mx-auto w-full max-w-[1800px]">
		<div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
			<aside class="lg:sticky lg:top-24">
				<?php require __DIR__ . '/../../student-dashboard/partials/nav.php'; ?>
			</aside>

			<div class="min-w-0 space-y-8">
				<header class="flex flex-col gap-2">
					<div>
						<p class="text-sm font-bold uppercase tracking-[0.3em] text-slate-400">Chi tiết hoạt động</p>
						<h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-800"><?= e($activity['title']); ?></h1>
						<p class="mt-2 text-slate-500"><?= e($activity['type']); ?> · <?= e($activity['date']); ?> · <?= e($activity['time']); ?></p>
					</div>
				</header>

				<div class="grid gap-8 xl:grid-cols-[minmax(0,1.4fr)_minmax(360px,0.9fr)]">
			<div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
				<div class="relative h-64 bg-gradient-to-br from-blue-900 to-indigo-800">
					<div class="absolute inset-0 bg-black/20"></div>
					<div class="absolute left-6 top-6 flex flex-wrap gap-3">
						<span class="rounded-full <?= e($activity['tagClass']); ?> px-4 py-2 text-xs font-bold uppercase tracking-wider"><?= e($activity['type']); ?></span>
						<span class="rounded-full bg-white/90 px-4 py-2 text-xs font-bold text-slate-700 backdrop-blur"><?= e($activity['date']); ?></span>
					</div>
					<div class="absolute bottom-6 left-6 right-6 text-white">
						<h2 class="text-2xl font-black leading-tight md:text-4xl"><?= e($activity['title']); ?></h2>
						<p class="mt-2 max-w-2xl text-sm text-white/85 md:text-base"><?= e($activity['description']); ?></p>
					</div>
				</div>

				<div class="grid gap-6 p-6 md:grid-cols-3">
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-xs font-bold uppercase tracking-wide text-slate-400">Người phụ trách</p>
						<p class="mt-2 font-bold text-slate-800"><?= e($activity['instructor']); ?></p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-xs font-bold uppercase tracking-wide text-slate-400">Địa điểm</p>
						<p class="mt-2 font-bold text-slate-800"><?= e($activity['location']); ?></p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-xs font-bold uppercase tracking-wide text-slate-400">Khung giờ</p>
						<p class="mt-2 font-bold text-slate-800"><?= e($activity['time']); ?></p>
					</div>
				</div>

				<div class="px-6 pb-6">
					<div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
						<h3 class="text-lg font-black text-slate-800">Mô tả chi tiết</h3>
						<p class="mt-3 leading-7 text-slate-600"><?= e($activity['longDescription']); ?></p>
					</div>
				</div>
			</div>

			<aside class="space-y-6">
				<div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
					<h3 class="text-lg font-black text-slate-800">Thông tin đăng ký</h3>
					<div class="mt-5 space-y-4">
						<div>
							<div class="mb-2 flex items-center justify-between text-sm font-medium text-slate-500">
								<span>Đã đăng ký</span>
								<span><?= $registeredCount; ?>/<?= $capacity; ?></span>
							</div>
							<div class="h-3 rounded-full bg-slate-100 overflow-hidden">
								<div class="h-3 rounded-full <?= e($activity['progressClass']); ?>" style="width: <?= $percentage; ?>%"></div>
							</div>
						</div>
						<div class="grid grid-cols-2 gap-3 text-sm">
							<div class="rounded-2xl bg-slate-50 p-4 text-center">
								<span class="block text-2xl font-black text-slate-800"><?= $percentage; ?>%</span>
								<span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Sức chứa</span>
							</div>
							<div class="rounded-2xl bg-slate-50 p-4 text-center">
								<span class="block text-2xl font-black <?= $isRegistered ? 'text-blue-600' : 'text-emerald-600'; ?>"><?= $isRegistered ? 'Đã' : 'Mở'; ?></span>
								<span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Trạng thái</span>
							</div>
						</div>
					</div>

					<div class="mt-6 grid gap-3">
						<button class="w-full rounded-2xl bg-blue-600 px-4 py-4 font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
							<?= $isRegistered ? 'Xem lịch tham gia' : 'Tham gia ngay'; ?>
						</button>
						<a href="<?= e(page_url('activities-student', ['filter' => $isRegistered ? 'registered' : 'available'])); ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-4 text-center font-bold text-slate-700 transition hover:bg-slate-50">
							Quay lại danh sách
					</a>
					</div>
				</div>

				<div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
					<h3 class="text-lg font-black text-slate-800">Lợi ích</h3>
					<ul class="mt-4 space-y-3">
						<?php foreach (($activity['benefits'] ?? []) as $benefit): ?>
							<li class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
								<span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-blue-600 text-xs font-black text-white">✓</span>
								<span><?= e($benefit); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</aside>
				</div>
			</div>
		</div>
	</div>
</section>
