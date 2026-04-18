<?php
$studentDashboardActiveTab = (string) ($studentDashboardActiveTab ?? 'dashboard-student');
?>
<nav class="w-full md:w-72 shrink-0 rounded-[2rem] border border-blue-100 bg-white/95 p-3 shadow-[0_14px_40px_rgba(37,99,235,0.08)] backdrop-blur">
	<div class="mb-3 flex items-center justify-between px-2 pt-1">
		<div>
			<p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-400">Student panel</p>
			<h2 class="mt-1 text-sm font-black text-slate-800">Bảng điều hướng</h2>
		</div>
		<div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
			<i class="fa-solid fa-layer-group"></i>
		</div>
	</div>

	<div class="space-y-2">
		<a href="<?= e(page_url('dashboard-student')); ?>"
		   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm transition <?= $studentDashboardActiveTab === 'dashboard-student' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
			<span class="flex h-9 w-9 items-center justify-center rounded-xl <?= $studentDashboardActiveTab === 'dashboard-student' ? 'bg-white/15 text-white' : 'bg-blue-50 text-blue-600' ?>">
				<i class="fa-solid fa-house"></i>
			</span>
			<span class="font-semibold">Tổng quan</span>
		</a>

		<a href="<?= e(page_url('classes-my')); ?>"
		   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm transition <?= $studentDashboardActiveTab === 'classes-my' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
			<span class="flex h-9 w-9 items-center justify-center rounded-xl <?= $studentDashboardActiveTab === 'classes-my' ? 'bg-white/15 text-white' : 'bg-blue-50 text-blue-600' ?>">
				<i class="fa-solid fa-book-open"></i>
			</span>
			<span class="font-semibold">Lớp học của tôi</span>
		</a>

		<a href="<?= e(page_url('activities-student')); ?>"
		   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm transition <?= $studentDashboardActiveTab === 'activities-student' ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' ?>">
			<span class="flex h-9 w-9 items-center justify-center rounded-xl <?= $studentDashboardActiveTab === 'activities-student' ? 'bg-white/15 text-white' : 'bg-blue-50 text-blue-600' ?>">
				<i class="fa-solid fa-people-group"></i>
			</span>
			<span class="font-semibold">Ngoại khoá</span>
		</a>
	</div>
</nav>
