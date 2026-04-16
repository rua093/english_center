<?php
require_role(['student', 'admin']);

$user = auth_user();
$model = new UserModel();
$dashboard = $model->studentDashboard((int) ($user['id'] ?? 0));
$submissionNotice = get_flash('submission_notice');
$success = get_flash('success');
$error = get_flash('error');
?>
<section class="min-h-screen bg-[#f8fafc] py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        
        <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-blue-900 tracking-tight">
                    Bảng điều khiển <span class="text-blue-600">Học viên</span>
                </h1>
                <p class="mt-1 text-slate-500 font-medium">
                    Chào mừng trở lại, <span class="text-blue-700 font-bold"><?= e($user['full_name']); ?></span>! 👋
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a class="flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200" href="<?= e(page_url('logout')); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Đăng xuất
                </a>
            </div>
        </header>

        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Đi học</p>
                        <h4 class="text-3xl font-bold text-emerald-600"><?= (int) ($dashboard['attendance_summary']['present_count'] ?? 0); ?></h4>
                    </div>
                    <div class="rounded-full bg-emerald-50 p-3 text-emerald-600 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full bg-emerald-500 opacity-20"></div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Đi muộn</p>
                        <h4 class="text-3xl font-bold text-amber-600"><?= (int) ($dashboard['attendance_summary']['late_count'] ?? 0); ?></h4>
                    </div>
                    <div class="rounded-full bg-amber-50 p-3 text-amber-600 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full bg-amber-500 opacity-20"></div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Vắng mặt</p>
                        <h4 class="text-3xl font-bold text-rose-600"><?= (int) ($dashboard['attendance_summary']['absent_count'] ?? 0); ?></h4>
                    </div>
                    <div class="rounded-full bg-rose-50 p-3 text-rose-600 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full bg-rose-500 opacity-20"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            
            <div class="lg:col-span-2 space-y-8">
                
                <article class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            Lịch học sắp tới
                        </h3>
                    </div>
                    
                    <?php if (empty($dashboard['upcoming_schedules'])): ?>
                        <div class="flex flex-col items-center py-10 text-slate-400 border-2 border-dashed border-slate-100 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <p class="text-sm">Hiện tại bạn chưa có lịch học mới.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($dashboard['upcoming_schedules'] as $schedule): ?>
                                <div class="group flex items-center justify-between rounded-2xl border border-slate-50 bg-slate-50/50 p-4 transition hover:bg-blue-50/50 hover:border-blue-100">
                                    <div class="flex gap-4">
                                        <div class="flex flex-col items-center justify-center rounded-xl bg-blue-600 px-3 py-1 text-white shadow-blue-200 shadow-lg">
                                            <span class="text-xs font-medium uppercase"><?= date('M', strtotime($schedule['study_date'])); ?></span>
                                            <span class="text-lg font-bold leading-none"><?= date('d', strtotime($schedule['study_date'])); ?></span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800"><?= e($schedule['class_name']); ?></h4>
                                            <p class="text-xs font-medium text-slate-500">
                                                <span class="inline-flex items-center gap-1 text-blue-600 font-semibold"><?= e($schedule['start_time']); ?> - <?= e($schedule['end_time']); ?></span>
                                                <span class="mx-2">•</span>
                                                Phòng: <?= e($schedule['room_name'] ?? 'N/A'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right hidden sm:block">
                                        <p class="text-xs text-slate-400">Giảng viên</p>
                                        <p class="text-sm font-semibold text-slate-700"><?= e($schedule['teacher_name']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>

                <article class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                    <h3 class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800">
                        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                        Bài tập & Nộp bài
                    </h3>
                    
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-3">
                            <?php if (empty($dashboard['assignments'])): ?>
                                <p class="text-sm text-slate-400">Chưa có bài tập.</p>
                            <?php else: ?>
                                <?php foreach ($dashboard['assignments'] as $assignment): ?>
                                    <div class="rounded-xl border border-slate-100 p-3 text-sm transition hover:bg-slate-50">
                                        <div class="flex justify-between font-bold text-slate-700">
                                            <span><?= e($assignment['title']); ?></span>
                                            <span class="<?= !empty($assignment['score']) ? 'text-emerald-600' : 'text-blue-500' ?>">
                                                <?= !empty($assignment['score']) ? $assignment['score'] . 'đ' : '---' ?>
                                            </span>
                                        </div>
                                        <div class="mt-1 flex justify-between text-[11px] text-slate-400">
                                            <span>DL: <?= e($assignment['deadline']); ?></span>
                                            <span class="italic font-medium uppercase text-blue-400"><?= !empty($assignment['submitted_at']) ? 'Đã nộp' : 'Chưa nộp' ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <form action="/api/assignments/submit" method="post" enctype="multipart/form-data" class="rounded-2xl bg-blue-50/50 p-5 border border-blue-100/50">
                            <?= csrf_input(); ?>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="col-span-2">
                                        <label class="text-[11px] font-bold text-blue-900/50 uppercase ml-1">ID Bài tập</label>
                                        <input type="number" name="assignment_id" class="w-full rounded-xl border-0 bg-white px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-blue-500" placeholder="Ví dụ: 101" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-blue-900/50 uppercase ml-1">Upload File</label>
                                    <input type="file" name="submission_file" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-blue-600 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700 hover:-translate-y-0.5 active:scale-95">Gửi bài ngay</button>
                            </div>
                        </form>
                    </div>
                </article>
            </div>

            <div class="space-y-8">
                
                <article class="overflow-hidden rounded-3xl bg-blue-900 text-white shadow-xl shadow-blue-200">
                    <div class="p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-200">Học phí hiện tại</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        </div>
                        
                        <?php if (!empty($dashboard['tuition'])): ?>
                            <div class="mb-6">
                                <p class="text-xs text-blue-300">Tổng số tiền cần đóng</p>
                                <h2 class="text-2xl font-black italic"><?= format_money((float) $dashboard['tuition']['total_amount']); ?></h2>
                            </div>
                            
                            <div class="space-y-2 border-t border-blue-800 pt-4">
                                <div class="flex justify-between text-xs font-medium">
                                    <span class="text-blue-300">Đã thanh toán:</span>
                                    <span><?= format_money((float) $dashboard['tuition']['amount_paid']); ?></span>
                                </div>
                                <div class="flex justify-between text-xs font-medium">
                                    <span class="text-blue-300">Trạng thái:</span>
                                    <span class="px-2 py-0.5 rounded-full bg-blue-800 text-[10px] uppercase tracking-wider text-blue-200"><?= e($dashboard['tuition']['status']); ?></span>
                                </div>
                            </div>

                            <form action="/api/tuitions/update" method="post" class="mt-6 flex flex-col gap-2">
                                <?= csrf_input(); ?>
                                <input type="hidden" name="tuition_id" value="<?= (int) $dashboard['tuition']['id']; ?>">
                                <input type="number" name="amount" placeholder="Số tiền đóng thêm..." class="w-full rounded-xl border-0 bg-blue-800/50 px-3 py-2 text-xs text-white placeholder:text-blue-400 focus:ring-1 focus:ring-blue-400">
                                <button type="submit" class="w-full rounded-xl bg-white py-2 text-sm font-bold text-blue-900 transition hover:bg-blue-50">Cập nhật phí</button>
                            </form>
                        <?php else: ?>
                            <p class="py-10 text-center text-sm text-blue-400 italic">Chưa có thông tin hóa đơn.</p>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
                    <h3 class="mb-4 text-lg font-bold text-slate-800">Thông báo mới</h3>
                    <div class="space-y-4">
                        <?php if (empty($dashboard['notifications'])): ?>
                            <p class="text-center text-sm text-slate-400 py-4">Hết thông báo rồi!</p>
                        <?php else: ?>
                            <?php foreach ($dashboard['notifications'] as $notif): ?>
                                <div class="relative pl-5 before:absolute before:left-0 before:top-2 before:h-2 before:w-2 before:rounded-full before:bg-blue-500">
                                    <h5 class="text-sm font-bold text-slate-700"><?= e($notif['title']); ?></h5>
                                    <p class="text-xs text-slate-500 line-clamp-2"><?= e($notif['message']); ?></p>
                                    <span class="text-[10px] text-slate-300"><?= e($notif['created_at']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>

            </div>
        </div>
    </div>
</section>


