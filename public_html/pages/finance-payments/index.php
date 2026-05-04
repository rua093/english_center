<?php
require_admin_or_staff();
require_any_permission(['finance.payments.view']);

$academicModel = new AcademicModel();
$paymentsPage = max(1, (int) ($_GET['payments_page'] ?? 1));
$paymentsPerPage = ui_pagination_resolve_per_page('payments_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$transactionStatusFilter = strtolower(trim((string) ($_GET['transaction_status'] ?? '')));
if (!in_array($transactionStatusFilter, ['pending', 'success', 'failed'], true)) {
    $transactionStatusFilter = '';
}
$paymentMethodFilter = strtolower(trim((string) ($_GET['payment_method'] ?? '')));
$paymentFilters = [
    'transaction_status' => $transactionStatusFilter,
    'payment_method' => $paymentMethodFilter,
];
$paymentsTotal = $academicModel->countPaymentTransactions($searchQuery, $paymentFilters);
$paymentsTotalPages = max(1, (int) ceil($paymentsTotal / $paymentsPerPage));
if ($paymentsPage > $paymentsTotalPages) {
    $paymentsPage = $paymentsTotalPages;
}
$transactions = $academicModel->listPaymentTransactionsPage($paymentsPage, $paymentsPerPage, $searchQuery, $paymentFilters);
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
                                #<?= (int) $fee['id']; ?> - <?= e(student_dropdown_label($fee)); ?> - <?= e((string) $fee['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>">Hủy chỉnh sửa</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article
        class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="payments-finance"
        data-ajax-page-param="payments_page"
        data-ajax-search-param="search"
    >
        <h3>Danh sách giao dịch</h3>
        <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-center">
                <label class="relative block w-full md:max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input
                        type="search"
                        value="<?= e($searchQuery); ?>"
                        data-ajax-search="1"
                        placeholder="Tìm mã GD, học viên, khóa học..."
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </label>
                <select name="transaction_status" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" <?= $transactionStatusFilter === 'pending' ? 'selected' : ''; ?>>Đang chờ</option>
                    <option value="success" <?= $transactionStatusFilter === 'success' ? 'selected' : ''; ?>>Thành công</option>
                    <option value="failed" <?= $transactionStatusFilter === 'failed' ? 'selected' : ''; ?>>Thất bại</option>
                </select>
                <select name="payment_method" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">Tất cả phương thức</option>
                    <?php foreach ($paymentMethodOptions as $value => $label): ?>
                        <option value="<?= e((string) $value); ?>" <?= $paymentMethodFilter === (string) $value ? 'selected' : ''; ?>><?= e((string) $label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>Mã HV</th>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày giao dịch</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có giao dịch nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= e((string) ($txn['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($txn['full_name'] ?? 'Học viên')); ?></td>
                                <td><?= e((string) $txn['course_name']); ?></td>
                                <td><?= format_money((float) $txn['amount']); ?></td>
                                <td><span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-bold capitalize text-blue-700 whitespace-nowrap"><?= e((string) ($paymentMethodOptions[$txn['method']] ?? $txn['method'])); ?></span></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $txn['transaction_status']); ?>"><?= e((string) $txn['transaction_status']); ?></span></td>
                                <td><?= e((string) ($txn['transaction_date'] ?? '')); ?></td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdatePayment): ?>
                                            <button
                                                type="button"
                                                class="admin-row-detail-button admin-action-icon-btn"
                                                data-action-kind="detail"
                                                data-admin-row-detail="1"
                                                data-detail-url="<?= e(page_url('payments-finance', ['edit' => (int) $txn['id'], 'payments_page' => $paymentsPage, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>"
                                                data-skip-action-icon="1"
                                                title="Xem chi tiết"
                                                aria-label="Xem chi tiết"
                                            >
                                                <span class="admin-action-icon-label">Xem chi tiết</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                                </span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canUpdatePayment): ?>
                                            <a
                                                href="<?= e(page_url('payments-finance', ['edit' => (int) $txn['id'], 'payments_page' => $paymentsPage, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>"
                                                class="admin-action-icon-btn"
                                                data-action-kind="edit"
                                                data-skip-action-icon="1"
                                                title="Sửa"
                                                aria-label="Sửa"
                                            >
                                                <span class="admin-action-icon-label">Sửa</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                                    </svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDeletePayment): ?>
                                            <form method="post" action="/api/payments/delete" onsubmit="return confirm('Bạn chắc chắn muốn xóa giao dịch này?');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int) $txn['id']; ?>">
                                                <button
                                                    class="admin-action-icon-btn"
                                                    data-action-kind="delete"
                                                    data-skip-action-icon="1"
                                                    type="submit"
                                                    title="Xóa"
                                                    aria-label="Xóa"
                                                >
                                                    <span class="admin-action-icon-label">Xóa</span>
                                                    <span class="admin-action-icon-glyph" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M3 6h18"></path>
                                                            <path d="M8 6V4h8v2"></path>
                                                            <path d="M19 6l-1 14H6L5 6"></path>
                                                            <path d="M10 11v6"></path>
                                                            <path d="M14 11v6"></path>
                                                        </svg>
                                                    </span>
                                                </button>
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
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2" data-ajax-pagination="1">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="min-w-0 flex-1 font-medium" data-ajax-row-info="1">Trang <?= (int) $paymentsPage; ?>/<?= (int) $paymentsTotalPages; ?> - Tổng <?= (int) $paymentsTotal; ?> giao dịch</span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('payments-finance')); ?>">
                                <input type="hidden" name="page" value="payments-finance">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="transaction_status" value="<?= e($transactionStatusFilter); ?>">
                                <input type="hidden" name="payment_method" value="<?= e($paymentMethodFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="payments-per-page">Số dòng</label>
                                <select id="payments-per-page" name="payments_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($paymentsPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $paymentsPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($paymentsPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage - 1, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($paymentsPage < $paymentsTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage + 1, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>">Sau</a>
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


