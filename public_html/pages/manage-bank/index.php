<?php
set_flash('info', 'Chuc nang quan ly ngan hang da duoc ngung su dung.');
redirect(page_url('admin'));

$academicModel = new AcademicModel();
$editingBank = null;
if (!empty($_GET['edit'])) {
    $editingBank = $academicModel->findBankAccount((int) $_GET['edit']);
}

$bankPage = max(1, (int) ($_GET['bank_page'] ?? 1));
$bankPerPage = ui_pagination_resolve_per_page('bank_per_page', 10);
$bankTotal = $academicModel->countBankAccounts();
$bankTotalPages = max(1, (int) ceil($bankTotal / $bankPerPage));
if ($bankPage > $bankTotalPages) {
    $bankPage = $bankTotalPages;
}
$bankAccounts = $academicModel->listBankAccountsPage($bankPage, $bankPerPage);
$bankPerPageOptions = ui_pagination_per_page_options();

$module = 'bank';
$adminTitle = 'Quản lý ngân hàng';

$success = get_flash('success');
$error = get_flash('error');
?>
<div class="grid gap-4">
    <?php
    $canCreateBank = has_permission('bank.create');
    $canUpdateBank = has_permission('bank.update');
    $canDeleteBank = has_permission('bank.delete');
    ?>

    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreateBank || $canUpdateBank): ?>
        <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingBank ? 'Sửa tài khoản' : 'Thêm tài khoản'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/banks/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingBank['id'] ?? 0); ?>">
                <label>
                    Mã BIN
                    <input type="text" name="bin" value="<?= e((string) ($editingBank['bin'] ?? '')); ?>" required>
                </label>
                <label>
                    Số tài khoản
                    <input type="text" name="account_number" value="<?= e((string) ($editingBank['account_number'] ?? '')); ?>" required>
                </label>
                <label>
                    Ngân hàng
                    <input type="text" name="bank_name" value="<?= e((string) ($editingBank['bank_name'] ?? '')); ?>" required>
                </label>
                <label>
                    Chủ tài khoản
                    <input type="text" name="account_holder" value="<?= e((string) ($editingBank['account_holder'] ?? '')); ?>" required>
                </label>
                <label class="md:col-span-2">
                    Liên kết QR tĩnh
                    <input type="text" name="qr_code_static_url" value="<?= e((string) ($editingBank['qr_code_static_url'] ?? '')); ?>">
                </label>
                <label class="md:col-span-2 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="is_default" value="1" <?= (int) ($editingBank['is_default'] ?? $editingBank['is_primary'] ?? 0) === 1 ? 'checked' : ''; ?>>
                    Tài khoản chính
                </label>
                <div class="md:col-span-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu tài khoản</button>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách tài khoản</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Số tài khoản</th>
                        <th>Ngân hàng</th>
                        <th>BIN</th>
                        <th>Chủ tài khoản</th>
                        <th>Chính</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bankAccounts)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có tài khoản ngân hàng nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bankAccounts as $bank): ?>
                            <tr>
                                <td><?= e((string) $bank['account_name']); ?></td>
                                <td><?= e((string) $bank['account_number']); ?></td>
                                <td><?= e((string) $bank['bank_name']); ?></td>
                                <td><?= e((string) ($bank['bin'] ?? '')); ?></td>
                                <td><?= e((string) $bank['account_holder']); ?></td>
                                <td><?= (int) ($bank['is_default'] ?? $bank['is_primary'] ?? 0) === 1 ? 'Có' : 'Không'; ?></td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateBank): ?>
                                            <a href="<?= e(page_url('bank-manage', ['edit' => (int) $bank['id'], 'bank_page' => $bankPage, 'bank_per_page' => $bankPerPage])); ?>">Sửa</a>
                                        <?php endif; ?>
                                        <?php if ($canDeleteBank): ?>
                                            <form class="inline-block" method="post" action="/api/banks/delete?id=<?= (int) $bank['id']; ?>" onsubmit="return confirm('Có chắc không?')">
                                                <?= csrf_input(); ?>
                                                <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Xóa</button>
                                            </form>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($bankTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $bankPage; ?>/<?= (int) $bankTotalPages; ?> - Tổng <?= (int) $bankTotal; ?> tài khoản</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('bank-manage')); ?>">
                                <input type="hidden" name="page" value="bank-manage">
                                <label class="text-[11px] font-semibold text-slate-500" for="bank-per-page">Số dòng</label>
                                <select id="bank-per-page" name="bank_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($bankPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $bankPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($bankPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('bank-manage', ['bank_page' => $bankPage - 1, 'bank_per_page' => $bankPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($bankPage < $bankTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('bank-manage', ['bank_page' => $bankPage + 1, 'bank_per_page' => $bankPerPage])); ?>">Sau</a>
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
