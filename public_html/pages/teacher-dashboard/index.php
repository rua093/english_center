<?php
set_flash('info', 'Trang giao vien da duoc hop nhat vao khu vuc quan tri.');
redirect(page_url('admin'));

$user = auth_user();
$academicModel = new AcademicModel();
$assignments = $academicModel->listAssignments();
$submissions = $academicModel->listSubmissionsForGrading();
$teacherSchedules = (new UserModel())->teacherUpcomingSchedules((int) ($user['id'] ?? 0));

$success = get_flash('success');
$error = get_flash('error');
?>

<section class="py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        
        <header class="mb-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                    Hệ thống quản trị giáo viên
                </nav>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                    Chào buổi sáng, <span class="text-blue-700">Thầy <?= e($user['full_name'] ?? 'Bảo'); ?></span>!
                </h1>
                <p class="mt-1 text-slate-500 font-medium">Hôm nay bạn có <span class="text-blue-600 font-bold"><?= count(array_filter($submissions, fn($s) => !isset($s['score']))); ?> bài nộp</span> đang chờ chấm điểm.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <?php if (can_access_page('assignments-academic')): ?>
                    <a class="inline-flex items-center justify-center rounded-2xl bg-blue-700 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition-all hover:bg-blue-800 hover:-translate-y-1 active:scale-95" href="<?= e(page_url('assignments-academic')); ?>">
                        + TẠO BÀI TẬP MỚI
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="mb-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article class="group relative overflow-hidden rounded-[2rem] border border-white bg-white p-6 shadow-xl shadow-slate-900/5 transition-all hover:shadow-blue-900/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Tổng bài tập</p>
                        <h4 class="mt-2 text-4xl font-black text-slate-900"><?= count($assignments); ?></h4>
                    </div>
                    <div class="rounded-2xl bg-blue-50 p-4 text-blue-600 transition-transform group-hover:scale-110">
                        <i class="fa-solid fa-file-signature text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-xs font-bold text-blue-600">
                    <span>Quản lý kho bài tập</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-[2rem] border border-white bg-white p-6 shadow-xl shadow-slate-900/5 transition-all hover:shadow-rose-900/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Cần chấm điểm</p>
                        <h4 class="mt-2 text-4xl font-black text-rose-600"><?= count(array_filter($submissions, fn($s) => !isset($s['score']))); ?></h4>
                    </div>
                    <div class="rounded-2xl bg-rose-50 p-4 text-rose-600 transition-transform group-hover:scale-110">
                        <i class="fa-solid fa-clock-rotate-left text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-xs font-bold text-rose-600">
                    <span>Ưu tiên chấm ngay</span>
                    <i class="fa-solid fa-fire-flame-curved"></i>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-[2rem] border border-white bg-white p-6 shadow-xl shadow-slate-900/5 transition-all hover:shadow-emerald-900/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Đã hoàn thành</p>
                        <h4 class="mt-2 text-4xl font-black text-emerald-600"><?= count(array_filter($submissions, fn($s) => isset($s['score']))); ?></h4>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-4 text-emerald-600 transition-transform group-hover:scale-110">
                        <i class="fa-solid fa-check-double text-2xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-xs font-bold text-emerald-600">
                    <span>Tiến độ tuyệt vời!</span>
                </div>
            </article>
        </div>

        <?php if ($success || $error): ?>
            <div class="mb-8">
                <?php if ($success): ?>
                    <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 shadow-sm">
                        <i class="fa-solid fa-circle-check"></i> <?= e($success); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm font-bold text-rose-700 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= e($error); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            
            <div class="lg:col-span-8 space-y-8">
                
                <article class="rounded-[2rem] border border-white bg-white p-8 shadow-xl shadow-slate-900/5">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Hàng chờ chấm điểm</h3>
                        <a href="<?= e(page_url('submissions-academic')); ?>" class="text-xs font-bold text-blue-600 hover:underline">Xem tất cả</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-50">
                                    <th class="pb-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Học viên</th>
                                    <th class="pb-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Bài tập</th>
                                    <th class="pb-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php $pending = array_values(array_filter($submissions, fn($s) => !isset($s['score']))); ?>
                                <?php if (empty($pending)): ?>
                                    <tr>
                                        <td colspan="3" class="py-10 text-center text-sm font-medium text-slate-400 italic">Không có bài nộp nào đang chờ.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($pending, 0, 5) as $sub): ?>
                                        <tr class="group transition-colors hover:bg-slate-50/50">
                                            <td class="py-4">
                                                <span class="text-sm font-bold text-slate-700"><?= e((string) $sub['student_name']); ?></span>
                                            </td>
                                            <td class="py-4">
                                                <span class="text-sm text-slate-500"><?= e((string) $sub['assignment_title']); ?></span>
                                            </td>
                                            <td class="py-4 text-right">
                                                <a href="<?= e(page_url('submissions-academic')); ?>" class="rounded-xl bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-blue-700 transition-all hover:bg-blue-700 hover:text-white">Chấm bài</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-white bg-white p-8 shadow-xl shadow-slate-900/5">
                    <h3 class="mb-6 text-xl font-black text-slate-800 tracking-tight">Bài tập vừa tạo</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <?php if (empty($assignments)): ?>
                            <div class="col-span-2 py-8 text-center text-sm text-slate-400 italic">Chưa có bài tập nào.</div>
                        <?php else: ?>
                            <?php foreach (array_slice($assignments, 0, 4) as $a): ?>
                                <div class="rounded-2xl border border-slate-50 bg-slate-50/50 p-4 transition hover:bg-white hover:shadow-md">
                                    <h4 class="text-sm font-bold text-slate-700 truncate"><?= e((string) $a['title']); ?></h4>
                                    <p class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hạn chót: <?= e((string) $a['deadline']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

            <div class="lg:col-span-4 space-y-8">
                
                <article class="rounded-[2rem] border border-white bg-blue-900 p-8 text-white shadow-xl shadow-blue-900/20">
                    <h3 class="mb-6 text-lg font-black tracking-tight">Lịch dạy 7 ngày tới</h3>
                    <div class="space-y-4">
                        <?php if (empty($teacherSchedules)): ?>
                            <p class="text-center text-xs text-blue-300 italic py-4">Bạn đang có thời gian nghỉ ngơi!</p>
                        <?php else: ?>
                            <?php foreach ($teacherSchedules as $schedule): ?>
                                <div class="relative pl-4 before:absolute before:left-0 before:top-1 before:h-2 before:w-2 before:rounded-full before:bg-blue-400">
                                    <h5 class="text-sm font-bold leading-tight"><?= e((string) $schedule['class_name']); ?></h5>
                                    <div class="mt-1 flex flex-wrap gap-x-3 text-[10px] font-bold text-blue-300 uppercase">
                                        <span><?= e((string) $schedule['study_date']); ?></span>
                                        <span><?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?></span>
                                    </div>
                                    <p class="mt-1 text-[10px] font-medium text-blue-400">Phòng: <?= e((string) $schedule['room_name']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-900/5">
                    <h3 class="mb-4 text-base font-black text-slate-800 tracking-tight">Yêu cầu dời lịch dạy</h3>
                    <p class="mb-6 text-xs font-medium text-slate-400 leading-relaxed">Admin sẽ xem xét và phản hồi yêu cầu của bạn trong vòng 24h.</p>
                    
                    <form class="space-y-4" method="post" action="/api/teachers/request-leave">
                        <?= csrf_input(); ?>
                        <div>
                            <label class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Chọn ID Lịch học</label>
                            <input type="number" name="schedule_id" min="1" required class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition-all focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Ngày đề xuất mới</label>
                            <input type="date" name="new_date" required class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition-all focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Lý do xin dời</label>
                            <textarea name="reason" rows="2" placeholder="Ví dụ: Công tác đột xuất..." required class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition-all focus:ring-4 focus:ring-blue-100 focus:border-blue-500"></textarea>
                        </div>
                        <button class="w-full rounded-xl bg-slate-900 py-3 text-xs font-black text-white shadow-lg shadow-slate-900/20 transition-all hover:bg-black hover:-translate-y-1" type="submit">GỬI PHÊ DUYỆT</button>
                    </form>
                </article>
            </div>
        </div>
    </div>
</section>
