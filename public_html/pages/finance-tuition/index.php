<?php
require_admin_or_staff();
require_permission('finance.tuition.view');

$academicModel = new AcademicModel();
$tuitionFees = $academicModel->listTuitionFees();

$module = 'tuition';
$adminTitle = 'Quản lý học phí';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <?php
            $viewer = auth_user();
            $isStaff = (($viewer['role'] ?? '') === 'staff');
            $canDeleteTuition = checkPermission('finance.tuition.delete') || (($viewer['role'] ?? '') === 'admin');
        ?>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1>Quản lý học phí</h1>
                <p>Theo dõi và cập nhật trạng thái thu học phí của học viên.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th>Học viên</th>
                            <th>Lớp học</th>
                            <th>Tổng tiền</th>
                            <th>Đã thu</th>
                            <th>Còn lại</th>
                            <th>Trạng thái</th>
                            <th>Chế độ đóng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tuitionFees)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có dữ liệu học phí.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tuitionFees as $fee): ?>
                                <tr>
                                    <td><?= e((string) $fee['student_name']); ?></td>
                                    <td><?= e((string) $fee['course_name']); ?></td>
                                    <td><?= format_money((float) $fee['total_amount']); ?></td>
                                    <td><?= format_money((float) $fee['amount_paid']); ?></td>
                                    <td><?= format_money((float) ($fee['total_amount'] - $fee['amount_paid'])); ?></td>
                                    <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $fee['status']); ?>"><?= e((string) $fee['status']); ?></span></td>
                                    <td><?= e((string) ($fee['payment_plan'] ?? 'full')); ?></td>
                                    <td>
                                        <?php if ($canDeleteTuition): ?>
                                            <form method="post" action="/api/tuitions/delete" onsubmit="return confirm('Bạn chắc chắn muốn xóa hóa đơn học phí này?');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="tuition_id" value="<?= (int) $fee['id']; ?>">
                                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Xóa</button>
                                            </form>
                                        <?php else: ?>
                                            <div class="inline-flex flex-wrap items-center gap-2">
                                                <button type="button" class="<?= ui_btn_primary_classes('sm'); ?>" disabled>Xóa</button>
                                                <form method="post" action="/api/tuitions/request-delete">
                                                    <?= csrf_input(); ?>
                                                    <input type="hidden" name="tuition_id" value="<?= (int) $fee['id']; ?>">
                                                    <input type="hidden" name="reason" value="Yêu cầu xóa học phí do cần điều chỉnh nghiệp vụ.">
                                                    <button class="<?= ui_btn_secondary_classes('sm'); ?>" type="submit">Gửi duyệt</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($isStaff): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mt-3.5">
                <h3>Yêu cầu chỉnh sửa tài chính</h3>
                <p>Giáo vụ không sửa trực tiếp dữ liệu học phí. Vui lòng tạo yêu cầu để Admin duyệt và thực thi.</p>
                <form class="grid gap-3" method="post" action="/api/tuitions/request-adjust">
                    <?= csrf_input(); ?>
                    <label>
                        Hóa đơn học phí
                        <select name="tuition_id" required>
                            <option value="">-- Chọn hóa đơn --</option>
                            <?php foreach ($tuitionFees as $fee): ?>
                                <option value="<?= (int) $fee['id']; ?>">
                                    #<?= (int) $fee['id']; ?> - <?= e((string) $fee['student_name']); ?> - <?= e((string) $fee['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Số đã thu đề xuất
                        <input type="number" step="1000" min="0" name="requested_amount_paid" required>
                    </label>
                    <label>
                        Lý do điều chỉnh
                        <input type="text" name="reason" required placeholder="Ví dụ: Nhập sai số tiền khi thu tại quầy">
                    </label>
                    <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Gửi yêu cầu phê duyệt</button>
                </form>
            </article>
        <?php endif; ?>
    </div>
</section>



