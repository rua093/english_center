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
$editingPayment = null;
if (!empty($_GET['edit'])) {
    $editingPayment = $academicModel->findPaymentTransaction((int) $_GET['edit']);
}

$tuitionOptions = $academicModel->listTuitionFeesPage(1, 200);
$selectedTuitionFee = null;
$selectedTuitionId = (int) ($_GET['tuition_id'] ?? 0);

$prefillTuitionId = $editingPayment ? (int) ($editingPayment['tuition_fee_id'] ?? 0) : $selectedTuitionId;
if ($prefillTuitionId > 0) {
    foreach ($tuitionOptions as $fee) {
        if ((int) ($fee['id'] ?? 0) === $prefillTuitionId) {
            $selectedTuitionFee = $fee;
            break;
        }
    }

    if (!$selectedTuitionFee) {
        $selectedTuitionFee = $academicModel->findTuitionFee($prefillTuitionId);
    }

    if ($selectedTuitionFee) {
        $filteredTuitionOptions = [];
        foreach ($tuitionOptions as $fee) {
            if ((int) ($fee['id'] ?? 0) !== $prefillTuitionId) {
                $filteredTuitionOptions[] = $fee;
            }
        }

        array_unshift($filteredTuitionOptions, $selectedTuitionFee);
        $tuitionOptions = $filteredTuitionOptions;
    }
}

$tuitionMeta = [];
foreach ($tuitionOptions as $fee) {
    $feeId = (int) ($fee['id'] ?? 0);
    if ($feeId <= 0) {
        continue;
    }

    $totalAmount = (float) ($fee['total_amount'] ?? 0);
    $amountPaid = (float) ($fee['amount_paid'] ?? 0);
    $tuitionMeta[$feeId] = [
        'total_amount' => $totalAmount,
        'amount_paid' => $amountPaid,
        'remaining' => max(0, $totalAmount - $amountPaid),
    ];
}
$prefillAmount = 0.0;
if (!$editingPayment && $prefillTuitionId > 0 && isset($tuitionMeta[$prefillTuitionId])) {
    $prefillAmount = (float) ($tuitionMeta[$prefillTuitionId]['remaining'] ?? 0);
}

$paymentMethodOptions = [
    'bank_transfer' => t('admin.payments.method_bank_transfer'),
    'cash'          => t('admin.payments.method_cash'),
    'ewallet'       => t('admin.payments.method_ewallet'),
    'card'          => t('admin.payments.method_card'),
    'other'         => t('admin.payments.method_other'),
];
$selectedPaymentMethod = (string) ($editingPayment['payment_method'] ?? 'bank_transfer');
if ($selectedPaymentMethod !== '' && !isset($paymentMethodOptions[$selectedPaymentMethod])) {
    $paymentMethodOptions[$selectedPaymentMethod] = $selectedPaymentMethod;
}

