<?php
declare(strict_types=1);

require_login();

$studentDashboardActiveTab = 'student-notification';
$currentUser = auth_user() ?? [];
$currentUserId = (int) ($currentUser['id'] ?? 0);
$academicModel = new AcademicModel();
$highlightNotificationId = max(0, (int) ($_GET['highlight_notification_id'] ?? 0));
$notificationPage = max(1, (int) ($_GET['notification_page'] ?? 1));
$notificationPerPage = ui_pagination_resolve_per_page('notification_per_page', 10);
$notificationPerPageOptions = ui_pagination_per_page_options();

$notifications = $currentUserId > 0 ? $academicModel->listNotifications($currentUserId) : [];
$highlightedNotification = null;
if ($currentUserId > 0 && $highlightNotificationId > 0) {
	foreach ($notifications as $index => $notification) {
		if ((int) ($notification['id'] ?? 0) !== $highlightNotificationId) {
			continue;
		}

		$highlightedNotification = $notification;
		if ((int) ($notification['is_read'] ?? 0) !== 1) {
			$academicModel->markNotificationRead($highlightNotificationId, $currentUserId);
			$notifications[$index]['is_read'] = 1;
		}
		break;
	}
}

$notificationTotal = count($notifications);
$notificationTotalPages = max(1, (int) ceil($notificationTotal / $notificationPerPage));
if ($highlightedNotification !== null && $notificationPage === 1) {
	$highlightedIndex = 0;
	foreach ($notifications as $index => $notification) {
		if ((int) ($notification['id'] ?? 0) === (int) ($highlightedNotification['id'] ?? 0)) {
			$highlightedIndex = $index;
			break;
		}
	}
	$notificationPage = (int) floor($highlightedIndex / $notificationPerPage) + 1;
}
if ($notificationPage > $notificationTotalPages) {
	$notificationPage = $notificationTotalPages;
}

$unreadCount = $currentUserId > 0 ? $academicModel->countUnreadNotifications($currentUserId) : 0;
$totalCount = $notificationTotal;
$notificationStart = ($notificationPage - 1) * $notificationPerPage;
$visibleNotifications = array_slice($notifications, $notificationStart, $notificationPerPage);

$buildNotificationUrl = static function (array $notification) use ($notificationPage, $notificationPerPage): string {
	$actionUrl = trim((string) ($notification['action_url'] ?? ''));
	if ($actionUrl !== '') {
		return $actionUrl;
	}

	$notificationId = (int) ($notification['id'] ?? 0);
	return page_url('student-notification', [
		'highlight_notification_id' => $notificationId,
		'notification_page' => $notificationPage,
		'notification_per_page' => $notificationPerPage,
	]);
};

$summarizeNotificationText = static function (string $text, int $limit = 180): string {
	$text = trim($text);
	if ($text === '') {
		return '';
	}

	if (function_exists('bbcode_truncate_plain_text')) {
		return bbcode_truncate_plain_text($text, $limit);
	}

	if (function_exists('mb_strimwidth')) {
		return mb_strimwidth(strip_tags($text), 0, $limit, '...');
	}

	$plainText = strip_tags($text);
	return strlen($plainText) > $limit ? substr($plainText, 0, $limit - 3) . '...' : $plainText;
};

$renderNotificationMessage = static function (string $text): string {
	$text = trim($text);
	if ($text === '') {
		return '';
	}

	if (function_exists('bbcode_to_html')) {
		return bbcode_to_html($text);
	}

	return nl2br(e($text), false);
};

$highlightedNotificationId = $highlightedNotification !== null ? (int) ($highlightedNotification['id'] ?? 0) : 0;
?>

<style>
	.notification-item {
		transition: transform 220ms ease, box-shadow 220ms ease, background-color 220ms ease, border-color 220ms ease;
	}

	.notification-item:hover {
		transform: translateY(-2px);
	}

	@keyframes notificationHighlightZoom {
		0% { transform: scale(0.96); box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08); }
		55% { transform: scale(1.03); box-shadow: 0 20px 55px rgba(59, 130, 246, 0.24); }
		100% { transform: scale(1); box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08); }
	}

	.notification-item.is-highlighted {
		animation: notificationHighlightZoom 900ms cubic-bezier(0.16, 1, 0.3, 1) 1;
		outline: 3px solid rgba(59, 130, 246, 0.14);
		outline-offset: 2px;
	}

	.notification-item.is-highlighted .notification-title,
	.notification-item.is-highlighted .notification-message {
		animation: notificationTitlePulse 900ms cubic-bezier(0.16, 1, 0.3, 1) 1;
	}

	@keyframes notificationTitlePulse {
		0% { opacity: 0.88; }
		55% { opacity: 1; }
		100% { opacity: 1; }
	}

	.notification-title,
	.notification-message {
		transition: opacity 220ms ease, transform 220ms ease;
	}
</style>

