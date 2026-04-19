<?php
$error = $error ?? get_flash('error');
$success = get_flash('success');
?>
<section class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-blue-500 via-blue-400 to-sky-300 relative overflow-hidden">
    
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 rounded-full bg-white/20 blur-3xl"></div>
    <div class="absolute bottom-[-10%] right-[-5%] w-96 h-96 rounded-full bg-sky-200/30 blur-3xl"></div>

    <div class="w-full max-w-6xl rounded-3xl bg-white/20 backdrop-blur-md shadow-2xl border border-white/30 overflow-hidden grid lg:grid-cols-2 relative z-10">
        
        <aside class="relative hidden lg:flex flex-col justify-end overflow-hidden group">
            <img src="/englist-center.jpeg" 
                alt="Background" 
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-t from-blue-600/80 via-blue-500/20 to-transparent"></div>

            <div class="relative z-10 p-12 text-white">
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur-md border border-white/30 shadow-sm">
                        ✨ Bảo mật 2 lớp
                    </span>
                </div>
                
                <h2 class="text-4xl font-extrabold mb-3 leading-tight tracking-tight drop-shadow-lg">
                    Nền tảng <br/><span class="text-sky-200">Trung tâm Anh ngữ</span>
                </h2>
                
                <p class="text-white/90 text-lg leading-relaxed font-medium max-w-sm drop-shadow-md">
                    Hệ sinh thái quản trị thông minh dành riêng cho giáo dục hiện đại.
                </p>
            </div>
        </aside>

        <article class="bg-white p-10 lg:p-14 lg:rounded-l-3xl flex flex-col justify-center shadow-[-20px_0_50px_rgba(0,0,0,0.05)]">
            <div class="max-w-md w-full mx-auto">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Đăng nhập</h1>
                    <p class="text-slate-500 text-sm">Chào mừng bạn quay trở lại với Nhuệ Minh Edu.</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-4 rounded-xl border-l-4 border-rose-400 bg-rose-50 p-4 text-sm text-rose-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <?= e($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/api/auth/login" class="grid gap-5">
                    <?= csrf_input(); ?>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tên đăng nhập</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                            </div>
                            <input type="text" name="username" required placeholder="admin@ec.local" 
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400 transition-all">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-semibold text-slate-700">Mật khẩu</label>
                            <a href="#" class="text-xs text-blue-500 hover:text-blue-600 font-medium">Quên mật khẩu?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            
                            <input type="password" id="passwordInput" name="password" required placeholder="••••••••" 
                                class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400 transition-all">
                            
                            <button type="button" id="togglePasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-blue-500 focus:outline-none transition-colors">
                                <svg id="eyeOpenIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeClosedIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-2 inline-flex items-center justify-center rounded-xl bg-blue-500 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 hover:bg-blue-600 hover:shadow-blue-600/40">
                        Tiếp tục đăng nhập
                    </button>
                </form>

                <div class="mt-5 text-center text-sm text-slate-500">
                    Chưa có tài khoản?
                    <a href="<?= e(page_url('register')); ?>" class="font-bold text-blue-600 hover:text-blue-800 hover:underline underline-offset-4">Đăng ký ngay</a>
                </div>

                <div class="mt-5 text-center text-sm text-slate-500">
                    Chưa có tài khoản?
                    <a href="<?= e(page_url('register')); ?>" class="font-bold text-blue-600 hover:text-blue-800 hover:underline underline-offset-4">Đăng ký ngay</a>
                </div>
            </div>
        </article>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('passwordInput');
        const eyeOpenIcon = document.getElementById('eyeOpenIcon');
        const eyeClosedIcon = document.getElementById('eyeClosedIcon');

        togglePasswordBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeOpenIcon.classList.toggle('hidden');
            eyeClosedIcon.classList.toggle('hidden');
        });
    });
</script>