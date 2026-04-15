<?php
require_admin_or_staff();
require_permission('finance.payment.view');

$academicModel = new AcademicModel();
$transactions = $academicModel->listPaymentTransactions();

$module = 'payments';
$adminTitle = 'Giao dịch thanh toán';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1>Giao dịch thanh toán</h1>
                <p>Danh sách tất cả các giao dịch thanh toán học phí.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>Học viên</th>
                            <th>Khóa học</th>
                            <th>Số tiền</th>
                            <th>Phương thức</th>
                            <th>Ngày giao dịch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có giao dịch nào.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $txn): ?>
                                <tr>
                                    <td><?= e((string) $txn['student_name']); ?></td>
                                    <td><?= e((string) $txn['course_name']); ?></td>
                                    <td><?= format_money((float) $txn['amount']); ?></td>
                                    <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize border-blue-200 bg-blue-50 text-blue-700"><?= e((string) $txn['method']); ?></span></td>
                                    <td><?= e((string) ($txn['transaction_date'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>



