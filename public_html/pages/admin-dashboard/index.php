<?php
require_role(['admin']);
require_permission('admin.dashboard.view');

$academicModel = new AcademicModel();
$adminModel = new AdminModel();

$stats = $academicModel->dashboardStats();
$chartData = $academicModel->dashboardChartData();
$overview = $adminModel->dashboardOverviewData();

$module = 'dashboard';
$adminTitle = 'Bảng điều khiển';
$adminDescription = 'Tổng quan tăng trưởng, doanh thu, chuyển đổi và cảm nhận học viên về trung tâm.';

$success = get_flash('success');
$error = get_flash('error');

$growth = is_array($overview['growth'] ?? null) ? $overview['growth'] : [];
$leadConversion = is_array($overview['lead_conversion'] ?? null) ? $overview['lead_conversion'] : [];
$teacherConversion = is_array($overview['teacher_conversion'] ?? null) ? $overview['teacher_conversion'] : [];
$feedback = is_array($overview['feedback'] ?? null) ? $overview['feedback'] : [];
$classStatus = is_array($overview['class_status'] ?? null) ? $overview['class_status'] : [];

$monthlyRevenueRows = is_array($chartData['months'] ?? null) ? $chartData['months'] : [];
$revenueLabels = [];
$revenueValues = [];
foreach ($monthlyRevenueRows as $monthRow) {
    $monthKey = (string) ($monthRow['month'] ?? '');
    if ($monthKey === '') {
        continue;
    }

    $parsedMonth = DateTimeImmutable::createFromFormat('Y-m', $monthKey);
    $revenueLabels[] = $parsedMonth instanceof DateTimeImmutable ? ('T' . $parsedMonth->format('m')) : $monthKey;
    $revenueValues[] = (float) ($monthRow['total'] ?? 0);
}

$latestRevenue = !empty($revenueValues) ? (float) end($revenueValues) : 0.0;
$previousRevenue = count($revenueValues) >= 2 ? (float) $revenueValues[count($revenueValues) - 2] : 0.0;
$revenueDelta = $latestRevenue - $previousRevenue;
$revenueDeltaPercent = $previousRevenue > 0 ? round(($revenueDelta / $previousRevenue) * 100, 1) : ($latestRevenue > 0 ? 100.0 : 0.0);
$revenueDeltaText = number_format(abs($revenueDeltaPercent), 1, ',', '.');

$tuitionTotal = (float) ($stats['tuition_total'] ?? 0);
$tuitionPaid = (float) ($stats['tuition_paid'] ?? 0);
$tuitionDebt = max(0, $tuitionTotal - $tuitionPaid);
$collectionRate = $tuitionTotal > 0 ? round(($tuitionPaid / $tuitionTotal) * 100, 1) : 0.0;

$studentCurrentMonth = (int) ($growth['student_current_month'] ?? 0);
$studentPreviousMonth = (int) ($growth['student_previous_month'] ?? 0);
$teacherCurrentMonth = (int) ($growth['teacher_current_month'] ?? 0);
$teacherPreviousMonth = (int) ($growth['teacher_previous_month'] ?? 0);

$studentDelta = $studentCurrentMonth - $studentPreviousMonth;
$teacherDelta = $teacherCurrentMonth - $teacherPreviousMonth;

$classStatusLabels = [
    'upcoming' => 'Sắp mở',
    'active' => 'Đang học',
    'graduated' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
];

