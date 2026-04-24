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
			<?php foreach ($myClasses as $class): ?>
			<div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition">
				<div class="bg-slate-800 px-6 py-4 flex justify-between items-center text-white">
					<div>
						<h2 class="text-xl font-bold"><?= $class['name'] ?></h2>
						<p class="text-sm text-slate-300 font-medium">Giảng viên: <?= $class['teacher'] ?></p>
					</div>
					<span class="px-3 py-1 bg-blue-600/30 text-blue-200 rounded-full text-xs font-bold border border-blue-500/50">Đang học</span>
				</div>

				<div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
                    
					<div class="p-6 space-y-6">
						<div>
							<h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-amber-500"></div> Tỉ lệ chuyên cần</h4>
							<?php 
							$total = $class['attendance']['total'];
							$p_rate = round(($class['attendance']['present'] / $total) * 100);
							$a_rate = round(($class['attendance']['absent'] / $total) * 100);
							?>
							<div class="flex gap-4 text-center">
								<div class="flex-1 bg-emerald-50 rounded-xl p-3 border border-emerald-100">
									<span class="block text-2xl font-black text-emerald-600"><?= $class['attendance']['present'] ?></span>
									<span class="text-[10px] uppercase text-emerald-800 font-semibold">Có mặt</span>
								</div>
								<div class="flex-1 bg-rose-50 rounded-xl p-3 border border-rose-100">
									<span class="block text-2xl font-black text-rose-600"><?= $class['attendance']['absent'] ?></span>
									<span class="text-[10px] uppercase text-rose-800 font-semibold">Vắng (<?= $a_rate ?>%)</span>
								</div>
							</div>
						</div>

						<div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
							<h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-2">Học phí môn này</h4>
							<div class="flex justify-between items-end">
								<div>
									<p class="text-xs text-slate-500">Đã đóng / Tổng:</p>
									<p class="font-bold text-slate-800"><?= number_format($class['tuition']['paid']) ?> / <span class="text-blue-600"><?= number_format($class['tuition']['amount']) ?></span> đ</p>
								</div>
								<span class="text-xs font-bold px-2 py-1 rounded bg-amber-100 text-amber-700"><?= $class['tuition']['status'] ?></span>
							</div>
						</div>
					</div>

					<div class="p-6 lg:col-span-2">
						<div class="flex justify-between items-center mb-4">
							<h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-blue-500"></div> Bài tập & Điểm số</h4>
							<button type="button" class="text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl transition shadow-sm" data-homework-open="1" data-homework-class="<?= e($class['name']); ?>" data-homework-empty="<?= $class['assignments'] === [] ? '1' : '0'; ?>">
								+ Nộp bài mới
							</button>
						</div>
                        
						<div class="overflow-x-auto">
							<table class="w-full text-left text-sm text-slate-600">
								<thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
									<tr>
										<th class="px-4 py-3 rounded-tl-xl">Tên bài tập</th>
										<th class="px-4 py-3">Trạng thái</th>
										<th class="px-4 py-3">Deadline</th>
										<th class="px-4 py-3 text-right">Điểm</th>
										<th class="px-4 py-3 text-right rounded-tr-xl">Nộp bài</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-slate-100">
									<?php if ($class['assignments'] === []): ?>
									<tr>
										<td colspan="5" class="px-4 py-8 text-center text-sm font-semibold text-slate-500">
											Chưa có bài tập được giao
										</td>
									</tr>
								<?php else: ?>
									<?php foreach ($class['assignments'] as $asm): ?>
									<tr class="hover:bg-slate-50 transition">
										<td class="px-4 py-3 font-medium text-slate-800"><?= $asm['title'] ?></td>
										<td class="px-4 py-3">
											<?php if($asm['status'] == 'Đã nộp'): ?>
												<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold">Đã nộp</span>
											<?php else: ?>
												<span class="bg-rose-100 text-rose-700 px-2 py-1 rounded text-xs font-bold">Chưa nộp</span>
											<?php endif; ?>
										</td>
										<td class="px-4 py-3 text-xs text-slate-500"><?= $asm['deadline'] ?? '---' ?></td>
										<td class="px-4 py-3 text-right font-black <?= $asm['score'] ? 'text-blue-600 text-lg' : 'text-slate-300' ?>">
											<?= $asm['score'] ?? '-' ?>
										</td>
										<td class="px-4 py-3 text-right">
											<button
												type="button"
												class="inline-flex items-center justify-center rounded-xl bg-slate-800 px-4 py-2 text-xs font-bold text-white transition hover:bg-blue-600"
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