$module = 'payments';
$adminTitle = t('admin.payments.title');

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
        <article id="payment-create-section" class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= e($editingPayment ? t('admin.payments.edit') : t('admin.payments.add')); ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/payments/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingPayment['id'] ?? 0); ?>">
                <label>
                    <?= e(t('admin.payments.invoice')); ?>
                    <select id="payment-tuition-select" name="tuition_fee_id" required>
                        <option value=""><?= e(t('admin.payments.choose_invoice')); ?></option>
                        <?php foreach ($tuitionOptions as $fee): ?>
                            <?php
                            $feeId = (int) ($fee['id'] ?? 0);
                            $selected = $prefillTuitionId > 0 && $prefillTuitionId === $feeId;
                            $meta = $tuitionMeta[$feeId] ?? ['total_amount' => 0, 'amount_paid' => 0, 'remaining' => 0];
                            ?>
                            <option
                                value="<?= $feeId; ?>"
                                data-total-amount="<?= e(number_format((float) ($meta['total_amount'] ?? 0), 2, '.', '')); ?>"
                                data-amount-paid="<?= e(number_format((float) ($meta['amount_paid'] ?? 0), 2, '.', '')); ?>"
                                data-remaining-amount="<?= e(number_format((float) ($meta['remaining'] ?? 0), 2, '.', '')); ?>"
                                <?= $selected ? 'selected' : ''; ?>
                            >
                                #<?= (int) $fee['id']; ?> - <?= e(student_dropdown_label($fee)); ?> - <?= e((string) $fee['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4" id="payment-invoice-preview">
                    <h4 class="text-xs font-extrabold uppercase tracking-wide text-slate-600"><?= e(t('admin.payments.invoice_info')); ?></h4>
                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= e(t('admin.payments.total_due')); ?></p>
                            <p id="payment-total-amount" class="mt-1 text-base font-extrabold text-slate-800"><?= e('0 ' . t('admin.common.currency_suffix')); ?></p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= e(t('admin.payments.amount_paid')); ?></p>
                            <p id="payment-amount-paid" class="mt-1 text-base font-extrabold text-emerald-700"><?= e('0 ' . t('admin.common.currency_suffix')); ?></p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?= e(t('admin.payments.remaining')); ?></p>
                            <p id="payment-amount-remaining" class="mt-1 text-base font-extrabold text-rose-700"><?= e('0 ' . t('admin.common.currency_suffix')); ?></p>
                        </div>
                    </div>
                </div>
                <label>
                    <?= e(t('admin.payments.method')); ?>
                    <select name="payment_method" required>
                        <?php foreach ($paymentMethodOptions as $value => $label): ?>
                            <option value="<?= e((string) $value); ?>" <?= $selectedPaymentMethod === (string) $value ? 'selected' : ''; ?>><?= e((string) $label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.payments.amount')); ?>
                    <input
                        type="number"
                        step="1000"
                        min="0"
                        name="amount"
                        id="payment-amount-input"
                        data-autofill="<?= $editingPayment ? '0' : '1'; ?>"
                        required
                        value="<?= e((string) ($editingPayment['amount'] ?? ($prefillAmount > 0 ? (string) $prefillAmount : ''))); ?>"
                    >
                </label>
                <label>
                    <?= e(t('admin.payments.status')); ?>
                    <select name="transaction_status">
                        <option value="pending" <?= (($editingPayment['transaction_status'] ?? 'pending') === 'pending') ? 'selected' : ''; ?>>pending</option>
                        <option value="success" <?= (($editingPayment['transaction_status'] ?? '') === 'success') ? 'selected' : ''; ?>>success</option>
                        <option value="failed" <?= (($editingPayment['transaction_status'] ?? '') === 'failed') ? 'selected' : ''; ?>>failed</option>
                    </select>
                </label>
                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e($editingPayment ? t('admin.payments.update') : t('admin.payments.create')); ?></button>
                    <?php if ($editingPayment): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>"><?= e(t('admin.common.cancel')); ?></a>
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
        <h3><?= e(t('admin.payments.list')); ?></h3>
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
                        placeholder="<?= e(t('admin.payments.search_placeholder')); ?>"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </label>
                <select name="transaction_status" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value=""><?= e(t('admin.payments.status_all')); ?></option>
                    <option value="pending" <?= $transactionStatusFilter === 'pending' ? 'selected' : ''; ?>><?= e(t('admin.payments.status_pending')); ?></option>
                    <option value="success" <?= $transactionStatusFilter === 'success' ? 'selected' : ''; ?>><?= e(t('admin.payments.status_success')); ?></option>
                    <option value="failed" <?= $transactionStatusFilter === 'failed' ? 'selected' : ''; ?>><?= e(t('admin.payments.status_failed')); ?></option>
                </select>
                <select name="payment_method" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value=""><?= e(t('admin.payments.method_all')); ?></option>
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
                        <th><?= e(t('admin.payments.table_student_code')); ?></th>
                        <th><?= e(t('admin.payments.table_student')); ?></th>
                        <th><?= e(t('admin.payments.table_course')); ?></th>
                        <th><?= e(t('admin.payments.table_amount')); ?></th>
                        <th><?= e(t('admin.payments.table_method')); ?></th>
                        <th><?= e(t('admin.payments.table_status')); ?></th>
                        <th><?= e(t('admin.payments.table_date')); ?></th>
                        <th><?= e(t('admin.common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.payments.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= e((string) ($txn['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($txn['full_name'] ?? t('admin.payments.student_fallback'))); ?></td>
                                <td><?= e((string) $txn['course_name']); ?></td>
                                <td><?= format_money((float) $txn['amount']); ?></td>
                                <td><span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-bold capitalize text-blue-700 whitespace-nowrap"><?= e((string) ($paymentMethodOptions[$txn['method']] ?? $txn['method'])); ?></span></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $txn['transaction_status']); ?>"><?= e((string) $txn['transaction_status']); ?></span></td>
                                <td><?= e(ui_format_datetime((string) ($txn['transaction_date'] ?? ''))); ?></td>
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
                                                title="<?= e(t('admin.common.view_detail')); ?>"
                                                aria-label="<?= e(t('admin.common.view_detail')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.common.view_detail')); ?></span>
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
                                                title="<?= e(t('admin.common.edit')); ?>"
                                                aria-label="<?= e(t('admin.common.edit')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.common.edit')); ?></span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                                    </svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDeletePayment): ?>
                                            <form method="post" action="/api/payments/delete" onsubmit="return confirm('<?= e(t('admin.payments.delete_confirm')); ?>');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int) $txn['id']; ?>">
                                                <button
                                                    class="admin-action-icon-btn"
                                                    data-action-kind="delete"
                                                    data-skip-action-icon="1"
                                                    type="submit"
                                                    title="<?= e(t('admin.common.delete')); ?>"
                                                    aria-label="<?= e(t('admin.common.delete')); ?>"
                                                >
                                                    <span class="admin-action-icon-label"><?= e(t('admin.common.delete')); ?></span>
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
                        <span class="min-w-0 flex-1 font-medium" data-ajax-row-info="1"><?= e(t('admin.payments.page_info', ['current' => (int) $paymentsPage, 'total' => (int) $paymentsTotalPages, 'count' => (int) $paymentsTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('payments-finance')); ?>">
                                <input type="hidden" name="page" value="payments-finance">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="transaction_status" value="<?= e($transactionStatusFilter); ?>">
                                <input type="hidden" name="payment_method" value="<?= e($paymentMethodFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="payments-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="payments-per-page" name="payments_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($paymentsPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $paymentsPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($paymentsPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage - 1, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($paymentsPage < $paymentsTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('payments-finance', ['payments_page' => $paymentsPage + 1, 'payments_per_page' => $paymentsPerPage, 'search' => $searchQuery, 'transaction_status' => $transactionStatusFilter, 'payment_method' => $paymentMethodFilter])); ?>"><?= e(t('admin.common.next')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>

<script>
    (function () {
        function initPaymentAutoFill() {
            const tuitionSelect = document.getElementById('payment-tuition-select');
            const totalAmountEl = document.getElementById('payment-total-amount');
            const amountPaidEl = document.getElementById('payment-amount-paid');
            const remainingEl = document.getElementById('payment-amount-remaining');
            const amountInput = document.getElementById('payment-amount-input');
            const section = document.getElementById('payment-create-section');
            const moneyLocale = <?= json_encode(current_locale() === 'en' ? 'en-US' : 'vi-VN'); ?>;
            const moneySuffix = <?= json_encode(t('admin.common.currency_suffix'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

            if (!tuitionSelect || !totalAmountEl) return;

            function formatMoney(value) {
                const amount = Number.isFinite(value) ? value : 0;
                const formatted = new Intl.NumberFormat(moneyLocale).format(Math.max(0, Math.round(amount)));
                return moneySuffix ? formatted + ' ' + moneySuffix : formatted;
            }

            function updateInvoicePreview() {
                const selectedValue = tuitionSelect.value;
                let selectedOption = null;
                if (selectedValue !== '') {
                    for (const option of tuitionSelect.options) {
                        if (option.value === selectedValue) {
                            selectedOption = option;
                            break;
                        }
                    }
                }
                if (!selectedOption || !selectedOption.value) {
                    const emptyMoney = formatMoney(0);
                    totalAmountEl.textContent = emptyMoney;
                    amountPaidEl.textContent = emptyMoney;
                    remainingEl.textContent = emptyMoney;
                    return;
                }

                const totalAmount = Number(selectedOption.dataset.totalAmount || 0);
                const amountPaid = Number(selectedOption.dataset.amountPaid || 0);
                const remainingAmount = Number(selectedOption.dataset.remainingAmount || 0);

                totalAmountEl.textContent = formatMoney(totalAmount);
                amountPaidEl.textContent = formatMoney(amountPaid);
                remainingEl.textContent = formatMoney(remainingAmount);

                if (amountInput && amountInput.dataset.autofill === '1') {
                    amountInput.value = remainingAmount > 0 ? Math.round(remainingAmount).toString() : '';
                }
            }

            tuitionSelect.addEventListener('change', function () {
                if (amountInput) amountInput.dataset.autofill = '1';
                updateInvoicePreview();
            });

            const initialValue = tuitionSelect.value;
if (initialValue) {
    let checkCount = 0;
    const checkTomSelect = setInterval(function() {
        // Chống lỗi: Nếu framework AJAX đập HTML cũ đi xây lại, ngưng vòng lặp hiện tại
        if (!document.body.contains(tuitionSelect)) {
            clearInterval(checkTomSelect);
            return;
        }

        checkCount++;
        if (tuitionSelect.tomselect) {
            clearInterval(checkTomSelect);
            tuitionSelect.tomselect.setValue(initialValue, true);
            tuitionSelect.value = initialValue; 
            updateInvoicePreview();
            
            // Đợi DOM và các request AJAX ban đầu ổn định rồi mới cuộn mượt
            setTimeout(() => {
                const currentSection = document.getElementById('payment-create-section');
                if (currentSection) currentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        } 
        else if (checkCount > 20) {
            clearInterval(checkTomSelect);
            tuitionSelect.value = initialValue;
            updateInvoicePreview();
            
            setTimeout(() => {
                const currentSection = document.getElementById('payment-create-section');
                if (currentSection) currentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }, 100);
}
        }

        // Logic thay thế DOMContentLoaded:
        // Nếu trang đang tải thì đợi, nếu tải xong rồi thì chạy luôn.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPaymentAutoFill);
        } else {
            setTimeout(initPaymentAutoFill, 50); // Cho HTML thời gian render nếu gọi qua AJAX
        }
    })();
</script>

