<?php
$error = $error ?? get_flash('error');
$success = get_flash('success');
$authLocale = current_locale();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Custom Animations & Utils - TONE ĐỎ XANH LÁ */
    .login-background {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, 0.58) 0%, rgba(30, 41, 59, 0.42) 52%, rgba(15, 23, 42, 0.54) 100%),
            url('/assets/images/login_background.jpg');
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.86);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.7);
    }
    .input-focus-ring:focus-within {
        border-color: #e11d48; /* Màu Đỏ Rose 600 */
        box-shadow: 0 10px 25px -5px rgba(225, 29, 72, 0.15);
        transform: translateY(-2px);
    }
    .input-focus-ring:focus-within .icon-input {
        color: #e11d48;
    }
    @keyframes floatBlob {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }
    .animate-blob { animation: floatBlob 10s infinite alternate ease-in-out; }
    .animation-delay-2000 { animation-delay: 2s; }
    @keyframes shimmer {
        100% { transform: translateX(100%); }
    }
</style>

<section class="login-background min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-hidden font-jakarta">
    <div class="absolute right-4 top-4 z-20 inline-flex rounded-full border border-white/60 bg-white/80 p-1 text-xs font-black shadow-lg shadow-slate-900/10 backdrop-blur">
        <?php foreach (APP_SUPPORTED_LOCALES as $localeOption): ?>
            <a
                href="<?= e(localized_current_url($localeOption)); ?>"
                class="rounded-full px-3 py-1.5 transition <?= $authLocale === $localeOption ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-rose-700'; ?>"
                aria-label="<?= e(t('locale.switch_to', ['language' => t('locale.' . $localeOption)])); ?>"
            >
                <?= e(t('locale.' . $localeOption)); ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[460px] h-[460px] rounded-full bg-slate-950/25 blur-[90px] animate-blob"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[560px] h-[560px] rounded-full bg-emerald-950/18 blur-[110px] animate-blob animation-delay-2000"></div>
        <div class="absolute top-[18%] right-[8%] w-[260px] h-[260px] rounded-full bg-rose-950/18 blur-[90px] animate-blob"></div>
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: radial-gradient(#ffffff 2px, transparent 2px); background-size: 34px 34px;"></div>
    </div>

    <div class="w-full max-w-[1100px] glass-card rounded-[2.5rem] shadow-[0_25px_50px_rgba(2,6,23,0.18)] overflow-hidden grid lg:grid-cols-12 relative z-10 transition-all duration-500 hover:shadow-[0_30px_60px_rgba(2,6,23,0.22)]">
        
        <aside class="relative hidden lg:flex flex-col justify-end overflow-hidden group lg:col-span-5 bg-slate-950/70">
            <img src="/assets/images/login3.jpg" 
                alt="<?= e(t('login.image_alt')); ?>"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105 opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-emerald-950/18 to-slate-900/8"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950/6 via-white/2 to-emerald-100/6"></div>

            <div class="relative z-10 p-10 text-white">
                <div class="flex items-center gap-2 mb-6">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-lime-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-lime-500"></span>
                    </span>
                    <span class="text-xs font-bold tracking-widest uppercase text-lime-200"><?= e(t('login.kicker')); ?></span>
                </div>
                
                <h2 class="text-4xl font-black mb-4 leading-tight tracking-tight text-white drop-shadow-md">
                    <?= e(t('login.welcome')); ?> <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-lime-300 to-white"><?= e(t('login.welcome_highlight')); ?></span>
                </h2>
                
                <p class="text-emerald-100/90 text-sm leading-relaxed font-medium max-w-sm border-l-2 border-rose-500 pl-4">
                    <?= e(t('login.intro')); ?>
                </p>
            </div>
        </aside>

        <article class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center lg:col-span-7 bg-white/88 relative">
            <div class="max-w-md w-full mx-auto relative z-10">
                
                <div class="text-center mb-10">
                    <!-- <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 mb-6 shadow-inner">
                        <i class="fa-solid fa-user-graduate text-3xl"></i>
                    </div> -->
                    <h1 class="text-3xl font-black text-slate-800 mb-2"><?= e(t('login.title')); ?></h1>
                    <p class="text-slate-500 text-sm font-medium"><?= e(t('login.subtitle')); ?></p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-600 font-bold flex items-center gap-3 animate-fade-in-down">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shadow-sm shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <?= e($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-600 font-bold flex items-center gap-3 animate-fade-in-down">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-emerald-500 shadow-sm shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <?= e($success); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/api/auth/login" class="space-y-6">
                    <?= csrf_input(); ?>
                    
                    <div class="group">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2 ml-1"><?= e(t('login.username_label')); ?></label>
                        <div class="relative flex items-center input-focus-ring rounded-2xl bg-slate-50 border border-slate-200 transition-all duration-300">
                            <div class="pl-5 pr-3 icon-input text-slate-400 transition-colors">
                                <i class="fa-regular fa-envelope text-lg"></i>
                            </div>
                            <input type="text" name="username" required placeholder="nhueminh@edu.vn" 
                                class="w-full py-4 pr-5 bg-transparent text-slate-800 font-semibold placeholder-slate-400 outline-none">
                        </div>
                    </div>

                    <div class="group">
                        <div class="flex justify-between items-center mb-2 ml-1 mr-1">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide"><?= e(t('login.password_label')); ?></label>
                        </div>
                        <div class="relative flex items-center input-focus-ring rounded-2xl bg-slate-50 border border-slate-200 transition-all duration-300">
                            <div class="pl-5 pr-3 icon-input text-slate-400 transition-colors">
                                <i class="fa-solid fa-lock text-lg"></i>
                            </div>
                            <input type="password" id="passwordInput" name="password" required placeholder="••••••••" 
                                class="w-full py-4 pr-12 bg-transparent text-slate-800 font-semibold placeholder-slate-400 outline-none">
                            
                            <button type="button" id="togglePasswordBtn" class="absolute right-2 top-1/2 z-20 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500 shadow-[0_1px_2px_rgba(15,23,42,0.06)] transition-all hover:border-slate-300 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                <i id="eyeOpenIcon" class="fa-solid fa-eye text-[17px] leading-none"></i>
                                <i id="eyeClosedIcon" class="fa-solid fa-eye-slash hidden text-[17px] leading-none"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 ml-1">
                        <label class="flex items-center cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="remember" class="sr-only peer">
                                <div class="w-5 h-5 border-2 border-slate-300 rounded bg-white transition-colors group-hover:border-rose-600 peer-checked:bg-rose-600 peer-checked:border-rose-600 flex items-center justify-center">
                                    <i class="fa-solid fa-check text-white text-[10px] opacity-100 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                            <span class="ml-3 text-sm font-semibold text-slate-600 group-hover:text-slate-800 transition-colors"><?= e(t('login.remember')); ?></span>
                        </label>

                        <a href="<?= e(page_url('forgot-password')); ?>" class="text-xs font-bold text-emerald-600 hover:text-rose-600 transition-colors whitespace-nowrap">
                            <?= e(t('login.forgot_password')); ?>
                        </a>
                    </div>

                    <button type="submit" class="group w-full relative inline-flex items-center justify-center gap-3 rounded-2xl bg-rose-600 hover:bg-rose-700 px-4 py-4 text-sm font-black text-white uppercase tracking-widest shadow-[0_10px_20px_rgba(225,29,72,0.3)] transition-all hover:-translate-y-1 hover:shadow-[0_15px_30px_rgba(225,29,72,0.4)] overflow-hidden">
                        <span class="relative z-10"><?= e(t('login.submit')); ?></span>
                        <div class="relative z-10 w-2 h-2 rounded-full bg-lime-400 group-hover:scale-150 transition-transform"></div>
                        <div class="absolute inset-0 h-full w-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                    </button>

                </form>

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
        const checkboxGroup = document.querySelector('input[type="checkbox"]').nextElementSibling;

        // Xử lý ẩn/hiện mật khẩu
        togglePasswordBtn.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeOpenIcon.classList.toggle('hidden');
            eyeClosedIcon.classList.toggle('hidden');
        });

        // Xử lý logic check box custom đồng bộ với màu Đỏ Rose
        const checkboxInput = document.querySelector('input[name="remember"]');
        checkboxInput.addEventListener('change', function() {
            const icon = checkboxGroup.querySelector('i');
            if (this.checked) {
                checkboxGroup.classList.add('bg-rose-600', 'border-rose-600');
                checkboxGroup.classList.remove('bg-white', 'border-slate-300');
                icon.classList.remove('opacity-0');
            } else {
                checkboxGroup.classList.remove('bg-rose-600', 'border-rose-600');
                checkboxGroup.classList.add('bg-white', 'border-slate-300');
                icon.classList.add('opacity-0');
            }
        });
    });
</script>
