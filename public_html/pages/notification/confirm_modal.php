<style>
    #edtech-confirm-modal { transition: opacity 0.3s ease-in-out; }
    #edtech-confirm-content { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
</style>

<div id="edtech-confirm-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm opacity-0">
    <div id="edtech-confirm-content" class="bg-white rounded-[2rem] shadow-2xl p-8 md:p-10 max-w-md w-[90%] transform scale-90 relative overflow-hidden text-center border border-slate-100">
        
        <div id="confirm-glow" class="absolute -top-10 -left-10 w-40 h-40 rounded-full blur-[60px] opacity-50 pointer-events-none transition-colors duration-300"></div>

        <div id="confirm-icon-bg" class="relative z-10 w-24 h-24 mx-auto rounded-[1.5rem] flex items-center justify-center mb-6 shadow-inner border-4 border-white transition-colors duration-300">
            <i id="confirm-icon" class="text-5xl transition-colors duration-300"></i>
        </div>
        
        <h3 id="confirm-title" class="relative z-10 text-2xl font-black mb-3 text-slate-800 tracking-tight transition-colors duration-300"></h3>
        <p id="confirm-message" class="relative z-10 text-slate-500 font-medium text-sm mb-10 leading-relaxed px-2"></p>
        
        <div class="relative z-10 flex gap-4">
            <button onclick="closeConfirm()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black py-3.5 rounded-xl transition-all uppercase tracking-widest text-xs">
                <?= e(t('confirm.cancel')); ?>
            </button>
            <button id="confirm-ok-btn" class="flex-1 font-black py-3.5 rounded-xl text-white shadow-lg transition-all hover:-translate-y-1 uppercase tracking-widest text-xs active:translate-y-0">
                <?= e(t('confirm.ok')); ?>
            </button>
        </div>
    </div>
</div>

<script>
    // Biến lưu trữ hàm sẽ được gọi khi bấm "Xác nhận"
    let confirmActionCallback = null;

    /**
     * Hàm gọi hiển thị hộp thoại xác nhận
     * @param {string} type - 'danger' (Xóa/Hủy), 'success' (Lưu/Gửi), 'warning' (Cảnh báo), 'info' (Hỏi đáp)
     * @param {string} title - Tiêu đề hộp thoại
     * @param {string} message - Nội dung chi tiết
     * @param {function} callback - Hàm thực thi sau khi bấm Xác nhận
     */
    function showConfirm(type, title, message, callback) {
        const modal = document.getElementById('edtech-confirm-modal');
        const content = document.getElementById('edtech-confirm-content');
        
        const icon = document.getElementById('confirm-icon');
        const iconBg = document.getElementById('confirm-icon-bg');
        const glow = document.getElementById('confirm-glow');
        const titleEl = document.getElementById('confirm-title');
        const msgEl = document.getElementById('confirm-message');
        const btnOk = document.getElementById('confirm-ok-btn');

        // Cấu hình giao diện theo loại hành động
        const config = {
            'danger': {
                iconClass: 'fa-solid fa-trash-can text-rose-500',
                bgClass: 'bg-rose-50',
                glowClass: 'bg-rose-500',
                btnClass: 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30'
            },
            'success': {
                iconClass: 'fa-solid fa-paper-plane text-emerald-500',
                bgClass: 'bg-emerald-50',
                glowClass: 'bg-emerald-500',
                btnClass: 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/30'
            },
            'warning': {
                iconClass: 'fa-solid fa-triangle-exclamation text-amber-500',
                bgClass: 'bg-amber-50',
                glowClass: 'bg-amber-500',
                btnClass: 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/30'
            },
            'info': {
                iconClass: 'fa-regular fa-circle-question text-blue-500',
                bgClass: 'bg-blue-50',
                glowClass: 'bg-blue-500',
                btnClass: 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/30'
            }
        };

        const currentConfig = config[type] || config['info'];

        // Cập nhật UI
        icon.className = `text-5xl transition-colors duration-300 ${currentConfig.iconClass}`;
        iconBg.className = `relative z-10 w-24 h-24 mx-auto rounded-[1.5rem] flex items-center justify-center mb-6 shadow-inner border-4 border-white transition-colors duration-300 ${currentConfig.bgClass}`;
        glow.className = `absolute -top-10 -left-10 w-40 h-40 rounded-full blur-[60px] opacity-50 pointer-events-none transition-colors duration-300 ${currentConfig.glowClass}`;
        btnOk.className = `flex-1 font-black py-3.5 rounded-xl text-white shadow-lg transition-all hover:-translate-y-1 uppercase tracking-widest text-xs active:translate-y-0 ${currentConfig.btnClass}`;
        
        titleEl.innerText = title;
        msgEl.innerHTML = message;

        // Lưu callback
        confirmActionCallback = callback;

        // Hiển thị Modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-90');
            content.classList.add('scale-100');
        });
    }

    /**
     * Hàm đóng hộp thoại (Khi bấm Hủy hoặc sau khi Xác nhận xong)
     */
    function closeConfirm() {
        const modal = document.getElementById('edtech-confirm-modal');
        const content = document.getElementById('edtech-confirm-content');
        
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-90');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            confirmActionCallback = null; // Xóa callback để tránh gọi nhầm lần sau
        }, 300);
    }

    // Gắn sự kiện cho nút Xác nhận
    document.getElementById('confirm-ok-btn').addEventListener('click', function() {
        if (typeof confirmActionCallback === 'function') {
            confirmActionCallback(); // Chạy lệnh của bạn
        }
        closeConfirm(); // Chạy xong thì tự động đóng form
    });
