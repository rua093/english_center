<?php
declare(strict_types=1);

$forgotPasswordCsrf = csrf_token();
$forgotPasswordSuccess = get_flash('success');
$forgotPasswordError = get_flash('error');
?>

<style>
    .focus-rose:focus { border-color: #e11d48; box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.1); }
    .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(225, 29, 72, 0.1); }
    .step-active { color: #e11d48; border-bottom: 3px solid #e11d48; }
    .step-inactive { color: #cbd5e1; border-bottom: 3px solid #f1f5f9; }
    .forgot-background {
        min-height: 100vh;
        width: 100%;
        background:
            linear-gradient(135deg, rgba(15, 23, 42, 0.48) 0%, rgba(15, 23, 42, 0.36) 50%, rgba(15, 23, 42, 0.50) 100%),
            url('/assets/images/login.jpg');
        background-color: #0f172a;
        background-position: center center;
        background-size: cover;
        background-repeat: no-repeat;
    }
</style>

<main class="forgot-background py-16 md:py-24 font-jakarta relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none -z-10">
        <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-rose-100/50 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-96 h-96 bg-lime-100/50 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 max-w-lg">
        <div class="glass-card rounded-[2rem] shadow-2xl shadow-rose-900/5 p-5 md:p-7" data-aos="zoom-in">
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 mb-3">
                    <i class="fa-solid fa-key text-2xl"></i>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tight">Khôi phục mật khẩu</h1>
                <p class="text-slate-500 text-sm font-medium mt-2">Đừng lo lắng, chúng tôi sẽ giúp bạn lấy lại quyền truy cập.</p>
            </div>

            <?php if ($forgotPasswordSuccess): ?>
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"><?= e($forgotPasswordSuccess); ?></div>
            <?php endif; ?>

            <?php if ($forgotPasswordError): ?>
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"><?= e($forgotPasswordError); ?></div>
            <?php endif; ?>

            <div id="forgot-password-feedback" class="mb-5 hidden rounded-2xl px-4 py-3 text-sm font-semibold"></div>

            <div class="flex justify-between mb-7 px-2 md:px-4">
                <div id="step-1-indicator" class="step-active pb-2 flex-1 text-center font-black text-xs uppercase tracking-widest">1. Email</div>
                <div id="step-2-indicator" class="step-inactive pb-2 flex-1 text-center font-black text-xs uppercase tracking-widest">2. Xác thực</div>
                <div id="step-3-indicator" class="step-inactive pb-2 flex-1 text-center font-black text-xs uppercase tracking-widest">3. Mật khẩu</div>
            </div>

            <form id="form-step-1" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase ml-2">Địa chỉ Email của bạn *</label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="email" id="email_input" required placeholder="nhueminh@edu.vn"
                            class="w-full pl-12 pr-5 py-3.5 rounded-2xl bg-slate-50 border-2 border-transparent outline-none focus-rose font-bold transition-all">
                    </div>
                </div>
                <button type="button" onclick="sendCodeToEmail()" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-3.5 rounded-2xl shadow-xl shadow-rose-600/20 transition-all hover:-translate-y-1 uppercase tracking-widest flex items-center justify-center gap-3">
                    Gửi mã xác nhận
                    <div class="w-2 h-2 rounded-full bg-lime-400"></div>
                </button>
            </form>

            <form id="form-step-2" class="space-y-5 hidden animate-fade-in">
                <div class="text-center bg-lime-50 p-3.5 rounded-2xl mb-5">
                    <p class="text-xs font-bold text-emerald-700">Nếu email tồn tại trong hệ thống, mã xác thực đã được gửi. Vui lòng kiểm tra hộp thư đến hoặc spam.</p>
                </div>
                <div class="space-y-2 text-center">
                    <label class="text-xs font-black text-slate-400 uppercase">Nhập mã 6 chữ số *</label>
                    <div class="flex justify-center gap-3 mt-3">
                        <input type="text" maxlength="6" id="verify_code" placeholder="000000"
                            class="w-full text-center tracking-[1em] text-2xl px-5 py-3.5 rounded-2xl bg-slate-50 border-2 border-transparent outline-none focus-rose font-black transition-all">
                    </div>
                </div>

                <div class="text-center">
                    <button type="button" id="btn-resend" disabled class="text-xs font-bold text-slate-400 cursor-not-allowed">
                        Gửi lại mã sau <span id="timer">60</span>s
                    </button>
                </div>

                <button type="button" onclick="validateCode()" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-3.5 rounded-2xl shadow-xl shadow-rose-600/20 transition-all hover:-translate-y-1 uppercase tracking-widest flex items-center justify-center gap-3">
                    Xác thực mã
                    <div class="w-2 h-2 rounded-full bg-lime-400"></div>
                </button>
            </form>

            <form id="form-step-3" class="space-y-5 hidden animate-fade-in">
                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-400 uppercase ml-2">Mật khẩu mới *</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="new_password" name="new_password" required placeholder="••••••••"
                                class="w-full pl-12 pr-12 py-3.5 rounded-2xl bg-slate-50 border-2 border-transparent outline-none focus-rose font-bold transition-all">
                            <button type="button" onclick="togglePass('new_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-400 uppercase ml-2">Xác nhận mật khẩu *</label>
                        <div class="relative">
                            <i class="fa-solid fa-shield-check absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••"
                                class="w-full pl-12 pr-12 py-3.5 rounded-2xl bg-slate-50 border-2 border-transparent outline-none focus-rose font-bold transition-all">
                            <button type="button" onclick="togglePass('confirm_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-3.5 rounded-2xl shadow-xl shadow-rose-600/20 transition-all hover:-translate-y-1 uppercase tracking-widest flex items-center justify-center gap-3">
                    Cập nhật mật khẩu
                    <div class="w-2 h-2 rounded-full bg-lime-400"></div>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="/login" class="text-sm font-black text-slate-400 hover:text-rose-600 transition-colors uppercase tracking-widest">
                    <i class="fa-solid fa-arrow-left-long mr-2"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
</main>

<script>
    let countdown = 60;
    let timerId = null;
    let resetFlowToken = '';
    const forgotPasswordCsrf = <?= json_encode($forgotPasswordCsrf, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    function showFeedback(type, message) {
        const feedback = document.getElementById('forgot-password-feedback');
        feedback.className = 'mb-5 rounded-2xl px-4 py-3 text-sm font-semibold ' + (type === 'error'
            ? 'border border-rose-200 bg-rose-50 text-rose-700'
            : 'border border-emerald-200 bg-emerald-50 text-emerald-700');
        feedback.textContent = message;
        feedback.classList.remove('hidden');
    }

    async function postForgotPassword(endpoint, payload) {
        const formData = new FormData();
        Object.entries(payload).forEach(([key, value]) => formData.append(key, value));
        formData.append('_csrf', forgotPasswordCsrf);
        formData.append('format', 'json');

        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();
        if (!response.ok || result.status !== 'success') {
            throw new Error(result.message || 'Yêu cầu không thành công.');
        }

        return result;
    }

    async function sendCodeToEmail() {
        const email = document.getElementById('email_input').value.trim();
        if (!email) {
            showFeedback('error', 'Vui lòng nhập email.');
            return;
        }

        try {
            const result = await postForgotPassword('/api/index.php?resource=passwords&method=request-reset', { email });
            resetFlowToken = String((result.data || {}).flow_token || '');
            document.getElementById('form-step-1').classList.add('hidden');
            document.getElementById('form-step-2').classList.remove('hidden');
            document.getElementById('step-2-indicator').className = 'step-active pb-2 flex-1 text-center font-black text-xs uppercase tracking-widest';
            showFeedback('success', result.message || 'Nếu email tồn tại, hệ thống đã gửi mã xác nhận.');
            startTimer();
        } catch (error) {
            showFeedback('error', error.message || 'Không thể gửi mã xác nhận lúc này.');
        }
    }

    async function validateCode() {
        const code = document.getElementById('verify_code').value.trim();
        if (!resetFlowToken) {
            showFeedback('error', 'Phiên khôi phục không hợp lệ. Vui lòng gửi lại mã.');
            return;
        }

        if (!code) {
            showFeedback('error', 'Vui lòng nhập mã xác nhận.');
            return;
        }

        try {
            const result = await postForgotPassword('/api/index.php?resource=passwords&method=verify-otp', {
                flow_token: resetFlowToken,
                otp_code: code
            });
            document.getElementById('form-step-2').classList.add('hidden');
            document.getElementById('form-step-3').classList.remove('hidden');
            document.getElementById('step-3-indicator').className = 'step-active pb-2 flex-1 text-center font-black text-xs uppercase tracking-widest';
            showFeedback('success', result.message || 'Xác thực thành công. Hãy đặt mật khẩu mới.');
        } catch (error) {
            showFeedback('error', error.message || 'Mã xác nhận không hợp lệ hoặc đã hết hạn.');
        }
    }

    function startTimer() {
        countdown = 60;
        const btnResend = document.getElementById('btn-resend');

        btnResend.disabled = true;
        btnResend.onclick = null;
        btnResend.className = 'text-xs font-bold text-slate-400 cursor-not-allowed';
        btnResend.innerHTML = 'Gửi lại mã sau <span id="timer">60</span>s';
        const timerText = document.getElementById('timer');

        timerId = setInterval(() => {
            countdown--;
            timerText.innerText = countdown;
            if (countdown <= 0) {
                clearInterval(timerId);
                btnResend.disabled = false;
                btnResend.className = 'text-xs font-black text-rose-600 hover:underline cursor-pointer';
                btnResend.innerHTML = 'Gửi lại mã ngay';
                btnResend.onclick = sendCodeToEmail;
            }
        }, 1000);
    }

    function togglePass(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    }

    document.getElementById('form-step-3').addEventListener('submit', async function(event) {
        event.preventDefault();

        if (!resetFlowToken) {
            showFeedback('error', 'Phiên khôi phục không hợp lệ. Vui lòng thực hiện lại từ đầu.');
            return;
        }

        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        try {
            const result = await postForgotPassword('/api/index.php?resource=passwords&method=confirm-reset', {
                flow_token: resetFlowToken,
                new_password: newPassword,
                confirm_password: confirmPassword
            });

            showFeedback('success', result.message || 'Mật khẩu đã được cập nhật thành công.');
            const redirectUrl = String((result.data || {}).redirect_url || '/?page=login');
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1200);
        } catch (error) {
            showFeedback('error', error.message || 'Không thể cập nhật mật khẩu mới.');
        }
    });
</script>
