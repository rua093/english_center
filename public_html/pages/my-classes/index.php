<?php
declare(strict_types=1);

require_role(['student', 'admin']);
require_once __DIR__ . '/../../models/tables/AttendanceTableModel.php';
require_once __DIR__ . '/../../models/tables/TuitionFeesTableModel.php';
require_once __DIR__ . '/../../models/tables/AssignmentsTableModel.php';

$classStudentsTable = new ClassStudentsTableModel();
$attendanceTable = new AttendanceTableModel();
$tuitionFeesTable = new TuitionFeesTableModel();
$assignmentsTable = new AssignmentsTableModel();

$studentDashboardActiveTab = 'classes-my';

$user = auth_user() ?? [];
$studentId = (int) ($user['id'] ?? 0);
$myClasses = [];
$classPage = max(1, (int) ($_GET['class_page'] ?? 1));
$classPerPage = ui_pagination_resolve_per_page('class_per_page', 4);
$classPerPageOptions = ui_pagination_per_page_options();

if ($studentId > 0) {
	foreach ($classStudentsTable->listMyClassesForStudent($studentId) as $row) {
		$classId = (int) ($row['class_id'] ?? 0);
		if ($classId <= 0) {
			continue;
		}

		$attendance = $attendanceTable->summaryByStudentForClass($studentId, $classId);
		$tuition = $tuitionFeesTable->findByStudentAndClass($studentId, $classId) ?? [];
		$assignments = [];
		foreach ($assignmentsTable->listForStudentByClass($studentId, $classId) as $assignmentRow) {
			$deadlineRaw = (string) ($assignmentRow['deadline'] ?? '');
			$deadlineTs = $deadlineRaw !== '' ? strtotime($deadlineRaw) : false;
			$isExpired = $deadlineTs !== false && $deadlineTs < time();
			$submittedAt = (string) ($assignmentRow['submitted_at'] ?? '');
			$score = $assignmentRow['score'] !== null ? (float) $assignmentRow['score'] : null;
			$assignments[] = [
				'id' => (int) ($assignmentRow['id'] ?? 0),
				'title' => (string) ($assignmentRow['title'] ?? ''),
				'note' => (string) ($assignmentRow['description'] ?? ''),
				'score' => $score,
				'status' => (string) ($assignmentRow['submission_status'] ?? t('my_classes.not_submitted')),
				'deadline' => $deadlineRaw !== '' ? date('d/m/Y', $deadlineTs !== false ? $deadlineTs : strtotime($deadlineRaw)) : '---',
				'deadline_raw' => $deadlineRaw,
				'submitted_at' => $submittedAt,
				'can_submit' => $submittedAt === '' && !$isExpired && $score === null,
				'can_resubmit' => $submittedAt !== '' && !$isExpired && $score === null,
				'is_graded' => $score !== null,
				'disabled_reason' => $score !== null ? t('my_classes.graded_no_resubmit') : ($isExpired ? t('my_classes.expired_no_submit') : ''),
				'is_expired' => $isExpired,
			];
		}

		$attendanceTotal = (int) ($attendance['total_sessions'] ?? 0);
		if ($attendanceTotal <= 0) {
			$attendanceTotal = (int) ($attendance['present_count'] ?? 0) + (int) ($attendance['late_count'] ?? 0) + (int) ($attendance['absent_count'] ?? 0);
		}
		if ($attendanceTotal <= 0) {
			$attendanceTotal = 1;
		}

		$myClasses[] = [
			'id' => $classId,
			'name' => (string) ($row['class_name'] ?? ''),
			'teacher' => (string) ($row['teacher_name'] ?? ''),
			'start_date' => (string) ($row['start_date'] ?? ''),
			'end_date' => (string) ($row['end_date'] ?? ''),
			'status' => (string) ($row['class_status'] ?? 'upcoming'),
			'attendance' => [
				'present' => (int) ($attendance['present_count'] ?? 0),
				'late' => (int) ($attendance['late_count'] ?? 0),
				'absent' => (int) ($attendance['absent_count'] ?? 0),
				'total' => $attendanceTotal,
			],
			'tuition' => [
				'amount' => (float) ($tuition['total_amount'] ?? 0),
				'paid' => (float) ($tuition['amount_paid'] ?? 0),
				'status' => ((string) ($tuition['status'] ?? 'debt')) === 'paid' ? t('my_classes.paid') : t('my_classes.debt'),
			],
			'assignments' => $assignments,
		];
	}
}