$heroStats = [
    [
        'label' => 'Học viên mới tháng này',
        'value' => (string) $studentCurrentMonth,
        'note' => ($studentDelta >= 0 ? '+' : '') . $studentDelta . ' so với tháng trước',
        'tone' => 'pink',
    ],
    [
        'label' => 'Giáo viên mới tháng này',
        'value' => (string) $teacherCurrentMonth,
        'note' => ($teacherDelta >= 0 ? '+' : '') . $teacherDelta . ' so với tháng trước',
        'tone' => 'amber',
    ],
    [
        'label' => 'Chuyển đổi học viên',
        'value' => number_format((float) ($leadConversion['conversion_rate'] ?? 0), 1, ',', '.') . '%',
        'note' => (int) ($leadConversion['converted'] ?? 0) . '/' . (int) ($leadConversion['total'] ?? 0) . ' lead đã thành học viên',
        'tone' => 'emerald',
    ],
    [
        'label' => 'Chuyển đổi giáo viên',
        'value' => number_format((float) ($teacherConversion['conversion_rate'] ?? 0), 1, ',', '.') . '%',
        'note' => (int) ($teacherConversion['converted'] ?? 0) . '/' . (int) ($teacherConversion['total'] ?? 0) . ' hồ sơ đã thành tài khoản',
        'tone' => 'violet',
    ],
    [
        'label' => 'Đánh giá trung bình',
        'value' => number_format((float) ($feedback['avg_rating'] ?? 0), 1, ',', '.') . '/5',
        'note' => (int) ($feedback['total'] ?? 0) . ' lượt đánh giá, công khai ' . number_format((float) ($feedback['public_rate'] ?? 0), 1, ',', '.') . '%',
        'tone' => 'sky',
    ],
    [
        'label' => 'Tỷ lệ thu học phí',
        'value' => number_format($collectionRate, 1, ',', '.') . '%',
        'note' => 'Còn nợ ' . format_money($tuitionDebt),
        'tone' => 'fuchsia',
    ],
];

$dashboardPayload = [
    'revenue' => [
        'labels' => $revenueLabels,
        'values' => $revenueValues,
    ],
    'growth' => [
        'labels' => is_array($growth['labels'] ?? null) ? $growth['labels'] : [],
        'students' => is_array($growth['students'] ?? null) ? $growth['students'] : [],
        'teachers' => is_array($growth['teachers'] ?? null) ? $growth['teachers'] : [],
    ],
    'conversion' => [
        'labels' => ['Lead học viên', 'Hồ sơ giáo viên'],
        'rates' => [
            (float) ($leadConversion['conversion_rate'] ?? 0),
            (float) ($teacherConversion['conversion_rate'] ?? 0),
        ],
    ],
    'feedback' => [
        'labels' => ['1 sao', '2 sao', '3 sao', '4 sao', '5 sao'],
        'values' => is_array($feedback['distribution'] ?? null) ? $feedback['distribution'] : [0, 0, 0, 0, 0],
    ],
];
?>

