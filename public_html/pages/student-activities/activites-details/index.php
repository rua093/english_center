<?php
declare(strict_types=1);

require_role(['student', 'admin']);

require_once __DIR__ . '/../../../models/tables/ExtracurricularActivitiesTableModel.php';

$studentDashboardActiveTab = 'activities-student';
$activityModel = new ExtracurricularActivitiesTableModel();
$currentUser = auth_user() ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);

$activityId = (int) ($_GET['id'] ?? 0);
$activity = $activityModel->findStudentActivityById($activityId, $currentUserId);
if (!is_array($activity)) {
	http_response_code(404);
	echo '404 Not Found';
	exit;
}

$registeredCount = (int) ($activity['registered_count'] );
$isRegistered = ((int) ($activity['is_registered'] ?? 0)) === 1;
$paymentStatus = (string) ($activity['payment_status'] ?? 'unpaid');
$isPaid = $paymentStatus === 'paid';
$status = (string) ($activity['status'] ?? 'upcoming');
$statusLabel = match ($status) {
	'ongoing' => 'Đang diễn ra',
	'finished' => 'Đã kết thúc',
	default => $isRegistered ? 'Đã đăng ký' : 'Chưa đăng ký',
};
$actionBadgeClass = !$isRegistered
	? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700'
	: ($isPaid
		? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20'
		: 'bg-amber-500 text-white shadow-lg shadow-amber-500/20');
$actionBadgeText = !$isRegistered
	? 'Đăng ký hoạt động'
	: ($isPaid
		? 'Đã thanh toán'
		: 'Đã đăng ký, chờ thanh toán');
$statusChipClass = !$isRegistered
	? 'bg-blue-50 text-blue-600'
	: ($isPaid
		? 'bg-emerald-50 text-emerald-600'
		: 'bg-amber-50 text-amber-600');
$statusNoteClass = !$isRegistered
	? 'border-blue-100 bg-blue-50 text-blue-700'
	: ($isPaid
		? 'border-emerald-100 bg-emerald-50 text-emerald-700'
		: 'border-amber-100 bg-amber-50 text-amber-700');
$startDate = !empty($activity['start_date']) ? date('d/m/Y', strtotime((string) $activity['start_date'])) : '---';
$fee = (float) ($activity['fee'] ?? 0);
$detailText = trim((string) ($activity['content'] ?? ''));
if ($detailText === '') {
	$detailText = trim((string) ($activity['description'] ?? ''));
}

