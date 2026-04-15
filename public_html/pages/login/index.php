<?php
$error = $error ?? get_flash('error');
$success = get_flash('success');
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 grid place-items-center">
        <div class="grid w-full max-w-6xl gap-4 lg:grid-cols-2">
            <aside class="rounded-2xl border border-slate-700/30 bg-slate-900 p-7 text-slate-100 shadow-xl">
                <div class="flex flex-wrap gap-2 mb-3.5">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-emerald-200 bg-emerald-50 text-emerald-700">Đăng nhập an toàn</span>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold border-amber-200 bg-amber-50 text-amber-700">Dữ liệu mẫu sẵn sàng</span>
                </div>
                <h2>Nền tảng Trung tâm Anh ngữ</h2>
                <p>Quản lý marketing, cổng học viên, giáo viên, tài chính và học vụ trong cùng một hệ thống.</p>

                <ul class="mt-4 grid gap-3">
                    <li>Một tài khoản, nhiều vai trò, phân quyền rõ ràng.</li>
                    <li>Tải lên bài tập, portfolio và tài liệu có xem trước.</li>
                    <li>Bảng điều khiển có số liệu và biểu đồ Chart.js trực quan.</li>
                </ul>
            </aside>

            <article class="p-7 rounded-2xl border border-slate-200 bg-white shadow-lg">
                <h1>Đăng nhập hệ thống</h1>
                <p>Cổng học viên, giáo viên, giáo vụ và admin trong một màn hình rõ ràng và gọn gàng.</p>

                <?php if ($error): ?>
                    <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
                <?php endif; ?>

                <form method="post" action="/api/auth/login" class="grid gap-3">
                    <?= csrf_input(); ?>
                    <label>
                        Tên đăng nhập
                        <input type="text" name="username" required placeholder="student@ec.local">
                    </label>
                    <label>
                        Mật khẩu
                        <input type="password" name="password" required placeholder="123456">
                    </label>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Đăng nhập</button>
                </form>

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">
                    <strong>Tài khoản demo:</strong>
                    <ul>
                        <li>admin@ec.local / 123456</li>
                        <li>staff@ec.local / 123456</li>
                        <li>teacher@ec.local / 123456</li>
                        <li>student@ec.local / 123456</li>
                    </ul>
                </div>
            </article>
        </div>
    </div>
</section>


