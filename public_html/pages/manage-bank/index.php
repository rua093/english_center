<?php
require_admin_or_staff();
require_permission('bank.view');

$academicModel = new AcademicModel();
$bankAccounts = $academicModel->listBankAccounts();
$editingBank = null;
if (!empty($_GET['edit'])) {
	$stmt = Database::connection()->prepare("SELECT id, bank_name, bank_name AS account_name, bin, account_number, account_holder, qr_code_static_url, is_default, is_default AS is_primary FROM bank_accounts WHERE id = :id LIMIT 1");
	$stmt->execute(['id' => (int) $_GET['edit']]);
	$editingBank = $stmt->fetch();
}

$module = 'bank';
$adminTitle = 'Quản lý ngân hàng';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <?php
        $canCreateBank = has_permission('bank.create');
        $canUpdateBank = has_permission('bank.update');
        $canDeleteBank = has_permission('bank.delete');
        ?>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1>Quản lý tài khoản ngân hàng</h1>
                <p>Cấu hình các tài khoản ngân hàng nhận thanh toán.</p>
            </div>
        </div>

        <?php if ($canCreateBank || $canUpdateBank): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3><?= $editingBank ? 'Sửa tài khoản' : 'Thêm tài khoản'; ?></h3>
                <form class="grid gap-3" method="post" action="/api/banks/save">
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
                    <label>
                        Liên kết QR tĩnh
                        <input type="text" name="qr_code_static_url" value="<?= e((string) ($editingBank['qr_code_static_url'] ?? '')); ?>">
                    </label>
                    <label>
                        <input type="checkbox" name="is_default" value="1" <?= (int) ($editingBank['is_default'] ?? $editingBank['is_primary'] ?? 0) === 1 ? 'checked' : ''; ?>>
                        Tài khoản chính
                    </label>
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu tài khoản</button>
                </form>
            </article>
        <?php endif; ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mt-6">
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
                            <th>Thao tác</th>
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
                                                <a href="/?page=manage-bank&edit=<?= (int) $bank['id']; ?>">Sửa</a>
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
            </div>
        </div>
    </div>
</section>