<section class="relative min-h-screen overflow-hidden bg-slate-200 pb-24 font-jakarta py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
	<div class="absolute inset-0 z-0 opacity-[0.10] pointer-events-none" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
	<div class="absolute inset-x-0 top-0 z-0 h-80 bg-gradient-to-b from-rose-200/75 via-slate-100/45 to-transparent pointer-events-none"></div>
	<div class="absolute -right-24 top-24 z-0 h-72 w-72 rounded-full bg-rose-200/30 blur-3xl pointer-events-none"></div>
	<div class="absolute -left-24 top-52 z-0 h-72 w-72 rounded-full bg-emerald-200/25 blur-3xl pointer-events-none"></div>
	<div class="absolute left-1/2 bottom-10 z-0 h-80 w-80 -translate-x-1/2 rounded-full bg-cyan-200/20 blur-3xl pointer-events-none"></div>

	<div class="relative z-10 mx-auto w-full max-w-[1800px]">
		<div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
			<aside class="lg:sticky lg:top-24">
				<?php require __DIR__ . '/../student-dashboard/partials/nav.php'; ?>
			</aside>

			<div class="min-w-0 space-y-6 sm:space-y-8">
				<header class="rounded-[2rem] border border-slate-100 bg-white/90 p-6 sm:p-8 shadow-[0_14px_40px_rgba(15,23,42,0.06)] backdrop-blur-sm">
					<p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-500">Student panel</p>
					<div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
						<div>
							<h1 class="text-3xl font-black tracking-tight text-slate-900">Thông báo</h1>
							<p class="mt-2 text-sm font-medium text-slate-500">Danh sách đầy đủ các thông báo của bạn trong hệ thống.</p>
						</div>
						<div class="flex flex-wrap gap-2">
							<span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-black text-blue-700">
								<i class="fa-solid fa-bell"></i>
								<?= (int) $totalCount; ?> mục
							</span>
							<span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-black text-rose-600">
								<i class="fa-solid fa-circle-dot"></i>
								<?= (int) $unreadCount; ?> chưa đọc
							</span>
						</div>
					</div>
				</header>

				<?php if ($notifications === []): ?>
					<div class="rounded-[2rem] border border-dashed border-slate-200 bg-white p-10 text-center shadow-sm">
						<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
							<i class="fa-regular fa-bell text-2xl"></i>
						</div>
						<h2 class="text-lg font-black text-slate-800">Chưa có thông báo nào</h2>
						<p class="mt-2 text-sm text-slate-500">Khi hệ thống gửi thông báo mới, chúng sẽ hiển thị tại đây.</p>
					</div>
				<?php else: ?>
					<div class="space-y-3 sm:space-y-4">
						<?php foreach ($visibleNotifications as $notification): ?>
							<?php
							$notificationId = (int) ($notification['id'] ?? 0);
							$isRead = (int) ($notification['is_read'] ?? 0) === 1;
							$title = trim((string) ($notification['title'] ?? 'Thông báo hệ thống'));
							$message = $summarizeNotificationText((string) ($notification['message'] ?? ''), 220);
							$fullMessage = (string) ($notification['message'] ?? '');
							$createdAt = ui_format_datetime((string) ($notification['created_at'] ?? ''));
							$actionUrl = $buildNotificationUrl($notification);
							$cardClasses = $isRead ? 'border-slate-200/90 bg-slate-50/95 ring-1 ring-white/70 shadow-[0_12px_28px_rgba(15,23,42,0.08)]' : 'border-slate-100 bg-white';
							?>
							<article id="notification-item-<?= $notificationId; ?>" data-notification-id="<?= $notificationId; ?>" class="notification-item rounded-[1.5rem] border <?= e($cardClasses); ?> <?= $isRead ? '' : 'shadow-sm'; ?> <?= $highlightedNotificationId === $notificationId ? 'is-highlighted' : ''; ?>">
								<a href="<?= e($actionUrl); ?>" class="block p-5 sm:p-6">
									<div class="flex items-start gap-4">
										<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl <?= $isRead ? 'bg-slate-300 text-slate-600' : 'bg-rose-50 text-rose-600'; ?>">
											<i class="fa-solid fa-bell"></i>
										</div>

										<div class="min-w-0 flex-1">
											<div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
												<div class="min-w-0">
													<h2 class="notification-title text-base sm:text-lg leading-snug <?= $isRead ? 'font-semibold text-slate-800' : 'font-black text-slate-950'; ?>"><?= e($title); ?></h2>
													<?php if ($message !== ''): ?>
														<div class="notification-message mt-2 text-sm leading-relaxed <?= $isRead ? 'font-normal text-slate-600' : 'font-semibold text-slate-700'; ?> [&_a]:font-bold [&_a]:text-blue-600 [&_blockquote]:border-l-4 [&_blockquote]:border-slate-200 [&_blockquote]:pl-4 [&_blockquote]:italic [&_code]:rounded-lg [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.92em]">
															<?= $renderNotificationMessage($fullMessage); ?>
														</div>
													<?php endif; ?>
												</div>

												<div class="shrink-0 text-xs font-bold uppercase tracking-[0.18em] <?= $isRead ? 'text-slate-500' : 'text-rose-500'; ?>">
													<?= $isRead ? 'Đã đọc' : 'Chưa xem'; ?>
												</div>
											</div>

											<div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-500">
												<span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5">
													<i class="fa-regular fa-clock"></i>
													<?= e($createdAt); ?>
												</span>
												<?php if (!$isRead): ?>
													<span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1.5 text-rose-600">
														<i class="fa-solid fa-circle"></i>
														Mới
													</span>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</a>
							</article>
						<?php endforeach; ?>
					</div>
					<?php if ($notificationTotalPages > 1): ?>
						<?php
						$prevPageUrl = $notificationPage > 1
							? page_url('student-notification', ['notification_page' => $notificationPage - 1, 'notification_per_page' => $notificationPerPage])
							: '';
						$nextPageUrl = $notificationPage < $notificationTotalPages
							? page_url('student-notification', ['notification_page' => $notificationPage + 1, 'notification_per_page' => $notificationPerPage])
							: '';
						$pageStart = max(1, $notificationPage - 2);
						$pageEnd = min($notificationTotalPages, $pageStart + 4);
						if ($pageEnd - $pageStart < 4) {
							$pageStart = max(1, $pageEnd - 4);
						}
						?>
						<div class="mt-6 rounded-[1.5rem] border border-slate-200 bg-white/90 px-4 py-4 shadow-sm backdrop-blur-sm sm:px-5">
							<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
								<div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
									<span>Trang <?= (int) $notificationPage; ?>/<?= (int) $notificationTotalPages; ?></span>
									<span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:inline-flex"></span>
									<span>Tổng <?= (int) $notificationTotal; ?> thông báo</span>
								</div>

								<div class="flex flex-wrap items-center gap-2">
									<form method="get" action="<?= e(page_url('student-notification')); ?>" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
										<input type="hidden" name="highlight_notification_id" value="<?= (int) $highlightNotificationId; ?>">
										<label for="notification-per-page" class="whitespace-nowrap">Số dòng</label>
										<select id="notification-per-page" name="notification_per_page" class="h-8 rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">
											<?php foreach ($notificationPerPageOptions as $option): ?>
												<option value="<?= (int) $option; ?>" <?= $notificationPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
											<?php endforeach; ?>
										</select>
										<button type="submit" class="hidden">Áp dụng</button>
									</form>

									<?php if ($prevPageUrl !== ''): ?>
										<a class="inline-flex h-9 items-center rounded-full border border-slate-200 bg-white px-3.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e($prevPageUrl); ?>">Trước</a>
									<?php else: ?>
										<span class="inline-flex h-9 items-center rounded-full border border-slate-200 bg-slate-100 px-3.5 text-sm font-bold text-slate-400">Trước</span>
									<?php endif; ?>

									<?php for ($pageNumber = $pageStart; $pageNumber <= $pageEnd; $pageNumber++): ?>
										<?php $pageUrl = page_url('student-notification', ['notification_page' => $pageNumber, 'notification_per_page' => $notificationPerPage]); ?>
										<?php if ($pageNumber === $notificationPage): ?>
											<span class="inline-flex h-9 min-w-9 items-center justify-center rounded-full bg-blue-600 px-3 text-sm font-black text-white shadow-md shadow-blue-200"><?= (int) $pageNumber; ?></span>
										<?php else: ?>
											<a class="inline-flex h-9 min-w-9 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e($pageUrl); ?>"><?= (int) $pageNumber; ?></a>
										<?php endif; ?>
									<?php endfor; ?>

									<?php if ($nextPageUrl !== ''): ?>
										<a class="inline-flex h-9 items-center rounded-full border border-slate-200 bg-white px-3.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e($nextPageUrl); ?>">Sau</a>
									<?php else: ?>
										<span class="inline-flex h-9 items-center rounded-full border border-slate-200 bg-slate-100 px-3.5 text-sm font-bold text-slate-400">Sau</span>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<script>
						(function () {
							const queryHighlightedId = <?= json_encode($highlightNotificationId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
							let highlightedId = queryHighlightedId;
							if (!highlightedId) {
								try {
									highlightedId = Number(sessionStorage.getItem('studentNotificationHighlightId') || 0);
								} catch (error) {
									highlightedId = 0;
								}
							}

							if (!highlightedId) {
								return;
							}

							try {
								sessionStorage.removeItem('studentNotificationHighlightId');
							} catch (error) {}

							window.requestAnimationFrame(function () {
								const highlightedItem = document.getElementById('notification-item-' + String(highlightedId));
								if (!(highlightedItem instanceof HTMLElement)) {
									return;
								}

								highlightedItem.scrollIntoView({ block: 'center', behavior: 'smooth' });
								highlightedItem.classList.add('is-highlighted');

								window.setTimeout(function () {
									highlightedItem.classList.remove('is-highlighted');
								}, 1000);
							});
						})();
					</script>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
