<style>
    /* Các class hỗ trợ hiệu ứng hiển thị mượt mà */
    #edtech-notify-modal { transition: opacity 0.3s ease-in-out; }
    #edtech-notify-content { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .progress-bar-anim { transition: transform 2000ms linear; transform-origin: left; }
</style>

<div id="edtech-notify-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm opacity-0">
    <div id="edtech-notify-content" class="bg-white rounded-[2rem] shadow-2xl p-8 max-w-sm w-[90%] transform scale-90 relative overflow-hidden text-center border-2 border-white">
        
        <div id="notify-icon-bg" class="w-20 h-20 mx-auto rounded-[1.5rem] flex items-center justify-center mb-5 shadow-inner border-4 border-white transition-colors duration-300">
            <i id="notify-icon" class="text-4xl transition-colors duration-300"></i>
        </div>
        
        <h3 id="notify-title" class="text-xl font-black mb-2 uppercase tracking-tight transition-colors duration-300"></h3>
        <p id="notify-message" class="text-slate-500 font-medium text-sm mb-8 leading-relaxed"></p>
        
        <button onclick="closeNotify()" id="notify-btn" class="w-full font-black py-3.5 rounded-xl text-white shadow-lg transition-all hover:-translate-y-1 uppercase tracking-widest text-sm active:translate-y-0">
            XÁC NHẬN
        </button>

        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-slate-100">
            <div id="notify-progress" class="h-full w-full progress-bar-anim"></div>
        </div>
    </div>
</div>

<script>
    let notifyTimeout;

    /**
     * Hàm gọi hiển thị thông báo
     * @param {string} type - 'success', 'error', 'warning', 'info'
     * @param {string} message - Nội dung cần hiển thị
     */
    function showNotify(type, message) {
        const modal = document.getElementById('edtech-notify-modal');
        const content = document.getElementById('edtech-notify-content');
        const icon = document.getElementById('notify-icon');
        const iconBg = document.getElementById('notify-icon-bg');
        const title = document.getElementById('notify-title');
        const msg = document.getElementById('notify-message');
        const btn = document.getElementById('notify-btn');
        const progress = document.getElementById('notify-progress');

        // Khai báo cấu hình màu sắc tường minh (Tránh lỗi mất màu của Tailwind)
        const config = {
            'success': {
                title: 'Thành công',
                iconClass: 'fa-solid fa-circle-check text-emerald-500',
                bgClass: 'bg-emerald-50',
                titleClass: 'text-emerald-600',
                btnClass: 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/30',
                progressColor: '#10b981' // emerald-500
            },
            'error': {
                title: 'Thất bại',
                iconClass: 'fa-solid fa-circle-xmark text-rose-500',
                bgClass: 'bg-rose-50',
                titleClass: 'text-rose-600',
                btnClass: 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30',
                progressColor: '#e11d48' // rose-600
            },
            'warning': {
                title: 'Cảnh báo',
                iconClass: 'fa-solid fa-triangle-exclamation text-amber-500',
                bgClass: 'bg-amber-50',
                titleClass: 'text-amber-600',
                btnClass: 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/30',
                progressColor: '#f59e0b' // amber-500
            },
            'info': {
                title: 'Thông báo',
                iconClass: 'fa-solid fa-bell text-blue-500',
                bgClass: 'bg-blue-50',
                titleClass: 'text-blue-600',
                btnClass: 'bg-blue-500 hover:bg-blue-600 shadow-blue-500/30',
                progressColor: '#3b82f6' // blue-500
            }
        };

        // Nếu truyền sai type, mặc định dùng 'info'
        const currentConfig = config[type] || config['info'];

        // 1. Reset toàn bộ class cũ
        icon.className = `text-4xl transition-colors duration-300 ${currentConfig.iconClass}`;
        iconBg.className = `w-20 h-20 mx-auto rounded-[1.5rem] flex items-center justify-center mb-5 shadow-inner border-4 border-white transition-colors duration-300 ${currentConfig.bgClass}`;
        title.className = `text-xl font-black mb-2 uppercase tracking-tight transition-colors duration-300 ${currentConfig.titleClass}`;
        btn.className = `w-full font-black py-3.5 rounded-xl text-white shadow-lg transition-all hover:-translate-y-1 uppercase tracking-widest text-sm active:translate-y-0 ${currentConfig.btnClass}`;
        
        // 2. Đổ dữ liệu text
        title.innerText = currentConfig.title;
        msg.innerHTML = message;
        progress.style.backgroundColor = currentConfig.progressColor;

        // 3. Bật Modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Kích hoạt hiệu ứng xuất hiện
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-90');
            content.classList.add('scale-100');
            
            // Xử lý thanh tiến trình (Reset về 100% rồi cho chạy về 0% trong 2s)
            progress.style.transition = 'none';
            progress.style.transform = 'scaleX(1)';
            requestAnimationFrame(() => {
                progress.style.transition = 'transform 2000ms linear';
                progress.style.transform = 'scaleX(0)';
            });
        });

        // 4. Xóa timeout cũ nếu click liên tục
        if (notifyTimeout) clearTimeout(notifyTimeout);

        // 5. Tự động đóng sau 2 giây (2000ms)
        notifyTimeout = setTimeout(() => {
            closeNotify();
        }, 2000);
    }

    /**
     * Hàm đóng thông báo
     */
    function closeNotify() {
        const modal = document.getElementById('edtech-notify-modal');
        const content = document.getElementById('edtech-notify-content');
        
        // Kích hoạt hiệu ứng biến mất
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-90');
        
        // Đợi 300ms cho hiệu ứng CSS hoàn thành rồi mới ẩn thẻ HTML
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
</script>

<?php if (!empty($notifyShowTestButtons)): ?>
<div class="p-10 flex gap-4 justify-center bg-slate-50 mt-10 rounded-2xl">
    <button onclick="showNotify('success', 'Đăng ký khóa học IELTS thành công! Vui lòng kiểm tra email.')" class="px-6 py-2 bg-emerald-500 text-white rounded-lg font-bold">Test Success</button>
    <button onclick="showNotify('error', 'Hệ thống đang bảo trì hoặc sai mật khẩu. Vui lòng thử lại.')" class="px-6 py-2 bg-rose-600 text-white rounded-lg font-bold">Test Error</button>
    <button onclick="showNotify('warning', 'Tài khoản của bạn sắp hết hạn trong vòng 2 ngày tới.')" class="px-6 py-2 bg-amber-500 text-white rounded-lg font-bold">Test Warning</button>
    <button onclick="showNotify('info', 'Bài tập mới môn Giao tiếp cơ bản đã được giảng viên cập nhật.')" class="px-6 py-2 bg-blue-500 text-white rounded-lg font-bold">Test Info</button>
</div>
<?php endif; ?>