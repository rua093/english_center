<?php
declare(strict_types=1);

require_role(['admin']);
require_permission('admin.dashboard.view');

$module = 'sync-control';
$adminTitle = 'Điều phối đồng bộ';
$adminDescription = 'Điều khiển maintenance mode, đồng bộ từ S3 về sandbox, xuất SQL thay đổi và hồi sinh dữ liệu.';

$success = get_flash('success');
$error = get_flash('error');
$serverRole = app_server_role();
$maintenanceEnabled = app_is_maintenance_mode_enabled();
$status = [];
$statusError = '';

try {
    $status = sync_change_log_status(Database::connection());
} catch (Throwable $exception) {
    $statusError = $exception->getMessage();
}

$pendingCount = (int) ($status['pending_count'] ?? 0);
$recentChanges = is_array($status['recent_changes'] ?? null) ? $status['recent_changes'] : [];
$changeLogMode = strtoupper(trim((string) ($status['mode'] ?? ($serverRole === 'sandbox' ? 'TRIGGER' : 'READ'))));
?>

<div class="admin-ui min-w-0 grid gap-5">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-500">Vai trò server</p>
            <p class="mt-2 text-2xl font-black text-slate-900"><?= e(strtoupper($serverRole)); ?></p>
            <p class="mt-2 text-sm text-slate-500"><?= e($serverRole === 'sandbox' ? 'Server phụ' : 'Server chính'); ?></p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-500">Maintenance</p>
            <p class="mt-2 text-2xl font-black <?= $maintenanceEnabled ? 'text-amber-600' : 'text-emerald-600'; ?>"><?= e($maintenanceEnabled ? 'ĐANG BẬT' : 'ĐANG TẮT'); ?></p>
            <p class="mt-2 text-sm text-slate-500"><?= e($maintenanceEnabled ? 'Web đang tạm dừng cho người dùng thường.' : 'Web đang hoạt động bình thường.'); ?></p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-500">Pending changes</p>
            <p class="mt-2 text-2xl font-black text-slate-900"><?= e((string) $pendingCount); ?></p>
            <p class="mt-2 text-sm text-slate-500">Chưa export</p>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <article class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-black text-slate-900">Maintenance</h2>
                <form method="post" action="/api/admin-sync-ops.php" data-sync-control-form="1">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="sync_control_password" value="">
                    <input type="hidden" name="action" value="toggle-maintenance">
                    <input type="hidden" name="enabled" value="<?= $maintenanceEnabled ? '0' : '1'; ?>">
                    <button class="<?= $maintenanceEnabled ? ui_btn_secondary_classes() : ui_btn_primary_classes(); ?>" type="submit">
                        <?= e($maintenanceEnabled ? 'Tắt maintenance' : 'Bật maintenance'); ?>
                    </button>
                </form>
            </div>
            <p class="mt-3 text-sm text-slate-500"><?= e($serverRole === 'sandbox' ? 'Dùng khi cần chặn dữ liệu mới trên Sandbox.' : 'Có thể bật tạm thời trên Main khi cần thao tác hệ thống.'); ?></p>
            <?php if ($statusError !== ''): ?>
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"><?= e($statusError); ?></div>
            <?php else: ?>
                <p class="mt-4 text-sm text-slate-500">Lần thay đổi gần nhất: <?= e((string) (($status['last_change_at'] ?? '') !== '' ? $status['last_change_at'] : 'chưa có')); ?></p>
            <?php endif; ?>
        </article>

        <article class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Trạng thái đồng bộ</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Batch chờ export</p>
                    <p class="mt-2 text-2xl font-black text-slate-900"><?= e((string) $pendingCount); ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Cơ chế log</p>
                    <p class="mt-2 text-2xl font-black text-slate-900"><?= e($serverRole === 'sandbox' ? $changeLogMode : 'READ'); ?></p>
                </div>
            </div>
            <p class="mt-4 text-sm text-slate-500"><?= e($serverRole === 'sandbox' ? ($changeLogMode === 'APP' ? 'Sandbox đang ghi log thay đổi trực tiếp từ tầng PHP.' : 'Trigger change-log tự bootstrap trong nền.') : 'Main chỉ nhận và áp dụng SQL incremental từ Sandbox.'); ?></p>
        </article>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <?php if ($serverRole === 'sandbox'): ?>
            <article class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Khởi tạo Sandbox</h2>
                <p class="mt-2 text-sm text-slate-500">Xóa dữ liệu Sandbox hiện tại rồi kéo bản backup mới nhất từ S3 về.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form method="post" action="/api/admin-sync-ops.php" data-sync-control-form="1">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()); ?>">
                        <input type="hidden" name="sync_control_password" value="">
                        <input type="hidden" name="action" value="pull-latest-backup">
                        <button class="<?= ui_btn_secondary_classes(); ?>" type="submit">Kéo backup từ S3</button>
                    </form>
                </div>
            </article>

            <article class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Xuất SQL thay đổi</h2>
                <p class="mt-2 text-sm text-slate-500">Xuất file incremental để đưa về Main.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form method="post" action="/api/admin-sync-ops.php" data-sync-control-form="1">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()); ?>">
                        <input type="hidden" name="sync_control_password" value="">
                        <input type="hidden" name="action" value="export-change-sql">
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Tải SQL incremental</button>
                    </form>
                </div>
            </article>
        <?php else: ?>
            <article class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Import SQL từ Sandbox</h2>
                <p class="mt-2 text-sm text-slate-500">Áp dụng file incremental vào DB hiện tại.</p>
                <form class="mt-4 grid gap-3" method="post" action="/api/import-fallback.php" enctype="multipart/form-data" data-sync-control-form="1">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="sync_control_password" value="">
                    <input type="file" name="sql_file" accept=".sql" required class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Import vào server chính</button>
                </form>
            </article>

            <article class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Quy trình nhanh</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-6 text-slate-500">
                    <li>Bật maintenance trên Sandbox để chặn dữ liệu mới.</li>
                    <li>Lấy SQL incremental từ Sandbox để apply vào DB hiện tại của Main.</li>
                    <li>Import vào server chính.</li>
                    <li>Kiểm tra nhanh dữ liệu, rồi tắt maintenance trên Sandbox và chuyển traffic về Main.</li>
                </ol>
            </article>
        <?php endif; ?>
    </section>

    <section class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-black text-slate-900">Thay đổi gần đây</h2>
                <p class="mt-1 text-sm text-slate-500">12 bản ghi mới nhất.</p>
            </div>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-slate-500">
                        <th class="px-3 py-2 font-bold">#</th>
                        <th class="px-3 py-2 font-bold">Bảng</th>
                        <th class="px-3 py-2 font-bold">Tác vụ</th>
                        <th class="px-3 py-2 font-bold">Khóa chính</th>
                        <th class="px-3 py-2 font-bold">Thời gian</th>
                        <th class="px-3 py-2 font-bold">Đã export</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentChanges === []): ?>
                        <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Chưa có thay đổi nào được ghi nhận.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentChanges as $change): ?>
                            <tr class="border-b border-slate-100 text-slate-700">
                                <td class="px-3 py-2"><?= e((string) ((int) ($change['id'] ?? 0))); ?></td>
                                <td class="px-3 py-2"><?= e((string) ($change['table_name'] ?? '')); ?></td>
                                <td class="px-3 py-2"><?= e((string) ($change['operation'] ?? '')); ?></td>
                                <td class="px-3 py-2"><?= e((string) (($change['primary_key_column'] ?? '') . '=' . ($change['primary_key_value'] ?? ''))); ?></td>
                                <td class="px-3 py-2"><?= e((string) ($change['changed_at'] ?? '')); ?></td>
                                <td class="px-3 py-2"><?= e((string) (($change['exported_at'] ?? '') !== '' ? 'Rồi' : 'Chưa')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form[data-sync-control-form="1"]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var passwordInput = form.querySelector('input[name="sync_control_password"]');
            if (!passwordInput) {
                return;
            }

            var password = window.prompt('Nhập mật khẩu xác nhận để tiếp tục thao tác này:');
            if (password === null) {
                event.preventDefault();
                return;
            }

            passwordInput.value = password;
        });
    });
});
</script>
