<?php
declare(strict_types=1);

require_role(['student', 'admin']);

require_once __DIR__ . '/../../models/tables/ExtracurricularActivitiesTableModel.php';

$studentDashboardActiveTab = 'activities-student';
$activityModel = new ExtracurricularActivitiesTableModel();
$currentUser = auth_user() ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);

$resolveActivityImage = static function (?string $value): string {
	$value = trim((string) $value);
	if ($value === '') {
		return '/assets/images/center.jpg';
	}

	if (preg_match('#^(?:https?:)?//#i', $value) === 1) {
		return $value;
	}

	return str_starts_with($value, '/') ? $value : '/' . ltrim($value, '/');
};

$renderBbcode = static function (string $text): string {
	$text = trim($text);
	if ($text === '') {
		return '';
	}

	if (function_exists('ui_render_bbcode')) {
		return ui_render_bbcode($text);
	}

	if (function_exists('bbcode_to_html')) {
		return bbcode_to_html($text);
	}

	return nl2br(e($text), false);
};

$activityFilter = strtolower(trim((string) ($_GET['filter'] ?? 'all')));
if (!in_array($activityFilter, ['all', 'registered', 'available'], true)) {
	$activityFilter = 'all';
}

$activityPage = max(1, (int) ($_GET['activity_page'] ?? 1));
$activityPerPage = ui_pagination_resolve_per_page('activity_per_page', 8);
$activityPerPageOptions = ui_pagination_per_page_options();

$activityRows = $currentUserId > 0 ? $activityModel->listForStudentActivities($currentUserId) : [];
$allActivities = [];

foreach ($activityRows as $row) {
	$startDate = !empty($row['start_date']) ? date('d/m/Y', strtotime((string) $row['start_date'])) : '---';
	$registeredCount = (int) ($row['registered_count'] ?? 0);
	$isRegistered = ((int) ($row['is_registered'] ?? 0)) === 1;
	$status = (string) ($row['status'] ?? 'upcoming');
	$statusLabel = match ($status) {
		'ongoing' => t('activities.status.ongoing'),
		'finished' => t('activities.status.finished'),
		default => $isRegistered ? t('student.activities.status_registered') : t('student.activities.status_unregistered'),
	};

	$allActivities[] = [
		'id' => (int) ($row['id'] ?? 0),
		'title' => (string) ($row['activity_name'] ?? ''),
		'description' => (string) ($row['description'] ?? ''),
		'content' => (string) ($row['content'] ?? ''),
		'image_thumbnail' => $resolveActivityImage((string) ($row['image_thumbnail'] ?? '')),
		'date' => $startDate,
		'location' => (string) ($row['location'] ?? ''),
		'fee' => (float) ($row['fee'] ?? 0),
		'registered' => $registeredCount,
		'is_registered' => $isRegistered,
			'registration_id' => (int) ($row['registration_id'] ?? 0),
			'payment_status' => (string) ($row['payment_status'] ?? ''),
		'status' => $status,
		'status_label' => $statusLabel,
		'tagClass' => $isRegistered ? 'text-blue-600 bg-blue-50' : 'text-emerald-600 bg-emerald-50',
	];
}

$registeredActivities = array_values(array_filter($allActivities, static fn (array $activity): bool => $activity['is_registered']));
$availableActivities = array_values(array_filter($allActivities, static fn (array $activity): bool => !$activity['is_registered']));
$registeredTotal = count($registeredActivities);
$availableTotal = count($availableActivities);
$registeredTotalPages = max(1, (int) ceil($registeredTotal / $activityPerPage));
$availableTotalPages = max(1, (int) ceil($availableTotal / $activityPerPage));
$activityTotalPages = max($registeredTotalPages, $availableTotalPages);
if ($activityPage > $activityTotalPages) {
	$activityPage = $activityTotalPages;
}

$pageOffset = ($activityPage - 1) * $activityPerPage;
$registeredActivitiesPage = array_slice($registeredActivities, $pageOffset, $activityPerPage);
$availableActivitiesPage = array_slice($availableActivities, $pageOffset, $activityPerPage);

