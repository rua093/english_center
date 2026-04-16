<?php
require_admin_or_staff();
require_permission('admin.dashboard.view');

$academicModel = new AcademicModel();
$stats = $academicModel->dashboardStats();
$chartData = $academicModel->dashboardChartData();
$recentClasses = $academicModel->listClasses();
$recentMaterials = $academicModel->listMaterials();
$recentNotifications = $academicModel->listNotifications();

$module = 'dashboard';
$adminTitle = 'Bảng điều khiển';
$adminDescription = 'Theo dõi nhanh số liệu học viên, học vụ và doanh thu.';

$success = get_flash('success');
$error = get_flash('error');
?>

<div class="grid gap-4">
    <?php if (can_access_page('classes-academic')): ?>
        <div class="flex justify-end rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <a class="<?= ui_btn_primary_classes(); ?>" href="<?= e(page_url('classes-academic')); ?>">Vào học vụ</a>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3>Lớp học</h3><p class="text-2xl font-extrabold text-blue-700"><?= (int) $stats['class_count']; ?></p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3>Học viên</h3><p class="text-2xl font-extrabold text-blue-700"><?= (int) $stats['student_count']; ?></p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3>Giáo viên</h3><p class="text-2xl font-extrabold text-blue-700"><?= (int) $stats['teacher_count']; ?></p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h3>Bài tập</h3><p class="text-2xl font-extrabold text-blue-700"><?= (int) $stats['assignment_count']; ?></p></article>
    </div>

    <div class="grid-cols-1 lg:grid-cols-2 grid gap-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h3>Biểu đồ doanh thu 6 tháng gần nhất</h3>
            <p>Tổng học phí: <strong><?= format_money((float) $stats['tuition_total']); ?></strong> | Đã thu: <strong><?= format_money((float) $stats['tuition_paid']); ?></strong></p>
            <canvas id="tuitionChart" height="220"></canvas>
            <p id="tuitionChartFallback" class="mt-2 hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Không thể tải thư viện biểu đồ, vui lòng kiểm tra kết nối mạng.</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-1">
            <h3>Thông báo mới nhất</h3>
            <?php if (empty($recentNotifications)): ?>
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có thông báo mới.</div>
            <?php else: ?>
                <ul class="m-0 grid list-none gap-2 p-0">
                    <?php foreach (array_slice($recentNotifications, 0, 6) as $notification): ?>
                        <li>
                            <strong><?= e((string) $notification['title']); ?></strong>
                            <small><?= e((string) $notification['created_at']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </div>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Lớp mới</h3>
            <?php if (empty($recentClasses)): ?>
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có lớp học nào.</div>
            <?php else: ?>
                <ul class="m-0 grid list-none gap-2 p-0">
                    <?php foreach (array_slice($recentClasses, 0, 6) as $class): ?>
                        <li>
                            <strong><?= e((string) $class['class_name']); ?></strong>
                            <small><?= e((string) $class['status']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Tài liệu gần đây</h3>
            <?php if (empty($recentMaterials)): ?>
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có tài liệu nào.</div>
            <?php else: ?>
                <ul class="m-0 grid list-none gap-2 p-0">
                    <?php foreach (array_slice($recentMaterials, 0, 6) as $material): ?>
                        <li>
                            <strong><?= e((string) $material['title']); ?></strong>
                            <small><?= e((string) $material['course_name']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </article>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const chartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const months = Array.isArray(chartData.months) ? chartData.months : [];
    const monthLabels = months.map((item) => item.month);
    const tuitionValues = months.map((item) => Number(item.total || 0));

    const tuitionCanvas = document.getElementById('tuitionChart');
    const tuitionFallback = document.getElementById('tuitionChartFallback');

    if (tuitionCanvas && typeof Chart !== 'undefined') {
        new Chart(tuitionCanvas, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Doanh thu học phí',
                    data: tuitionValues,
                    borderColor: '#1e3a8a',
                    backgroundColor: 'rgba(30, 58, 138, 0.12)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const value = Number(context.parsed.y || 0);
                                return 'Doanh thu: ' + value.toLocaleString('vi-VN') + ' đ';
                            },
                        },
                    },
                },
            },
        });
    } else if (tuitionFallback) {
        tuitionFallback.classList.remove('hidden');
    }
</script>