$classTotal = count($myClasses);
$classTotalPages = max(1, (int) ceil($classTotal / $classPerPage));
if ($classPage > $classTotalPages) {
	$classPage = $classTotalPages;
}

$classOffset = ($classPage - 1) * $classPerPage;
$pagedClasses = array_slice($myClasses, $classOffset, $classPerPage);

?>
<section class="relative min-h-screen overflow-hidden bg-slate-200 py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
	<div class="absolute inset-0 z-0 opacity-[0.10] pointer-events-none" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
	<div class="absolute inset-x-0 top-0 z-0 h-72 bg-gradient-to-b from-rose-200/75 via-slate-100/45 to-transparent pointer-events-none"></div>
	<div class="mx-auto w-full max-w-[1800px]">
		<div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
			<aside class="lg:sticky lg:top-24">
				<?php require __DIR__ . '/../student-dashboard/partials/nav.php'; ?>
			</aside>

			<div class="relative z-10 min-w-0 space-y-8">
				<header class="flex flex-col gap-2">
					<div>
						<h1 class="text-3xl font-extrabold text-slate-800 tracking-tight"><?= e(t('my_classes.title_1')); ?> <span class="text-blue-600"><?= e(t('my_classes.title_2')); ?></span></h1>
					</div>
				</header>

				<div class="space-y-8">
				<?php if ($myClasses === []): ?>
					<div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-lg shadow-slate-200/60">
						<p class="text-sm font-bold uppercase tracking-widest text-slate-400"><?= e(t('my_classes.empty_title')); ?></p>
						<p class="mt-2 text-sm text-slate-500"><?= e(t('my_classes.empty_copy')); ?></p>
					</div>
				<?php endif; ?>
				<?php foreach ($pagedClasses as $class): ?>
				<div class="overflow-visible rounded-[3.5rem] border border-slate-400 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.14)] transition hover:shadow-[0_30px_80px_rgba(15,23,42,0.18)]">
					<div class="flex flex-col gap-3 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-800 px-4 py-3 text-white sm:flex-row sm:items-center sm:justify-between">
					<div>
							<h2 class="text-lg font-bold leading-tight"><?= e($class['name']); ?></h2>
							<p class="text-xs font-medium text-slate-300"><?= e(t('my_classes.teacher')); ?>: <?= e($class['teacher']); ?></p>
							<?php
								$classStartDate = (string) ($class['start_date'] ?? '');
								$classEndDate = (string) ($class['end_date'] ?? '');
								$classDateRange = ($classStartDate !== '' || $classEndDate !== '')
									? trim((($classStartDate !== '' ? date('d/m/Y', strtotime($classStartDate)) : '--') . ' - ' . ($classEndDate !== '' ? date('d/m/Y', strtotime($classEndDate)) : '--')))
									: '--';
							?>
							<p class="mt-1 text-xs font-medium text-slate-300"><?= e(t('my_classes.study_time')); ?>: <?= e($classDateRange); ?></p>
					</div>
						<div class="flex items-center gap-2 self-start sm:self-center">
							<span class="rounded-full border border-blue-500/50 bg-blue-600/30 px-2.5 py-1 text-[11px] font-bold text-blue-200"><?= e(t('my_classes.active')); ?></span>
							<a class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-2 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-white hover:text-slate-800" href="<?= e(page_url('classes-my-details', ['class_id' => (int) ($class['id'] ?? 0)])); ?>">
								<?= e(t('my_classes.show_details')); ?> <i class="fa-solid fa-arrow-right text-[10px]"></i>
							</a>
						</div>
					</div>

					<div class="grid grid-cols-1 divide-y divide-slate-200 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
                    
						<div class="space-y-4 p-4 bg-slate-50/95">
						<div>
								<h4 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-700"><div class="h-2 w-2 rounded-full bg-amber-500"></div> <?= e(t('my_classes.attendance_rate')); ?></h4>
							<?php 
							$total = $class['attendance']['total'];
							$p_rate = round(($class['attendance']['present'] / $total) * 100);
							$a_rate = round(($class['attendance']['absent'] / $total) * 100);
							?>
								<div class="flex gap-3 text-center">
									<div class="flex-1 rounded-xl border border-emerald-200 bg-white p-2.5 shadow-sm">
										<span class="block text-xl font-black text-emerald-600"><?= (int) $class['attendance']['present']; ?></span>
										<span class="text-[10px] font-semibold uppercase text-emerald-800"><?= e(t('my_classes.present')); ?></span>
								</div>
									<div class="flex-1 rounded-xl border border-rose-200 bg-white p-2.5 shadow-sm">
										<span class="block text-xl font-black text-rose-600"><?= (int) $class['attendance']['absent']; ?></span>
										<span class="text-[10px] font-semibold uppercase text-rose-800"><?= e(t('my_classes.absent_percent', ['percent' => (string) $a_rate])); ?></span>
								</div>
							</div>
						</div>

							<div class="rounded-2xl border border-slate-300 bg-white p-3.5 shadow-sm">
								<h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-700"><?= e(t('my_classes.subject_tuition')); ?></h4>
							<div class="flex justify-between items-end">
								<div>
										<p class="text-[11px] text-slate-500"><?= e(t('my_classes.paid_total')); ?>:</p>
										<p class="font-bold text-slate-800"><?= number_format($class['tuition']['paid']); ?> / <span class="text-blue-600"><?= number_format($class['tuition']['amount']); ?></span> đ</p>
								</div>
									<span class="rounded bg-amber-100 px-2 py-1 text-[11px] font-bold text-amber-700"><?= e($class['tuition']['status']); ?></span>
							</div>
						</div>
					</div>

						<div class="p-4 lg:col-span-2 bg-white/95">
							<div class="mb-4 flex items-center justify-between gap-3">
								<h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-700"><div class="h-2 w-2 rounded-full bg-blue-500"></div> <?= e(t('my_classes.assignments_scores')); ?></h4>
						</div>
                        
							<div class="max-h-[10.75rem] overflow-y-auto overflow-x-hidden rounded-2xl border border-slate-300 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.10)] sm:max-h-[11.25rem] lg:max-h-[11.75rem]">
								<table class="w-full table-fixed text-left text-xs text-slate-600">
									<thead class="border-b border-slate-200 bg-slate-100 text-[11px] font-semibold uppercase text-slate-600">
									<tr>
											<th class="w-[34%] rounded-tl-xl px-3 py-2.5"><?= e(t('my_classes.assignment_name')); ?></th>
											<th class="w-[15%] px-3 py-2.5"><?= e(t('my_classes.status')); ?></th>
											<th class="w-[18%] px-3 py-2.5">Deadline</th>
											<th class="w-[13%] px-3 py-2.5 text-right"><?= e(t('my_classes.score')); ?></th>
											<th class="w-[20%] rounded-tr-xl px-3 py-2.5 text-right"><?= e(t('my_classes.submit_homework')); ?></th>
									</tr>
								</thead>
								<tbody class="divide-y divide-slate-100">
									<?php if ($class['assignments'] === []): ?>
									<tr>
											<td colspan="5" class="px-3 py-6 text-center text-sm font-semibold text-slate-500">
											<?= e(t('my_classes.no_assignment')); ?>
										</td>
									</tr>
								<?php else: ?>
									<?php foreach ($class['assignments'] as $asm): ?>
									<tr class="hover:bg-slate-50 transition">
											<td class="px-3 py-2.5 align-top font-medium text-slate-800">
												<div class="whitespace-normal break-words leading-snug"><?= e($asm['title']); ?></div>
											</td>
											<td class="px-3 py-2.5 align-top">
											<?php if($asm['status'] == t('my_classes.submitted') || $asm['status'] == 'Đã nộp'): ?>
													<span class="rounded bg-emerald-100 px-2 py-1 text-[11px] font-bold text-emerald-700"><?= e(t('my_classes.submitted')); ?></span>
											<?php else: ?>
													<span class="rounded bg-rose-100 px-2 py-1 text-[11px] font-bold text-rose-700"><?= e(t('my_classes.not_submitted')); ?></span>
											<?php endif; ?>
										</td>
											<td class="px-3 py-2.5 align-top text-xs text-slate-500">
												<div class="whitespace-normal break-words leading-snug"><?= e((string) ($asm['deadline'] ?? '---')); ?></div>
											</td>
											<td class="px-3 py-2.5 align-top text-right font-black <?= $asm['score'] ? 'text-blue-600' : 'text-slate-300'; ?>">
												<?= $asm['score'] !== null ? e((string) $asm['score']) : '-'; ?>
										</td>
											<td class="px-3 py-2.5 align-top text-right">
												<?php $asmCanOpen = !empty($asm['can_submit']) || !empty($asm['can_resubmit']); ?>
												<span class="group relative inline-flex" <?= !$asmCanOpen && !empty($asm['disabled_reason']) ? 'title="' . e((string) $asm['disabled_reason']) . '"' : ''; ?>>
													<button
												type="button"
													class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-[11px] font-black uppercase tracking-widest transition-all hover:-translate-y-0.5 <?= !empty($asm['submitted_at']) && !empty($asm['can_resubmit']) ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35' : (!empty($asm['can_submit']) ? 'bg-gradient-to-r from-blue-600 via-sky-600 to-cyan-600 text-white shadow-lg shadow-blue-500/20 hover:shadow-blue-500/35' : 'bg-slate-100 text-slate-400 cursor-not-allowed shadow-none') ?>"
												data-homework-open="1"
												data-homework-assignment-id="<?= (int) ($asm['id'] ?? 0); ?>"
												data-homework-class="<?= e($class['name']); ?>"
												data-homework-assignment="<?= e($asm['title']); ?>"
												data-homework-deadline="<?= e((string) ($asm['deadline_raw'] ?? '')); ?>"
												data-homework-note="<?= e((string) ($asm['note'] ?? '')); ?>"
												data-homework-status="<?= e((string) $asm['status']); ?>"
													<?= $asmCanOpen ? '' : 'disabled'; ?>
													>
														<?= e(!empty($asm['can_resubmit']) ? t('my_classes.resubmit') : t('my_classes.submit_homework')); ?>
													</button>
													<?php if (!$asmCanOpen): ?>
														<span class="pointer-events-none absolute left-1/2 top-full z-[9999] mt-2 w-max max-w-[240px] -translate-x-1/2 rounded-xl bg-slate-900 px-3 py-2 text-[11px] font-semibold leading-tight text-white opacity-0 shadow-2xl transition group-hover:opacity-100">
															<?= e((string) ($asm['disabled_reason'] ?: t('my_classes.expired_no_submit'))); ?>
														</span>
													<?php endif; ?>
												</span>
										</td>
									</tr>
									<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
				<?php endforeach; ?>

				<?php if ($classTotalPages > 1): ?>
					<div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
						<div class="text-xs font-semibold text-slate-500">
							<?= e(t('my_classes.page_info', ['current' => (string) $classPage, 'total' => (string) $classTotalPages, 'count' => (string) $classTotal])); ?>
						</div>
						<form class="flex items-center gap-2" method="get" action="<?= e(page_url('classes-my')); ?>">
							<input type="hidden" name="page" value="classes-my">
							<input type="hidden" name="class_page" value="1">
							<label for="class-per-page" class="text-xs font-semibold text-slate-500"><?= e(t('my_classes.rows')); ?></label>
							<select id="class-per-page" name="class_per_page" class="h-9 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm" onchange="this.form.submit()">
								<?php foreach ($classPerPageOptions as $option): ?>
									<option value="<?= (int) $option; ?>" <?= $classPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
								<?php endforeach; ?>
							</select>
						</form>
						<div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
							<?php if ($classPage > 1): ?>
								<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classes-my', ['class_page' => $classPage - 1, 'class_per_page' => $classPerPage])); ?>"><?= e(t('my_classes.previous')); ?></a>
							<?php else: ?>
								<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400"><?= e(t('my_classes.previous')); ?></span>
							<?php endif; ?>

							<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700">
								Trang <?= (int) $classPage; ?>/<?= (int) $classTotalPages; ?>
							</span>

							<?php if ($classPage < $classTotalPages): ?>
								<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classes-my', ['class_page' => $classPage + 1, 'class_per_page' => $classPerPage])); ?>"><?= e(t('my_classes.next')); ?></a>
							<?php else: ?>
								<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400"><?= e(t('my_classes.next')); ?></span>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
				</div>
			</div>
		</div>

		<?php $notifyShowTestButtons = false; require __DIR__ . '/../notification/notification.php'; ?>
	</div>

	<div id="homework-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm rounded-[3.5rem]">
		<div class="w-full max-w-2xl overflow-hidden rounded-[3.5rem] bg-white shadow-2xl">
			<div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
				<div>
					<p class="text-xs font-black uppercase tracking-[0.3em] text-blue-400"><?= e(t('my_classes.submit_homework')); ?></p>
					<h3 class="mt-2 text-2xl font-black text-slate-800"><?= e(t('my_classes.upload_homework_title')); ?></h3>
				</div>
				<button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" data-homework-close="1" aria-label="<?= e(t('my_classes.close')); ?>">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>

			<form class="space-y-5 px-6 py-6" method="post" action="<?= e('/api/index.php?resource=assignments&method=submit'); ?>" enctype="multipart/form-data">
				<?= csrf_input(); ?>
				<input type="hidden" name="redirect_to" value="<?= e(page_url('classes-my')); ?>">
				<input type="hidden" name="assignment_id" id="homework-assignment-id" value="">
				<div class="grid gap-5 md:grid-cols-2">
					<div>
						<label class="mb-2 block text-sm font-bold text-slate-700"><?= e(t('my_classes.class')); ?></label>
						<input type="hidden" name="class_name" id="homework-class-name" value="">
						<input type="text" id="homework-class-display" value="" placeholder="<?= e(t('my_classes.selected_class')); ?>" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
					</div>
					<div>
						<label class="mb-2 block text-sm font-bold text-slate-700">Deadline</label>
						<input type="hidden" name="assignment_deadline" id="homework-deadline" value="">
						<input type="text" id="homework-deadline-display" placeholder="<?= e(t('my_classes.deadline_placeholder')); ?>" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
					</div>
				</div>

				<div>
					<label class="mb-2 block text-sm font-bold text-slate-700"><?= e(t('my_classes.assignment_name')); ?></label>
					<input type="hidden" name="assignment_title" id="homework-assignment-title" value="">
					<input type="text" id="homework-assignment-title-display" placeholder="<?= e(t('my_classes.assignment_placeholder')); ?>" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
				</div>

				<div>
					<label class="mb-2 block text-sm font-bold text-slate-700"><?= e(t('my_classes.note')); ?></label>
					<input type="hidden" name="note" id="homework-note" value="">
					<textarea id="homework-note-display" rows="4" placeholder="<?= e(t('my_classes.note_placeholder')); ?>" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed"></textarea>
				</div>

				<div>
					<label class="mb-2 block text-sm font-bold text-slate-700"><?= e(t('my_classes.homework_file')); ?></label>
					<div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-5 transition hover:border-blue-300 hover:bg-blue-50/40">
						<input type="file" name="submission_file" id="homework-file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-blue-600">
						<p class="mt-3 text-xs text-slate-500"><?= e(t('my_classes.accepted_files')); ?></p>
					</div>
				</div>

				<div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
					<button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50" data-homework-close="1"><?= e(t('my_classes.close')); ?></button>
					<button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"><?= e(t('my_classes.submit_homework')); ?></button>
				</div>
			</form>
		</div>
	</div>

	<script>
		(function () {
			const noAssignmentMessage = <?= json_encode(t('my_classes.no_assignment'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
			const modal = document.getElementById('homework-modal');
			if (!modal) return;

			const classInput = document.getElementById('homework-class-name');
			const classDisplay = document.getElementById('homework-class-display');
			const assignmentIdInput = document.getElementById('homework-assignment-id');
			const assignmentInput = document.getElementById('homework-assignment-title');
			const assignmentDisplay = document.getElementById('homework-assignment-title-display');
			const deadlineInput = document.getElementById('homework-deadline');
			const deadlineDisplay = document.getElementById('homework-deadline-display');
			const noteInput = document.getElementById('homework-note');
			const noteDisplay = document.getElementById('homework-note-display');
			const fileInput = document.getElementById('homework-file');
			const homeworkForm = modal.querySelector('form');
			const submitButton = homeworkForm ? homeworkForm.querySelector('button[type="submit"]') : null;

			function notify(type, message) {
				if (typeof showNotify === 'function') {
					showNotify(type, message);
				}
			}

			function openModal(button) {
				if (classInput && button.dataset.homeworkClass) {
					classInput.value = button.dataset.homeworkClass;
				}
				if (classDisplay) {
					classDisplay.value = button.dataset.homeworkClass || '';
				}
				if (assignmentInput && button.dataset.homeworkAssignment) {
					assignmentInput.value = button.dataset.homeworkAssignment;
				}
				if (assignmentIdInput) {
					assignmentIdInput.value = button.dataset.homeworkAssignmentId || '';
				}
				if (assignmentDisplay) {
					assignmentDisplay.value = button.dataset.homeworkAssignment || '';
				}
				if (deadlineInput && button.dataset.homeworkDeadline !== undefined) {
					deadlineInput.value = button.dataset.homeworkDeadline || '';
				}
				if (deadlineDisplay) {
					deadlineDisplay.value = button.dataset.homeworkDeadline || '';
				}
				if (noteInput) {
					noteInput.value = button.dataset.homeworkNote || '';
				}
				if (noteDisplay) {
					noteDisplay.value = button.dataset.homeworkNote || '';
				}
				if (fileInput) {
					fileInput.value = '';
				}
				modal.classList.remove('hidden');
				modal.classList.add('flex');
				document.body.classList.add('overflow-hidden');
			}

			function closeModal() {
				modal.classList.add('hidden');
				modal.classList.remove('flex');
				document.body.classList.remove('overflow-hidden');
			}

			async function submitHomework(event) {
				event.preventDefault();

				if (!(homeworkForm instanceof HTMLFormElement)) {
					return;
				}

				if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
					notify('warning', 'Vui lòng tải lên file bài làm.');
					return;
				}

				if (submitButton instanceof HTMLButtonElement) {
					submitButton.disabled = true;
					submitButton.dataset.originalText = submitButton.textContent || '';
					submitButton.textContent = 'Đang nộp...';
				}

				try {
					const response = await fetch(homeworkForm.action, {
						method: 'POST',
						credentials: 'same-origin',
						headers: {
							'X-Requested-With': 'XMLHttpRequest',
							'Accept': 'application/json'
						},
						body: new FormData(homeworkForm)
					});

					const payload = await response.json().catch(function () {
						return null;
					});

					if (!response.ok || !payload || payload.status !== 'success') {
						throw new Error((payload && payload.message) || 'Nộp bài thất bại. Vui lòng thử lại.');
					}

					closeModal();
					notify('success', payload.message || 'Đã nộp bài thành công.');
				} catch (error) {
					notify('error', error instanceof Error ? error.message : 'Nộp bài thất bại. Vui lòng thử lại.');
				} finally {
					if (submitButton instanceof HTMLButtonElement) {
						submitButton.disabled = false;
						submitButton.textContent = submitButton.dataset.originalText || 'Nộp bài';
						delete submitButton.dataset.originalText;
					}
				}
			}

			document.querySelectorAll('[data-homework-open="1"]').forEach(function (button) {
				button.addEventListener('click', function () {
						if (button.dataset.homeworkEmpty === '1') {
							showNotify('info', noAssignmentMessage);
							return;
						}
					openModal(button);
				});
			});

			document.querySelectorAll('[data-homework-close="1"]').forEach(function (button) {
				button.addEventListener('click', closeModal);
			});

			if (homeworkForm instanceof HTMLFormElement) {
				homeworkForm.addEventListener('submit', submitHomework);
			}

			modal.addEventListener('click', function (event) {
				if (event.target === modal) {
					closeModal();
				}
			});

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
					closeModal();
				}
			});
		})();
	</script>
</section>