$shouldShowRegistered = $activityFilter === 'all' || $activityFilter === 'registered';
$shouldShowAvailable = $activityFilter === 'all' || $activityFilter === 'available';
?>
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<style>
	.activity-card:hover .activity-card-img {
		transform: scale(1.08);
	}

	.activity-card {
		transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
	}

	.activity-card:hover {
		transform: translateY(-0.5rem);
		box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
	}

	@media (max-width: 767px) {
		.activity-card-carousel {
			overflow-x: auto;
			display: flex;
			gap: 0.75rem;
			padding: 0 0.25rem 0.25rem;
			scroll-snap-type: x mandatory;
			scroll-behavior: smooth;
			-webkit-overflow-scrolling: touch;
			scrollbar-width: none;
		}

		.activity-card-carousel::-webkit-scrollbar {
			display: none;
		}

		.activity-card-carousel .activity-card {
			min-width: 82vw;
			max-width: 82vw;
			flex: 0 0 auto;
			scroll-snap-align: start;
			scroll-snap-stop: always;
		}
	}

	@media (min-width: 768px) {
		.activity-card-carousel {
			overflow: visible;
			display: grid;
		}
	}
</style>
<section class="relative min-h-screen overflow-hidden bg-slate-200 py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
	<div class="absolute inset-0 z-0 opacity-[0.10] pointer-events-none" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
	<div class="absolute inset-x-0 top-0 z-0 h-72 bg-gradient-to-b from-rose-200/75 via-slate-100/45 to-transparent pointer-events-none"></div>
	<div class="mx-auto w-full max-w-[1800px]">
		<div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
			<aside class="lg:sticky lg:top-24">
				<?php require __DIR__ . '/../student-dashboard/partials/nav.php'; ?>
			</aside>

			<div class="relative z-10 min-w-0 space-y-8">
				<header class="flex flex-col gap-2" data-aos="fade-down" data-aos-duration="650" data-aos-delay="160">
					<div>
						<h1 class="text-3xl font-extrabold text-slate-800 tracking-tight" data-aos="fade-right" data-aos-delay="180"><?= e(t('student.activities.title')); ?> <span class="text-blue-600"><?= e(t('student.activities.highlight')); ?></span></h1>
					</div>
				</header>

				<form class="mb-8 max-w-sm" method="get" action="/" data-aos="fade-up" data-aos-delay="240">
					<input type="hidden" name="page" value="activities-student">
					<label for="activity-filter" class="mb-2 block text-sm font-semibold text-slate-600"><?= e(t('student.activities.filter_label')); ?></label>
					<div class="relative">
						<select
							id="activity-filter"
							name="filter"
							class="w-full appearance-none rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
							onchange="this.form.submit()"
						>
							<option value="all" <?= $activityFilter === 'all' ? 'selected' : ''; ?>><?= e(t('activities.status.all')); ?></option>
							<option value="registered" <?= $activityFilter === 'registered' ? 'selected' : ''; ?>><?= e(t('student.activities.filter_registered')); ?></option>
							<option value="available" <?= $activityFilter === 'available' ? 'selected' : ''; ?>><?= e(t('student.activities.filter_available')); ?></option>
						</select>
						<div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
							<i class="fa-solid fa-chevron-down text-xs"></i>
						</div>
					</div>
				</form>

				<?php if ($shouldShowRegistered): ?>
					<div class="mb-10">
						<div class="mb-5 flex items-center justify-between gap-4" data-aos="fade-up" data-aos-delay="160">
							<h2 class="text-2xl font-black text-slate-800"><?= e(t('student.activities.registered_title')); ?></h2>
							<span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><?= e(t('student.activities.count', ['count' => $registeredTotal])); ?></span>
						</div>

						<?php if ($registeredActivitiesPage === []): ?>
							<div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm font-semibold text-slate-500 shadow-lg shadow-slate-200/60">
								<?= e(t('student.activities.empty_registered')); ?>
							</div>
						<?php else: ?>
							<div class="activity-card-carousel grid grid-cols-1 gap-6 items-stretch sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
								<?php $registeredDelay = 120; ?>
								<?php foreach ($registeredActivitiesPage as $activity): ?>
									<article class="activity-card flex flex-col overflow-hidden rounded-[2rem] border border-slate-300 bg-white shadow-[0_22px_65px_rgba(15,23,42,0.16)] group transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_32px_85px_rgba(15,23,42,0.22)]" data-aos="fade-up" data-aos-delay="<?= $registeredDelay; ?>" data-aos-duration="700">
										<div class="relative h-56 overflow-hidden">
											<img src="<?= e((string) $activity['image_thumbnail']); ?>" alt="<?= e((string) $activity['title']); ?>" class="activity-card-img h-full w-full object-cover transition-transform duration-700">
											<div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/15 to-transparent"></div>
											<div class="absolute top-4 right-4 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-blue-900 backdrop-blur">
												<?= e($activity['date']); ?>
											</div>
										</div>

										<div class="flex flex-1 flex-col p-5">
											<div class="mb-2 flex items-start justify-between">
												<span class="rounded px-2 py-1 text-[10px] font-bold uppercase tracking-wider <?= e($activity['tagClass']); ?>"><?= e(t('student.activities.status_registered')); ?></span>
												<span class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($activity['status_label']); ?></span>
											</div>
											<h3 class="mb-2 text-base font-bold leading-tight text-slate-800 transition group-hover:text-blue-600"><?= e($activity['title']); ?></h3>
											<div class="mb-4 line-clamp-2 text-sm leading-relaxed text-slate-500 [&_a]:text-blue-600 [&_a]:underline [&_a]:underline-offset-2 [&_blockquote]:border-l-4 [&_blockquote]:border-slate-200 [&_blockquote]:pl-3 [&_blockquote]:italic [&_code]:rounded-lg [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.92em]">
												<?= $renderBbcode(trim((string) ($activity['content'] ?? '')) !== '' ? (string) ($activity['content'] ?? '') : (string) ($activity['description'] ?? '')); ?>
											</div>

											<div class="mt-auto space-y-4">
												<div class="grid grid-cols-2 gap-3 text-xs">
													<div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
														<span class="block uppercase tracking-wide text-slate-400 font-semibold"><?= e(t('student.activities.date')); ?></span>
														<span class="mt-1 block font-bold text-slate-800"><?= e($activity['date']); ?></span>
													</div>
													<div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
														<span class="block uppercase tracking-wide text-slate-400 font-semibold"><?= e(t('student.activities.location')); ?></span>
														<span class="mt-1 block font-bold text-slate-800"><?= e($activity['location'] !== '' ? $activity['location'] : '---'); ?></span>
													</div>
												</div>

												<div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-xs shadow-sm">
													<span class="block uppercase tracking-wide text-slate-400 font-semibold"><?= e(t('student.activities.registered_count')); ?></span>
													<span class="mt-1 block font-bold text-slate-800"><?= e(t('student.activities.people', ['count' => (int) $activity['registered']])); ?></span>
												</div>

												<div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 text-xs">
													<span class="block uppercase tracking-wide text-slate-400 font-semibold"><?= e(t('student.activities.payment')); ?></span>
													<span class="mt-1 block font-bold text-slate-500"><?= e(t('student.activities.not_integrated')); ?></span>
												</div>

												<a href="<?= e(page_url('activities-details', ['id' => (int) $activity['id']])); ?>" class="mt-2 inline-flex w-full shrink-0 items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-center text-sm font-bold leading-none whitespace-nowrap text-white transition-colors hover:bg-blue-600">
													<?= e(t('public.common.view_detail')); ?>
												</a>
											</div>
										</div>
									</article>
									<?php $registeredDelay += 120; ?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ($registeredTotalPages > 1): ?>
							<div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-lg shadow-slate-200/50 text-xs font-semibold text-slate-600" data-aos="fade-up" data-aos-delay="220">
								<div>
									<?= e(t('student.activities.page_info', ['current' => (int) $activityPage, 'total' => (int) $registeredTotalPages, 'per_page' => (int) $activityPerPage])); ?>
								<div>
								<form class="flex items-center gap-2" method="get" action="<?= e(page_url('activities-student')); ?>">
									<input type="hidden" name="page" value="activities-student">
									<input type="hidden" name="filter" value="<?= e($activityFilter); ?>">
									<input type="hidden" name="activity_page" value="1">
									<label for="activity-per-page" class="text-xs font-semibold text-slate-500"><?= e(t('student.activities.rows')); ?></label>
									<select id="activity-per-page" name="activity_per_page" class="h-9 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm" onchange="this.form.submit()">
										<?php foreach ($activityPerPageOptions as $option): ?>
											<option value="<?= (int) $option; ?>" <?= $activityPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
										<?php endforeach; ?>
									</select>
								</form>
								<div class="flex items-center gap-2">
								<?php if ($activityPage > 1): ?>
									<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-student', ['filter' => $activityFilter, 'activity_page' => $activityPage - 1, 'activity_per_page' => $activityPerPage])); ?>"><?= e(t('activities.previous')); ?></a>
								<?php else: ?>
									<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400"><?= e(t('activities.previous')); ?></span>
								<?php endif; ?>

								<?php if ($activityPage < $registeredTotalPages): ?>
									<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-student', ['filter' => $activityFilter, 'activity_page' => $activityPage + 1, 'activity_per_page' => $activityPerPage])); ?>"><?= e(t('activities.next')); ?></a>
								<?php else: ?>
									<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400"><?= e(t('activities.next')); ?></span>
								<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ($shouldShowAvailable): ?>
					<div>
						<div class="mb-5 flex items-center justify-between gap-4">
							<h2 class="text-2xl font-black text-slate-800"><?= e(t('student.activities.available_title')); ?></h2>
							<span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"><?= e(t('student.activities.count', ['count' => $availableTotal])); ?></span>
						</div>

							<?php if ($availableActivitiesPage === []): ?>
							<div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm font-semibold text-slate-500 shadow-lg shadow-slate-200/60">
								<?= e(t('student.activities.empty_available')); ?>
							</div>
						<?php else: ?>
								<div class="activity-card-carousel grid grid-cols-1 gap-6 items-stretch sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
									<?php $availableDelay = 120; ?>
									<?php foreach ($availableActivitiesPage as $activity): ?>
									<article class="activity-card flex flex-col overflow-hidden rounded-[2rem] border border-slate-300 bg-white shadow-[0_22px_65px_rgba(15,23,42,0.16)] group transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_32px_85px_rgba(15,23,42,0.22)]" data-aos="fade-up" data-aos-delay="<?= $availableDelay; ?>" data-aos-duration="700">
										<div class="relative h-56 overflow-hidden">
											<img src="<?= e((string) $activity['image_thumbnail']); ?>" alt="<?= e((string) $activity['title']); ?>" class="activity-card-img h-full w-full object-cover transition-transform duration-700">
											<div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/15 to-transparent"></div>
											<div class="absolute top-4 right-4 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-blue-900 backdrop-blur">
												<?= e($activity['date']); ?>
											</div>
										</div>

										<div class="flex flex-1 flex-col p-5">
											<div class="mb-2 flex items-start justify-between">
												<span class="rounded px-2 py-1 text-[10px] font-bold uppercase tracking-wider <?= e($activity['tagClass']); ?>"><?= e($activity['is_registered'] ? t('student.activities.status_registered') : t('student.activities.new_activity')); ?></span>
												<span class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($activity['status_label']); ?></span>
											</div>
											<h3 class="mb-2 text-base font-bold leading-tight text-slate-800 transition group-hover:text-blue-600"><?= e($activity['title']); ?></h3>
											<div class="mb-4 line-clamp-2 text-sm leading-relaxed text-slate-500 [&_a]:text-blue-600 [&_a]:underline [&_a]:underline-offset-2 [&_blockquote]:border-l-4 [&_blockquote]:border-slate-200 [&_blockquote]:pl-3 [&_blockquote]:italic [&_code]:rounded-lg [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.92em]">
												<?= $renderBbcode(trim((string) ($activity['content'] ?? '')) !== '' ? (string) ($activity['content'] ?? '') : (string) ($activity['description'] ?? '')); ?>
											</div>

											<div class="mt-auto space-y-4">
												<div class="grid grid-cols-2 gap-3 text-xs">
													<div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
														<span class="block uppercase tracking-wide text-slate-400 font-semibold"><?= e(t('student.activities.date')); ?></span>
														<span class="mt-1 block font-bold text-slate-800"><?= e($activity['date']); ?></span>
													</div>
													<div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
														<span class="block uppercase tracking-wide text-slate-400 font-semibold"><?= e(t('activities.fee')); ?></span>
														<span class="mt-1 block font-bold text-slate-800"><?= (float) $activity['fee'] > 0 ? e(number_format((float) $activity['fee']) . ' đ') : e(t('activities.free_fee')); ?></span>
													</div>
												</div>

												<div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-xs shadow-sm">
													<span class="block uppercase tracking-wide text-slate-400 font-semibold"><?= e(t('student.activities.registered_count')); ?></span>
													<span class="mt-1 block font-bold text-slate-800"><?= e(t('student.activities.people', ['count' => (int) $activity['registered']])); ?></span>
												</div>

												<a href="<?= e(page_url('activities-details', ['id' => (int) $activity['id']])); ?>" class="mt-2 inline-flex w-full shrink-0 items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-center text-sm font-bold leading-none whitespace-nowrap text-white transition-colors hover:bg-blue-600">
													<?= e(t('public.common.view_detail')); ?>
												</a>
											</div>
										</div>
									</article>
									<?php $availableDelay += 120; ?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ($availableTotalPages > 1): ?>
							<div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-lg shadow-slate-200/50 text-xs font-semibold text-slate-600" data-aos="fade-up" data-aos-delay="220">
								<div>
									<?= e(t('student.activities.page_info', ['current' => (int) $activityPage, 'total' => (int) $availableTotalPages, 'per_page' => (int) $activityPerPage])); ?>
								</div>
								<form class="flex items-center gap-2" method="get" action="<?= e(page_url('activities-student')); ?>">
									<input type="hidden" name="page" value="activities-student">
									<input type="hidden" name="filter" value="<?= e($activityFilter); ?>">
									<input type="hidden" name="activity_page" value="1">
									<label for="activity-per-page-available" class="text-xs font-semibold text-slate-500"><?= e(t('student.activities.rows')); ?></label>
									<select id="activity-per-page-available" name="activity_per_page" class="h-9 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm" onchange="this.form.submit()">
										<?php foreach ($activityPerPageOptions as $option): ?>
											<option value="<?= (int) $option; ?>" <?= $activityPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
										<?php endforeach; ?>
									</select>
								</form>
								<div class="flex items-center gap-2">
								<?php if ($activityPage > 1): ?>
									<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-student', ['filter' => $activityFilter, 'activity_page' => $activityPage - 1, 'activity_per_page' => $activityPerPage])); ?>"><?= e(t('activities.previous')); ?></a>
								<?php else: ?>
									<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400"><?= e(t('activities.previous')); ?></span>
								<?php endif; ?>

								<?php if ($activityPage < $availableTotalPages): ?>
									<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('activities-student', ['filter' => $activityFilter, 'activity_page' => $activityPage + 1, 'activity_per_page' => $activityPerPage])); ?>"><?= e(t('activities.next')); ?></a>
								<?php else: ?>
									<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400"><?= e(t('activities.next')); ?></span>
								<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
	const mobileCarouselQuery = window.matchMedia('(max-width: 767px)');
	const carouselState = new WeakMap();

	function stopCarouselAutoScroll(carousel) {
		const state = carouselState.get(carousel);
		if (!state) {
			return;
		}

		if (state.timer) {
			window.clearInterval(state.timer);
			state.timer = null;
		}

		if (state.resumeTimer) {
			window.clearTimeout(state.resumeTimer);
			state.resumeTimer = null;
		}
	}

	function startCarouselAutoScroll(carousel) {
		if (!(carousel instanceof HTMLElement) || !mobileCarouselQuery.matches) {
			return;
		}

		stopCarouselAutoScroll(carousel);

		let state = carouselState.get(carousel);
		if (!state) {
			state = { timer: null, resumeTimer: null, handlersAttached: false };
			carouselState.set(carousel, state);
		}

		const scheduleResume = () => {
			if (state.resumeTimer) {
				window.clearTimeout(state.resumeTimer);
			}

			stopCarouselAutoScroll(carousel);
			state.resumeTimer = window.setTimeout(() => {
				if (!mobileCarouselQuery.matches) {
					return;
				}

				startCarouselAutoScroll(carousel);
			}, 3500);
		};

		if (!state.handlersAttached) {
			carousel.addEventListener('touchstart', scheduleResume, { passive: true });
			carousel.addEventListener('pointerdown', scheduleResume, { passive: true });
			carousel.addEventListener('mouseenter', scheduleResume, { passive: true });
			state.handlersAttached = true;
		}

		state.timer = window.setInterval(() => {
			if (!mobileCarouselQuery.matches || document.hidden) {
				return;
			}

			const cards = Array.from(carousel.querySelectorAll('.activity-card'));
			if (cards.length === 0) {
				return;
			}

			const activeCard = cards.find((card) => {
				const rect = card.getBoundingClientRect();
				const carouselRect = carousel.getBoundingClientRect();
				return rect.left >= carouselRect.left - 4 && rect.left < carouselRect.left + carouselRect.width * 0.5;
			}) ?? cards[0];
			const currentIndex = cards.indexOf(activeCard);
			const nextIndex = (currentIndex + 1) % cards.length;
			cards[nextIndex].scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
		}, 1500);
	}

	function syncActivityCarousels() {
		document.querySelectorAll('.activity-card-carousel').forEach((carousel) => {
			if (mobileCarouselQuery.matches) {
				startCarouselAutoScroll(carousel);
				return;
			}

			stopCarouselAutoScroll(carousel);
			if (carousel instanceof HTMLElement) {
				carousel.scrollTo({ left: 0, behavior: 'auto' });
			}
		});
	}

	if (typeof AOS !== 'undefined') {
		AOS.init({
			duration: 350,
			once: true,
			offset: 0
		});
	}

	syncActivityCarousels();
	if (typeof mobileCarouselQuery.addEventListener === 'function') {
		mobileCarouselQuery.addEventListener('change', syncActivityCarousels);
	} else if (typeof mobileCarouselQuery.addListener === 'function') {
		mobileCarouselQuery.addListener(syncActivityCarousels);
	}
});

window.addEventListener('load', function () {
	if (typeof AOS !== 'undefined') {
		AOS.refresh();
	}
});
</script>