$showConfirmTestButtons = false;
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
						<h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-800"><?= e((string) ($activity['activity_name'] ?? '')); ?></h1>
					</div>
				</header>

				<div class="grid gap-8 xl:grid-cols-[minmax(0,1.4fr)_minmax(360px,0.9fr)]">
					<div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
						<div class="relative h-64 bg-gradient-to-br from-blue-900 to-indigo-800"<?= !empty($activity['image_thumbnail']) ? ' style="background-image: url(' . e((string) $activity['image_thumbnail']) . '); background-size: cover; background-position: center;"' : ''; ?>>
							<div class="absolute inset-0 bg-black/20"></div>
							<div class="absolute left-6 top-6 flex flex-wrap gap-3">
								<span class="rounded-full px-4 py-2 text-xs font-bold uppercase tracking-wider <?= e($statusChipClass); ?>"><?= e($statusLabel); ?></span>
								<span class="rounded-full bg-white/90 px-4 py-2 text-xs font-bold text-slate-700 backdrop-blur"><?= e($startDate); ?></span>
							</div>
							<div class="absolute bottom-6 left-6 right-6 text-white">
								<h2 class="text-2xl font-black leading-tight md:text-4xl"><?= e((string) ($activity['activity_name'] ?? '')); ?></h2>
								<p class="mt-2 max-w-2xl text-sm text-white/85 md:text-base"><?= e((string) ($activity['description'] ?? '')); ?></p>
							</div>
						</div>

						<div class="grid gap-6 p-6 md:grid-cols-3">
							<div class="rounded-2xl bg-slate-50 p-4">
								<p class="text-xs font-bold uppercase tracking-wide text-slate-400">Ngày diễn ra</p>
								<p class="mt-2 font-bold text-slate-800"><?= e($startDate); ?></p>
							</div>
							<div class="rounded-2xl bg-slate-50 p-4">
								<p class="text-xs font-bold uppercase tracking-wide text-slate-400">Địa điểm</p>
								<p class="mt-2 font-bold text-slate-800"><?= e((string) ($activity['location'] !== '' ? $activity['location'] : '---')); ?></p>
							</div>
							<div class="rounded-2xl bg-slate-50 p-4">
								<p class="text-xs font-bold uppercase tracking-wide text-slate-400">Phí tham gia</p>
								<p class="mt-2 font-bold text-slate-800"><?= $fee > 0 ? number_format($fee) . ' đ' : 'Miễn phí'; ?></p>
							</div>
						</div>

						<div class="px-6 pb-6">
							<div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
								<h3 class="text-lg font-black text-slate-800">Mô tả chi tiết</h3>
								<p class="mt-3 leading-7 text-slate-600"><?= e($detailText !== '' ? $detailText : 'Chưa có mô tả chi tiết.'); ?></p>
							</div>

							<div class="mt-4 rounded-2xl border px-4 py-3 text-sm font-semibold <?= e($statusNoteClass); ?>">
								<?= !$isRegistered ? 'Bạn chưa đăng ký hoạt động này.' : ($isPaid ? 'Hoạt động đã được thanh toán.' : 'Bạn đã đăng ký hoạt động này, hiện đang chờ thanh toán.'); ?>
							</div>

							<div class="mt-5 flex flex-col gap-3 sm:flex-row">
								<?php if (!$isRegistered): ?>
									<form method="post" action="/api/index.php?action=do-register-activity" class="flex-1" onsubmit="event.preventDefault(); showConfirm('success', 'Đăng ký hoạt động?', 'Bạn chắc chắn muốn đăng ký tham gia hoạt động này chứ?', () => this.submit());">
										<?= csrf_input(); ?>
										<input type="hidden" name="activity_id" value="<?= (int) $activity['id']; ?>">
										<button type="submit" class="w-full rounded-2xl px-4 py-4 font-bold transition <?= e($actionBadgeClass); ?>">
											<?= e($actionBadgeText); ?>
										</button>
									</form>
								<?php else: ?>
									<div class="flex-1 rounded-2xl px-4 py-4 text-center font-bold <?= e($actionBadgeClass); ?>">
										<?= e($actionBadgeText); ?>
									</div>
								<?php endif; ?>

								<a href="<?= e(page_url('activities-student', ['filter' => $isRegistered ? 'registered' : 'available'])); ?>" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-center font-bold text-slate-700 transition hover:bg-slate-50">
									Quay lại danh sách
								</a>
							</div>
						</div>
					</div>

					<?php require __DIR__ . '/../../notification/confirm_modal.php'; ?>

					<aside class="space-y-6">
						<div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
							<h3 class="text-lg font-black text-slate-800">Thông tin đăng ký</h3>
							<div class="mt-5 space-y-4">
								<div class="grid grid-cols-2 gap-3 text-sm">
									<div class="rounded-2xl bg-slate-50 p-4 text-center">
										<span class="block text-2xl font-black text-slate-800"><?= $registeredCount; ?></span>
										<span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Người đã đăng ký</span>
									</div>
									<div class="rounded-2xl bg-slate-50 p-4 text-center">
										<span class="block text-2xl font-black <?= $isRegistered ? 'text-blue-600' : 'text-emerald-600'; ?>"><?= $isRegistered ? 'Đã' : 'Mở'; ?></span>
										<span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Trạng thái</span>
									</div>
								</div>
								<div class="rounded-2xl bg-slate-50 p-4">
									<p class="text-xs font-bold uppercase tracking-wide text-slate-400">Ghi chú</p>
									<p class="mt-2 text-sm leading-6 text-slate-600"><?= e($statusLabel); ?>.</p>
								</div>
							</div>

							<div class="mt-6 grid gap-3">
								<a href="<?= e(page_url('activities-student', ['filter' => $isRegistered ? 'registered' : 'available'])); ?>" class="w-full rounded-2xl bg-blue-600 px-4 py-4 text-center font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
									<?= $isRegistered ? 'Xem danh sách đã đăng ký' : 'Xem các hoạt động khác'; ?>
								</a>
								<a href="<?= e(page_url('activities-student', ['filter' => 'all'])); ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-4 text-center font-bold text-slate-700 transition hover:bg-slate-50">
									Quay lại danh sách
								</a>
							</div>
						</div>
					</aside>
				</div>
			</div>
		</div>
	</div>
</section>
