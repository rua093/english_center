<?php
require_admin_or_staff();
require_any_permission(['finance.payments.view']);

$academicModel = new AcademicModel();
$paymentsPage = max(1, (int) ($_GET['payments_page'] ?? 1));
$paymentsPerPage = ui_pagination_resolve_per_page('payments_per_page', 10);
$paymentsTotal = $academicModel->countPaymentTransactions();
$paymentsTotalPages = max(1, (int) ceil($paymentsTotal / $paymentsPerPage));
if ($paymentsPage > $paymentsTotalPages) {
    $paymentsPage = $paymentsTotalPages;
}
$transactions = $academicModel->listPaymentTransactionsPage($paymentsPage, $paymentsPerPage);
$paymentsPerPageOptions = ui_pagination_per_page_options();
$tuitionOptions = $academicModel->listTuitionFeesPage(1, 200);

$editingPayment = null;
if (!empty($_GET['edit'])) {
    $editingPayment = $academicModel->findPaymentTransaction((int) $_GET['edit']);
}

$paymentMethodOptions = [
    'bank_transfer' => 'Chuyển khoản', // Đổi chữ hiển thị
    'cash'          => 'Tiền mặt',               // Đổi chữ hiển thị
    'ewallet'       => 'Ví điện tử',
    'card'          => 'Thẻ tín dụng',
    'other'         => 'Phương thức khác',
];
$selectedPaymentMethod = (string) ($editingPayment['payment_method'] ?? 'bank_transfer');
if ($selectedPaymentMethod !== '' && !isset($paymentMethodOptions[$selectedPaymentMethod])) {
    $paymentMethodOptions[$selectedPaymentMethod] = $selectedPaymentMethod;
}

$module = 'payments';
$adminTitle = 'Giao dịch thanh toán';

$canCreatePayment = has_permission('finance.payments.create');
$canUpdatePayment = has_permission('finance.payments.update');
$canDeletePayment = has_permission('finance.payments.delete');

$success = get_flash('success');
$error = get_flash('error');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreatePayment || ($canUpdatePayment && $editingPayment)): ?>
        <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingPayment ? 'Sửa giao dịch thanh toán' : 'Tạo giao dịch thanh toán'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/payments/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingPayment['id'] ?? 0); ?>">
                <label>
                    Hóa đơn học phí
                    <select id="payment-tuition-select" name="tuition_fee_id" required>
                        <option value="">-- Chọn hóa đơn --</option>
                        <?php foreach ($tuitionOptions as $fee): ?>
                            <option value="<?= (int) $fee['id']; ?>" <?= (int) ($editingPayment['tuition_fee_id'] ?? 0) === (int) $fee['id'] ? 'selected' : ''; ?>>
                                #<?= (int) $fee['id']; ?> - <?= e((string) $fee['full_name']); ?> - <?= e((string) $fee['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Mã giao dịch
                    <input type="text" name="transaction_no" value="<?= e((string) ($editingPayment['transaction_no'] ?? '')); ?>" placeholder="Để trống để hệ thống tự sinh cho giao dịch tại trung tâm">
                </label>
                <label>
                    Phương thức
                    <select name="payment_method" required>
                        <?php foreach ($paymentMethodOptions as $value => $label): ?>
                            <option value="<?= e((string) $value); ?>" <?= $selectedPaymentMethod === (string) $value ? 'selected' : ''; ?>><?= e((string) $label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Số tiền
                    <input type="number" step="1000" min="0" name="amount" required value="<?= e((string) ($editingPayment['amount'] ?? '')); ?>">
                </label>
                <label>
                    Trạng thái
                    <select name="transaction_status">
                        <option value="pending" <?= (($editingPayment['transaction_status'] ?? 'pending') === 'pending') ? 'selected' : ''; ?>>pending</option>
                        <option value="success" <?= (($editingPayment['transaction_status'] ?? '') === 'success') ? 'selected' : ''; ?>>success</option>
                        <option value="failed" <?= (($editingPayment['transaction_status'] ?? '') === 'failed') ? 'selected' : ''; ?>>failed</option>
                    </select>
                </label>
                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= $editingPayment ? 'Cập nhật giao dịch' : 'Tạo giao dịch'; ?></button>
                    <?php if ($editingPayment): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('payments-finance')); ?>">Hủy chỉnh sửa</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách giao dịch</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Mã giao dịch</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày giao dịch</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có giao dịch nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= e((string) $txn['full_name']); ?></td>
                                <td><?= e((string) $txn['course_name']); ?></td>
                                <td><?= e((string) $txn['transaction_no']); ?></td>
                                <td><?= format_money((float) $txn['amount']); ?></td>
                                <td><span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-bold capitalize text-blue-700 whitespace-nowrap"><?= e((string) ($paymentMethodOptions[$txn['method']] ?? $txn['method'])); ?></span></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $txn['transaction_status']); ?>"><?= e((string) $txn['transaction_status']); ?></span></td>
                                <td><?= e((string) ($txn['transaction_date'] ?? '')); ?></td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdatePayment): ?>
                                            <a class="text-sm font-semibold text-blue-700 hover:underline" href="<?= e(page_url('payments-finance', ['edit' => (int) $txn['id'], 'payments_page' => $paymentsPage, 'payments_per_page' => $paymentsPerPage])); ?>">Sửa</a>
                                        <?php endif; ?>
                                        <?php if ($canDeletePayment): ?>
                                            <form method="post" action="/api/payments/delete" onsubmit="return confirm('Bạn chắc chắn muốn xóa giao dịch này?');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int) $txn['id']; ?>">
                                                <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($paymentsTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $paymentsPage; ?>/<?= (int) $paymentsTotalPages; ?> - Tổng <?= (int) $paymentsTotal; ?> giao dịch</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('payments-finance')); ?>">
                                <input type="hidden" name="page" value="payments-finance">
                                <label class="text-[11px] font-semibold text-slate-500" for="payments-per-page">Số dòng</label>
                                <select id="payments-per-page" name="payments_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($paymentsPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $paymentsPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($paymentsPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage - 1, 'payments_per_page' => $paymentsPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($paymentsPage < $paymentsTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage + 1, 'payments_per_page' => $paymentsPerPage])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>


