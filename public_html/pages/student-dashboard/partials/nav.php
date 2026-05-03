<?php
$studentDashboardActiveTab = (string) ($studentDashboardActiveTab ?? 'dashboard-student');
?>
<nav class="w-full rounded-[2rem] border border-blue-100 bg-white/95 p-5 md:p-6 shadow-[0_14px_40px_rgba(37,99,235,0.08)] backdrop-blur">
	<div class="mb-5 flex items-center justify-between">
		<div>
			<p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-400">Student panel</p>
			<h2 class="mt-1 text-base font-black text-slate-800">Bảng điều hướng</h2>
		</div>
		<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 shadow-inner">
			<i class="fa-solid fa-layer-group"></i>
		</div>
	</div>

	<div class="space-y-3">
		<a href="<?= e(page_url('dashboard-student')); ?>"
		   class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm transition-all duration-300 <?= $studentDashboardActiveTab === 'dashboard-student' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
			<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors <?= $studentDashboardActiveTab === 'dashboard-student' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-100' ?>">
				<i class="fa-solid fa-house"></i>
			</span>
			<span class="font-bold">Thời khoá biểu</span>
		</a>

		<a href="<?= e(page_url('classes-my')); ?>"
		   class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm transition-all duration-300 <?= $studentDashboardActiveTab === 'classes-my' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
			<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors <?= $studentDashboardActiveTab === 'classes-my' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-100' ?>">
				<i class="fa-solid fa-book-open"></i>
			</span>
			<span class="font-bold">Lớp học của tôi</span>
		</a>

		<a href="<?= e(page_url('activities-student')); ?>"
		   class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm transition-all duration-300 <?= $studentDashboardActiveTab === 'activities-student' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
			<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors <?= $studentDashboardActiveTab === 'activities-student' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-100' ?>">
				<i class="fa-solid fa-people-group"></i>
			</span>
			<span class="font-bold">Ngoại khoá</span>
		</a>

		<a href="<?= e(page_url('feedback')); ?>"
		   class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm transition-all duration-300 <?= $studentDashboardActiveTab === 'feedback' ? 'bg-gradient-to-r from-emerald-600 to-teal-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
			<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors <?= $studentDashboardActiveTab === 'feedback' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100' ?>">
				<i class="fa-regular fa-comment-dots"></i>
			</span>
			<span class="font-bold">Đánh giá trung tâm</span>
		</a>
	</div>
</nav>
