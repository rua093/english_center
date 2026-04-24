<?php
declare(strict_types=1);

require_role(['student', 'admin']);

require_once __DIR__ . '/../../models/tables/ClassStudentsTableModel.php';
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
			$assignments[] = [
				'title' => (string) ($assignmentRow['title'] ?? ''),
				'note' => (string) ($assignmentRow['description'] ?? ''),
				'score' => $assignmentRow['score'] !== null ? (float) $assignmentRow['score'] : null,
				'status' => (string) ($assignmentRow['submission_status'] ?? 'Chưa nộp'),
				'deadline' => !empty($assignmentRow['deadline']) ? date('d/m/Y', strtotime((string) $assignmentRow['deadline'])) : '---',
				'deadline_raw' => (string) ($assignmentRow['deadline'] ?? ''),
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
			'name' => (string) ($row['class_name'] ?? ''),
			'teacher' => (string) ($row['teacher_name'] ?? ''),
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
				'status' => ((string) ($tuition['status'] ?? 'debt')) === 'paid' ? 'Đã đóng' : 'Đang nợ',
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
<section class="min-h-screen bg-[#f8fafc] py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
	<div class="mx-auto w-full max-w-[1800px]">
		<div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
			<aside class="lg:sticky lg:top-24">
				<?php require __DIR__ . '/../student-dashboard/partials/nav.php'; ?>
			</aside>

			<div class="min-w-0 space-y-8">
				<header class="flex flex-col gap-2">
					<div>
						<h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Lớp học <span class="text-blue-600">Của tôi</span></h1>
						<p class="mt-2 text-slate-500">Quản lý tiến độ, bài tập và học phí theo từng môn học.</p>
					</div>
				</header>

				<div class="space-y-8">
				<?php if ($myClasses === []): ?>
					<div class="rounded-3xl border border-dashed border-slate-200 bg-white p-10 text-center shadow-sm">
						<p class="text-sm font-bold uppercase tracking-widest text-slate-400">Chưa có lớp học nào</p>
						<p class="mt-2 text-sm text-slate-500">Danh sách lớp sẽ xuất hiện ở đây khi học viên đã được gán vào lớp trong database.</p>
					</div>
				<?php endif; ?>
				<?php foreach ($pagedClasses as $class): ?>
				<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
					<div class="flex items-center justify-between gap-3 bg-slate-800 px-4 py-3 text-white">
					<div>
							<h2 class="text-lg font-bold leading-tight"><?= e($class['name']); ?></h2>
							<p class="text-xs font-medium text-slate-300">Giảng viên: <?= e($class['teacher']); ?></p>
					</div>
						<span class="rounded-full border border-blue-500/50 bg-blue-600/30 px-2.5 py-1 text-[11px] font-bold text-blue-200">Đang học</span>
				</div>

					<div class="grid grid-cols-1 divide-y divide-slate-100 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
                    
						<div class="space-y-4 p-4">
						<div>
								<h4 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-700"><div class="h-2 w-2 rounded-full bg-amber-500"></div> Tỉ lệ chuyên cần</h4>
							<?php 
							$total = $class['attendance']['total'];
							$p_rate = round(($class['attendance']['present'] / $total) * 100);
							$a_rate = round(($class['attendance']['absent'] / $total) * 100);
							?>
								<div class="flex gap-3 text-center">
									<div class="flex-1 rounded-xl border border-emerald-100 bg-emerald-50 p-2.5">
										<span class="block text-xl font-black text-emerald-600"><?= (int) $class['attendance']['present']; ?></span>
										<span class="text-[10px] font-semibold uppercase text-emerald-800">Có mặt</span>
								</div>
									<div class="flex-1 rounded-xl border border-rose-100 bg-rose-50 p-2.5">
										<span class="block text-xl font-black text-rose-600"><?= (int) $class['attendance']['absent']; ?></span>
										<span class="text-[10px] font-semibold uppercase text-rose-800">Vắng (<?= (int) $a_rate; ?>%)</span>
								</div>
							</div>
						</div>

							<div class="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
								<h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-700">Học phí môn này</h4>
							<div class="flex justify-between items-end">
								<div>
										<p class="text-[11px] text-slate-500">Đã đóng / Tổng:</p>
										<p class="font-bold text-slate-800"><?= number_format($class['tuition']['paid']); ?> / <span class="text-blue-600"><?= number_format($class['tuition']['amount']); ?></span> đ</p>
								</div>
									<span class="rounded bg-amber-100 px-2 py-1 text-[11px] font-bold text-amber-700"><?= e($class['tuition']['status']); ?></span>
							</div>
						</div>
					</div>

						<div class="p-4 lg:col-span-2">
							<div class="mb-4 flex items-center justify-between gap-3">
								<h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-700"><div class="h-2 w-2 rounded-full bg-blue-500"></div> Bài tập & Điểm số</h4>
								<button type="button" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700" data-homework-open="1" data-homework-class="<?= e($class['name']); ?>" data-homework-empty="<?= $class['assignments'] === [] ? '1' : '0'; ?>">
								+ Nộp bài mới
							</button>
						</div>
                        
						<div class="overflow-x-auto">
								<table class="w-full text-left text-xs text-slate-600">
									<thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase text-slate-500">
									<tr>
											<th class="rounded-tl-xl px-3 py-2.5">Tên bài tập</th>
											<th class="px-3 py-2.5">Trạng thái</th>
											<th class="px-3 py-2.5">Deadline</th>
											<th class="px-3 py-2.5 text-right">Điểm</th>
											<th class="rounded-tr-xl px-3 py-2.5 text-right">Nộp bài</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-slate-100">
									<?php if ($class['assignments'] === []): ?>
									<tr>
											<td colspan="5" class="px-3 py-6 text-center text-sm font-semibold text-slate-500">
											Chưa có bài tập được giao
										</td>
									</tr>
								<?php else: ?>
									<?php foreach ($class['assignments'] as $asm): ?>
									<tr class="hover:bg-slate-50 transition">
											<td class="px-3 py-2.5 font-medium text-slate-800"><?= e($asm['title']); ?></td>
											<td class="px-3 py-2.5">
											<?php if($asm['status'] == 'Đã nộp'): ?>
													<span class="rounded bg-emerald-100 px-2 py-1 text-[11px] font-bold text-emerald-700">Đã nộp</span>
											<?php else: ?>
													<span class="rounded bg-rose-100 px-2 py-1 text-[11px] font-bold text-rose-700">Chưa nộp</span>
											<?php endif; ?>
										</td>
											<td class="px-3 py-2.5 text-xs text-slate-500"><?= e((string) ($asm['deadline'] ?? '---')); ?></td>
											<td class="px-3 py-2.5 text-right font-black <?= $asm['score'] ? 'text-blue-600' : 'text-slate-300'; ?>">
												<?= $asm['score'] !== null ? e((string) $asm['score']) : '-'; ?>
										</td>
											<td class="px-3 py-2.5 text-right">
											<button
												type="button"
													class="inline-flex items-center justify-center rounded-xl bg-slate-800 px-3 py-2 text-[11px] font-bold text-white transition hover:bg-blue-600"
												data-homework-open="1"
												data-homework-class="<?= e($class['name']); ?>"
												data-homework-assignment="<?= e($asm['title']); ?>"
												data-homework-deadline="<?= e((string) ($asm['deadline_raw'] ?? '')); ?>"
												data-homework-note="<?= e((string) ($asm['note'] ?? '')); ?>"
												data-homework-status="<?= e((string) $asm['status']); ?>"
											>
												<?= $asm['status'] === 'Đã nộp' ? 'Nộp lại' : 'Nộp bài'; ?>
											</button>
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
							Trang <?= (int) $classPage; ?>/<?= (int) $classTotalPages; ?> · Tổng <?= (int) $classTotal; ?> lớp
						</div>
						<form class="flex items-center gap-2" method="get" action="<?= e(page_url('classes-my')); ?>">
							<input type="hidden" name="page" value="classes-my">
							<input type="hidden" name="class_page" value="1">
							<label for="class-per-page" class="text-xs font-semibold text-slate-500">Số dòng</label>
							<select id="class-per-page" name="class_per_page" class="h-9 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm" onchange="this.form.submit()">
								<?php foreach ($classPerPageOptions as $option): ?>
									<option value="<?= (int) $option; ?>" <?= $classPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
								<?php endforeach; ?>
							</select>
						</form>
						<div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
							<?php if ($classPage > 1): ?>
								<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classes-my', ['class_page' => $classPage - 1, 'class_per_page' => $classPerPage])); ?>">Trước</a>
							<?php else: ?>
								<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400">Trước</span>
							<?php endif; ?>

							<?php if ($classPage < $classTotalPages): ?>
								<a class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classes-my', ['class_page' => $classPage + 1, 'class_per_page' => $classPerPage])); ?>">Sau</a>
							<?php else: ?>
								<span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400">Sau</span>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
				</div>
			</div>
		</div>

		<?php $notifyShowTestButtons = false; require __DIR__ . '/../notification/notification.php'; ?>
	</div>

	<div id="homework-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm">
		<div class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
			<div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
				<div>
					<p class="text-xs font-black uppercase tracking-[0.3em] text-blue-400">Nộp bài tập</p>
					<h3 class="mt-2 text-2xl font-black text-slate-800">Tải file bài làm lên hệ thống</h3>
				</div>
				<button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" data-homework-close="1" aria-label="Đóng">
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>

			<form class="space-y-5 px-6 py-6" method="post" action="/pages/my-classes/subform/submit_homework.php" enctype="multipart/form-data">
				<?= csrf_input(); ?>
				<input type="hidden" name="redirect_to" value="<?= e(page_url('classes-my')); ?>">
				<div class="grid gap-5 md:grid-cols-2">
					<div>
						<label class="mb-2 block text-sm font-bold text-slate-700">Lớp học</label>
						<input type="hidden" name="class_name" id="homework-class-name" value="">
						<input type="text" id="homework-class-display" value="" placeholder="Lớp học được chọn" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
					</div>
					<div>
						<label class="mb-2 block text-sm font-bold text-slate-700">Deadline</label>
						<input type="hidden" name="assignment_deadline" id="homework-deadline" value="">
						<input type="text" id="homework-deadline-display" placeholder="Ví dụ: 20/05/2026" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
					</div>
				</div>

				<div>
					<label class="mb-2 block text-sm font-bold text-slate-700">Tên bài tập</label>
					<input type="hidden" name="assignment_title" id="homework-assignment-title" value="">
					<input type="text" id="homework-assignment-title-display" placeholder="Nhập tên bài tập" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
				</div>

				<div>
					<label class="mb-2 block text-sm font-bold text-slate-700">Ghi chú</label>
					<input type="hidden" name="note" id="homework-note" value="">
					<textarea id="homework-note-display" rows="4" placeholder="Ghi chú thêm cho giáo viên nếu cần" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed"></textarea>
				</div>

				<div>
					<label class="mb-2 block text-sm font-bold text-slate-700">File bài làm</label>
					<div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-5 transition hover:border-blue-300 hover:bg-blue-50/40">
						<input type="file" name="submission_file" id="homework-file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-blue-600">
						<p class="mt-3 text-xs text-slate-500">Chấp nhận: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG.</p>
					</div>
				</div>

				<div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
					<button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50" data-homework-close="1">Đóng</button>
					<button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Nộp bài</button>
				</div>
			</form>
		</div>
	</div>

	<script>
		(function () {
			const modal = document.getElementById('homework-modal');
			if (!modal) return;

			const classInput = document.getElementById('homework-class-name');
			const classDisplay = document.getElementById('homework-class-display');
			const assignmentInput = document.getElementById('homework-assignment-title');
			const assignmentDisplay = document.getElementById('homework-assignment-title-display');
			const deadlineInput = document.getElementById('homework-deadline');
			const deadlineDisplay = document.getElementById('homework-deadline-display');
			const noteInput = document.getElementById('homework-note');
			const noteDisplay = document.getElementById('homework-note-display');
			const fileInput = document.getElementById('homework-file');

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

			document.querySelectorAll('[data-homework-open="1"]').forEach(function (button) {
				button.addEventListener('click', function () {
						if (button.dataset.homeworkEmpty === '1') {
							showNotify('info', 'Chưa có bài tập được giao');
							return;
						}
					openModal(button);
				});
			});

			document.querySelectorAll('[data-homework-close="1"]').forEach(function (button) {
				button.addEventListener('click', closeModal);
			});

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