<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(244,114,182,0.24),_transparent_35%),radial-gradient(circle_at_top_right,_rgba(34,211,238,0.22),_transparent_32%),linear-gradient(135deg,_#111827_0%,_#1e1b4b_44%,_#172554_100%)] p-5 text-white shadow-[0_26px_80px_rgba(15,23,42,0.28)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-cyan-100">Điều hành trung tâm</span>
                <h2 class="mt-3 text-2xl font-black tracking-tight md:text-3xl">Bức tranh tổng quan để nhìn nhanh tăng trưởng, chuyển đổi và mức độ hài lòng của học viên.</h2>
                <p class="mt-2 text-sm leading-7 text-slate-200">Trang này chỉ giữ các chỉ số ở tầm quản trị trung tâm. Các chi tiết vận hành từng phân hệ nên theo dõi ở trang chuyên biệt.</p>
            </div>
            <?php if (can_access_page('classes-academic')): ?>
                <a class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white hover:text-slate-900" href="<?= e(page_url('classes-academic')); ?>">Đi tới học vụ</a>
            <?php endif; ?>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($heroStats as $item): ?>
                <?php
                $tone = (string) ($item['tone'] ?? 'sky');
                $toneClasses = [
                    'pink' => 'from-pink-500/28 to-rose-400/12 border-pink-300/30',
                    'amber' => 'from-amber-400/28 to-orange-400/12 border-amber-300/30',
                    'emerald' => 'from-emerald-400/28 to-lime-300/12 border-emerald-300/30',
                    'violet' => 'from-violet-400/28 to-fuchsia-300/12 border-violet-300/30',
                    'sky' => 'from-sky-400/28 to-cyan-300/12 border-sky-300/30',
                    'fuchsia' => 'from-fuchsia-400/28 to-pink-300/12 border-fuchsia-300/30',
                ];
                ?>
                <article class="rounded-[1.35rem] border bg-gradient-to-br <?= e($toneClasses[$tone] ?? $toneClasses['sky']); ?> p-4 backdrop-blur-sm">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-white/70"><?= e((string) $item['label']); ?></p>
                    <p class="mt-2 text-3xl font-black text-white"><?= e((string) $item['value']); ?></p>
                    <p class="mt-2 text-xs leading-5 text-white/75"><?= e((string) $item['note']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="mb-1 text-lg font-black text-slate-900">Doanh thu học phí 6 tháng</h3>
                    <p class="text-sm text-slate-500">Biểu đồ cột giúp thấy ngay tháng nào đang tăng hoặc chững lại.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-3 py-2 text-right">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Tháng gần nhất</p>
                    <p class="mt-1 text-lg font-black text-slate-900"><?= e(format_money($latestRevenue)); ?></p>
                    <p class="text-xs <?= $revenueDelta >= 0 ? 'text-emerald-600' : 'text-rose-600'; ?>">
                        <?= e(($revenueDelta >= 0 ? '+' : '-') . $revenueDeltaText . '%'); ?>
                    </p>
                </div>
            </div>
            <div class="h-[250px]">
                <canvas id="dashboardRevenueChart"></canvas>
            </div>
        </article>

        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3">
                <h3 class="mb-1 text-lg font-black text-slate-900">Trạng thái lớp học</h3>
                <p class="text-sm text-slate-500">Một góc nhìn nhanh về quy mô vận hành hiện tại của trung tâm.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <?php foreach ($classStatusLabels as $statusKey => $statusLabel): ?>
                    <?php
                    $statusValue = (int) ($classStatus[$statusKey] ?? 0);
                    $statusTone = [
                        'upcoming' => 'border-sky-200 bg-sky-50 text-sky-900',
                        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                        'graduated' => 'border-violet-200 bg-violet-50 text-violet-900',
                        'cancelled' => 'border-rose-200 bg-rose-50 text-rose-900',
                    ];
                    ?>
                    <div class="rounded-2xl border p-4 <?= e($statusTone[$statusKey] ?? 'border-slate-200 bg-slate-50 text-slate-900'); ?>">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] opacity-70"><?= e($statusLabel); ?></p>
                        <p class="mt-2 text-3xl font-black"><?= $statusValue; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm leading-6 text-slate-700">Hiện trung tâm có <strong><?= (int) ($stats['class_count'] ?? 0); ?></strong> lớp trong hệ thống, với <strong><?= (int) ($classStatus['active'] ?? 0); ?></strong> lớp đang học và <strong><?= (int) ($classStatus['upcoming'] ?? 0); ?></strong> lớp sắp mở.</p>
            </div>
        </article>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm xl:col-span-1">
            <div class="mb-3">
                <h3 class="mb-1 text-lg font-black text-slate-900">Học viên và giáo viên mới</h3>
                <p class="text-sm text-slate-500">So sánh nhịp tăng trưởng của hai nhóm qua từng tháng.</p>
            </div>
            <div class="h-[260px]">
                <canvas id="dashboardGrowthChart"></canvas>
            </div>
        </article>

        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm xl:col-span-1">
            <div class="mb-3">
                <h3 class="mb-1 text-lg font-black text-slate-900">Tỷ lệ chuyển đổi</h3>
                <p class="text-sm text-slate-500">Nhìn nhanh chất lượng chuyển đổi đầu vào của trung tâm.</p>
            </div>
            <div class="h-[260px]">
                <canvas id="dashboardConversionChart"></canvas>
            </div>
        </article>

        <article class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm xl:col-span-1">
            <div class="mb-3">
                <h3 class="mb-1 text-lg font-black text-slate-900">Phân bố đánh giá học viên</h3>
                <p class="text-sm text-slate-500">Thấy ngay cảm nhận chung của học viên về trung tâm qua các mức sao.</p>
            </div>
            <div class="h-[260px]">
                <canvas id="dashboardFeedbackChart"></canvas>
            </div>
        </article>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const payload = <?= json_encode($dashboardPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        if (typeof Chart === 'undefined') {
            return;
        }

        Chart.defaults.font.family = 'Manrope, system-ui, sans-serif';
        Chart.defaults.color = '#475569';

        function formatMoney(value) {
            return Number(value || 0).toLocaleString('vi-VN') + ' đ';
        }

        function createChart(canvasId, config) {
            const canvas = document.getElementById(canvasId);
            if (!(canvas instanceof HTMLCanvasElement)) {
                return null;
            }

            return new Chart(canvas, config);
        }

        createChart('dashboardRevenueChart', {
            type: 'bar',
            data: {
                labels: Array.isArray(payload.revenue && payload.revenue.labels) ? payload.revenue.labels : [],
                datasets: [{
                    label: 'Doanh thu',
                    data: Array.isArray(payload.revenue && payload.revenue.values) ? payload.revenue.values : [],
                    borderRadius: 12,
                    borderSkipped: false,
                    backgroundColor: ['#fb7185', '#f97316', '#f59e0b', '#22c55e', '#06b6d4', '#6366f1'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return 'Doanh thu: ' + formatMoney(context.parsed.y);
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback(value) {
                                return Number(value).toLocaleString('vi-VN');
                            },
                        },
                    },
                },
            },
        });

        createChart('dashboardGrowthChart', {
            type: 'line',
            data: {
                labels: Array.isArray(payload.growth && payload.growth.labels) ? payload.growth.labels : [],
                datasets: [
                    {
                        label: 'Học viên mới',
                        data: Array.isArray(payload.growth && payload.growth.students) ? payload.growth.students : [],
                        borderColor: '#ec4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.16)',
                        pointBackgroundColor: '#ec4899',
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Giáo viên mới',
                        data: Array.isArray(payload.growth && payload.growth.teachers) ? payload.growth.teachers : [],
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        pointBackgroundColor: '#f59e0b',
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });

        createChart('dashboardConversionChart', {
            type: 'bar',
            data: {
                labels: Array.isArray(payload.conversion && payload.conversion.labels) ? payload.conversion.labels : [],
                datasets: [{
                    label: 'Tỷ lệ chuyển đổi (%)',
                    data: Array.isArray(payload.conversion && payload.conversion.rates) ? payload.conversion.rates : [],
                    backgroundColor: ['#14b8a6', '#8b5cf6'],
                    borderRadius: 14,
                    borderSkipped: false,
                    maxBarThickness: 58,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return 'Tỷ lệ: ' + Number(context.parsed.x || 0).toLocaleString('vi-VN') + '%';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback(value) {
                                return Number(value).toLocaleString('vi-VN') + '%';
                            },
                        },
                    },
                    y: {
                        grid: { display: false },
                    },
                },
            },
        });

        createChart('dashboardFeedbackChart', {
            type: 'doughnut',
            data: {
                labels: Array.isArray(payload.feedback && payload.feedback.labels) ? payload.feedback.labels : [],
                datasets: [{
                    data: Array.isArray(payload.feedback && payload.feedback.values) ? payload.feedback.values : [],
                    backgroundColor: ['#fb7185', '#f97316', '#facc15', '#38bdf8', '#34d399'],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return context.label + ': ' + Number(context.raw || 0).toLocaleString('vi-VN') + ' lượt';
                            },
                        },
                    },
                },
            },
        });
    })();
</script>