</script>

<?php if (!empty($showConfirmTestButtons)): ?>
<div class="p-10 flex flex-wrap gap-4 justify-center bg-slate-50 mt-10 rounded-2xl">
    
    <button onclick="
        showConfirm('danger', <?= json_encode(t('confirm.delete_document_title'), JSON_UNESCAPED_UNICODE); ?>, <?= json_encode(t('confirm.delete_document_message'), JSON_UNESCAPED_UNICODE); ?>, function() {
            alert(<?= json_encode(t('confirm.delete_done'), JSON_UNESCAPED_UNICODE); ?>);
        })
    " class="px-6 py-2 bg-rose-600 text-white rounded-lg font-bold shadow-md"><?= e(t('confirm.test_delete')); ?></button>
    
    <button onclick="
        showConfirm('success', <?= json_encode(t('confirm.submit_test_title'), JSON_UNESCAPED_UNICODE); ?>, <?= json_encode(t('confirm.submit_test_message'), JSON_UNESCAPED_UNICODE); ?>, function() {
            alert(<?= json_encode(t('confirm.submit_done'), JSON_UNESCAPED_UNICODE); ?>);
        })
    " class="px-6 py-2 bg-emerald-500 text-white rounded-lg font-bold shadow-md"><?= e(t('confirm.test_submit')); ?></button>

    <button onclick="
        showConfirm('warning', <?= json_encode(t('confirm.cancel_course_title'), JSON_UNESCAPED_UNICODE); ?>, <?= json_encode(t('confirm.cancel_course_message'), JSON_UNESCAPED_UNICODE); ?>, function() {
            alert(<?= json_encode(t('confirm.cancel_done'), JSON_UNESCAPED_UNICODE); ?>);
        })
    " class="px-6 py-2 bg-amber-500 text-white rounded-lg font-bold shadow-md"><?= e(t('confirm.test_warning')); ?></button>

    <button onclick="
        showConfirm('info', <?= json_encode(t('confirm.logout_title'), JSON_UNESCAPED_UNICODE); ?>, <?= json_encode(t('confirm.logout_message'), JSON_UNESCAPED_UNICODE); ?>, function() {
            window.location.href = '/logout.php'; // Ví dụ chuyển hướng thực tế
        })
    " class="px-6 py-2 bg-blue-500 text-white rounded-lg font-bold shadow-md"><?= e(t('confirm.test_logout')); ?></button>

</div>
<?php endif; ?>
