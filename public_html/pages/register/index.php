<?php
$error = $error ?? get_flash('error');
$success = $success ?? get_flash('success');
?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
    /* Animation cho background blob */
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
</style>

<section class="font-jakarta min-h-screen bg-slate-50 relative overflow-hidden flex items-center px-4 py-10 sm:px-6 lg:py-14">
    
    <div class="absolute top-0 -left-40 h-[30rem] w-[30rem] rounded-full bg-blue-200/50 blur-3xl animate-blob mix-blend-multiply"></div>
    <div class="absolute top-40 -right-20 h-[30rem] w-[30rem] rounded-full bg-cyan-200/50 blur-3xl animate-blob animation-delay-2000 mix-blend-multiply"></div>
    <div class="absolute -bottom-40 left-20 h-[30rem] w-[30rem] rounded-full bg-indigo-200/50 blur-3xl animate-blob animation-delay-4000 mix-blend-multiply"></div>

	<div class="relative z-10 mx-auto grid w-full max-w-6xl gap-8 lg:grid-cols-2 items-center">
		
        <aside class="relative overflow-hidden rounded-[2rem] border border-white/60 bg-white/40 p-8 shadow-2xl shadow-blue-900/5 backdrop-blur-xl lg:p-12">
			<div class="relative z-10 flex h-full flex-col justify-center gap-10">
				<div>
					<div class="mb-6 flex flex-wrap gap-3">
						<span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-200 bg-cyan-50 px-4 py-1.5 text-xs font-bold text-cyan-700 shadow-sm">
                            <i class="fa-solid fa-sparkles text-cyan-500"></i> Khởi đầu mới
                        </span>
						<span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-xs font-bold text-blue-700 shadow-sm">
                            <i class="fa-solid fa-bolt text-blue-500"></i> Nhanh chóng & Bảo mật
                        </span>
					</div>

					<h2 class="text-4xl font-extrabold leading-tight tracking-tight text-slate-800 sm:text-5xl">
                        Đăng ký <br><span class="bg-gradient-to-r from-blue-600 to-cyan-500 bg-clip-text text-transparent drop-shadow-sm">tài khoản</span> trung tâm
                    </h2>
					<p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
						Chỉ với một tài khoản duy nhất, bạn có thể theo dõi tiến độ học tập, học phí, bài tập và nhận thông báo tức thì từ hệ thống.
					</p>
				</div>

				<ul class="grid gap-6 text-sm text-slate-700 sm:text-base font-medium">
					<li class="flex items-start gap-4 group">
						<span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-md shadow-slate-200 border border-slate-100 transition-transform group-hover:scale-110">
                            <i class="fa-solid fa-user-pen text-xs"></i>
                        </span>
						<span class="pt-1">Điền thông tin cá nhân cơ bản để tạo hồ sơ.</span>
					</li>
					<li class="flex items-start gap-4 group">
						<span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-md shadow-slate-200 border border-slate-100 transition-transform group-hover:scale-110">
                            <i class="fa-solid fa-users-gear text-xs"></i>
                        </span>
						<span class="pt-1">Chọn vai trò phù hợp (Học viên / Phụ huynh).</span>
					</li>
					<li class="flex items-start gap-4 group">
						<span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-500 shadow-md shadow-slate-200 border border-slate-100 transition-transform group-hover:scale-110">
                            <i class="fa-solid fa-check text-xs"></i>
                        </span>
						<span class="pt-1">Hoàn tất và bắt đầu trải nghiệm ngay lập tức.</span>
					</li>
				</ul>
			</div>
		</aside>

        <article class="rounded-[2rem] border border-white bg-white p-8 text-slate-900 shadow-xl shadow-slate-200/50 sm:p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 via-cyan-400 to-indigo-500 opacity-90"></div>

			<div class="mx-auto max-w-md relative z-10">
				<div class="mb-8 text-center">
					<h1 class="text-3xl font-black tracking-tight text-slate-800">Tạo tài khoản</h1>
					<p class="mt-2 text-sm font-medium text-slate-500">Tham gia hệ thống quản lý học tập thông minh.</p>
				</div>

				<?php if ($error): ?>
					<div class="mb-6 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-700">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-xl"></i>
						<?= e($error); ?>
					</div>
				<?php endif; ?>

				<?php if ($success): ?>
					<div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
						<?= e($success); ?>
					</div>
				<?php endif; ?>

				<form class="grid gap-5" method="post" action="#">
					<?= csrf_input(); ?>

					<div class="grid gap-1.5">
						<label class="text-sm font-bold text-slate-700">Họ và tên</label>
                        <div class="relative">
                            <i class="fa-regular fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-600"></i>
						    <input type="text" name="full_name" placeholder="Nguyễn Văn A" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400" required>
                        </div>
					</div>

					<div class="grid gap-1.5">
						<label class="text-sm font-bold text-slate-700">Email hoặc tên đăng nhập</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-600"></i>
						    <input type="text" name="username" placeholder="student@ec.local" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400" required>
                        </div>
					</div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-1.5">
                            <label class="text-sm font-bold text-slate-700">Mật khẩu</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-600"></i>
                                <input type="password" name="password" id="password_input" placeholder="••••••••" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-10 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400" required>
                                <button type="button" onclick="togglePassword('password_input', 'eye_icon_1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 focus:outline-none p-1 transition-colors">
                                    <i id="eye_icon_1" class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <label class="text-sm font-bold text-slate-700">Nhập lại</label>
                            <div class="relative">
                                <i class="fa-solid fa-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-600"></i>
                                <input type="password" name="password_confirm" id="password_confirm_input" placeholder="••••••••" class="peer w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-10 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400" required>
                                <button type="button" onclick="togglePassword('password_confirm_input', 'eye_icon_2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 focus:outline-none p-1 transition-colors">
                                    <i id="eye_icon_2" class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

					<div class="grid gap-1.5">
						<label class="text-sm font-bold text-slate-700">Vai trò của bạn</label>
                        <div class="relative">
                            <i class="fa-solid fa-user-tag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors peer-focus:text-blue-600 z-10"></i>
						    <select name="role" class="peer w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-10 text-slate-800 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 cursor-pointer font-bold">
                                <option value="student">👨‍🎓 Học viên</option>
                                <option value="parent">👨‍👩‍👧 Phụ huynh</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
					</div>

					<button type="submit" class="mt-4 group relative inline-flex w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-4 text-base font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02] hover:shadow-indigo-500/30">
                        <span class="absolute right-0 -mt-12 h-32 w-8 translate-x-12 rotate-12 bg-white opacity-20 transition-all duration-1000 ease-out group-hover:-translate-x-96"></span>
						Đăng ký ngay <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
					</button>

					<div class="mt-4 text-center text-sm font-medium text-slate-500">
						Đã có tài khoản?
						<a href="<?= e(page_url('login')); ?>" class="font-bold text-blue-600 transition-colors hover:text-indigo-600 hover:underline underline-offset-4">Đăng nhập tại đây</a>
					</div>
				</form>
			</div>
		</article>
	</div>
</section>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash"); // Đổi thành icon nhắm mắt
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye"); // Đổi lại icon mở mắt
        }
    }
</script>