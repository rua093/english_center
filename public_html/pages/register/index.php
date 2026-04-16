<?php
$error = $error ?? get_flash('error');
$success = $success ?? get_flash('success');
?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
    /* Animation nhẹ cho background blob */
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
</style>

<section class="font-jakarta min-h-screen bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-900 via-slate-900 to-black px-4 py-10 text-slate-100 sm:px-6 lg:py-14 flex items-center">
	<div class="mx-auto grid w-full max-w-6xl gap-6 lg:grid-cols-2 items-center">
		
        <aside class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl lg:p-12">
            <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-cyan-500/30 blur-3xl animate-blob"></div>
			<div class="absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-blue-600/30 blur-3xl animate-blob animation-delay-2000"></div>

			<div class="relative z-10 flex h-full flex-col justify-center gap-10">
				<div>
					<div class="mb-6 flex flex-wrap gap-3">
						<span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-4 py-1.5 text-xs font-bold text-cyan-300 backdrop-blur-md">
                            <i class="fa-solid fa-sparkles"></i> Khởi đầu mới
                        </span>
						<span class="inline-flex items-center gap-1.5 rounded-full border border-blue-400/30 bg-blue-500/10 px-4 py-1.5 text-xs font-bold text-blue-300 backdrop-blur-md">
                            <i class="fa-solid fa-bolt"></i> Nhanh chóng & Bảo mật
                        </span>
					</div>

					<h2 class="text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">
                        Đăng ký <br><span class="bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">tài khoản</span> trung tâm
                    </h2>
					<p class="mt-5 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
						Chỉ với một tài khoản duy nhất, bạn có thể theo dõi tiến độ học tập, học phí, bài tập và nhận thông báo tức thì từ hệ thống.
					</p>
				</div>

				<ul class="grid gap-5 text-sm text-slate-300 sm:text-base font-medium">
					<li class="flex items-start gap-4">
						<span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <i class="fa-solid fa-user-pen text-xs"></i>
                        </span>
						<span>Điền thông tin cá nhân cơ bản để tạo hồ sơ.</span>
					</li>
					<li class="flex items-start gap-4">
						<span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <i class="fa-solid fa-users-gear text-xs"></i>
                        </span>
						<span>Chọn vai trò phù hợp (Học viên / Phụ huynh).</span>
					</li>
					<li class="flex items-start gap-4">
						<span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <i class="fa-solid fa-check text-xs"></i>
                        </span>
						<span>Hoàn tất và bắt đầu trải nghiệm ngay lập tức.</span>
					</li>
				</ul>
			</div>
		</aside>

        <article class="rounded-[2rem] border border-white/20 bg-white p-8 text-slate-900 shadow-[0_0_40px_rgba(37,99,235,0.15)] sm:p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyan-400 via-blue-500 to-indigo-600"></div>

			<div class="mx-auto max-w-md relative z-10">
				<div class="mb-8 text-center">
					<h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Tạo tài khoản</h1>
					<p class="mt-2 text-sm font-medium text-slate-500">Tham gia hệ thống quản lý học tập thông minh.</p>
				</div>

				<?php if ($error): ?>
					<div class="mb-5 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-700">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
						<?= e($error); ?>
					</div>
				<?php endif; ?>

				<?php if ($success): ?>
					<div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
						<?= e($success); ?>
					</div>
				<?php endif; ?>

				<form class="grid gap-5" method="post" action="#">
					<?= csrf_input(); ?>

					<div class="grid gap-1.5">
						<label class="text-sm font-bold text-slate-700">Họ và tên</label>
                        <div class="relative">
                            <i class="fa-regular fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-500"></i>
						    <input type="text" name="full_name" placeholder="Nguyễn Văn A" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400">
                        </div>
					</div>

					<div class="grid gap-1.5">
						<label class="text-sm font-bold text-slate-700">Email hoặc tên đăng nhập</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-500"></i>
						    <input type="text" name="username" placeholder="student@ec.local" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400">
                        </div>
					</div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-1.5">
                            <label class="text-sm font-bold text-slate-700">Mật khẩu</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-500"></i>
                                <input type="password" name="password" placeholder="••••••••" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400">
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <label class="text-sm font-bold text-slate-700">Nhập lại</label>
                            <div class="relative">
                                <i class="fa-solid fa-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-500"></i>
                                <input type="password" name="password_confirm" placeholder="••••••••" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400">
                            </div>
                        </div>
                    </div>

					<div class="grid gap-1.5">
						<label class="text-sm font-bold text-slate-700">Vai trò của bạn</label>
                        <div class="relative">
                            <i class="fa-solid fa-user-tag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-500 z-10"></i>
						    <select name="role" class="peer w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-10 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 cursor-pointer font-medium">
                                <option value="student">👨‍🎓 Học viên</option>
                                <option value="parent">👨‍👩‍👧 Phụ huynh</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
					</div>

					<button type="submit" class="mt-4 group relative inline-flex w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-4 py-4 text-base font-bold text-white shadow-lg shadow-blue-500/30 transition-all hover:scale-[1.02] hover:shadow-cyan-500/40">
                        <span class="absolute right-0 -mt-12 h-32 w-8 translate-x-12 rotate-12 bg-white opacity-10 transition-all duration-1000 ease-out group-hover:-translate-x-96"></span>
						Đăng ký ngay <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
					</button>

					<div class="mt-2 text-center text-sm font-medium text-slate-500">
						Đã có tài khoản?
						<a href="<?= e(page_url('login')); ?>" class="font-bold text-blue-600 transition-colors hover:text-blue-800 hover:underline underline-offset-4">Đăng nhập tại đây</a>
					</div>
				</form>
			</div>
		</article>
	</div>
</section>