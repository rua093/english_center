<?php
require_login();

$user = auth_user();
$fullName = $user['full_name'] ?? t('feedback.default_name');
$studentDashboardActiveTab = 'feedback';
$success = get_flash('success');
$error = get_flash('error');
?>

<style>
    .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.6); }
    .focus-emerald:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); outline: none; }
    
    /* Hiệu ứng cho ngôi sao */
    .rating-star { cursor: pointer; transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.2s; }
    .rating-star:hover { transform: scale(1.2); }
</style>

<section class="min-h-screen bg-[#f8fafc] font-jakarta relative overflow-hidden py-8 px-2 sm:px-4 lg:px-6 xl:px-8">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-slate-50 to-rose-50"></div>
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-emerald-300/20 rounded-full blur-[80px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-rose-300/20 rounded-full blur-[80px] animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute inset-0 opacity-[0.2]" style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    </div>

    <div class="mx-auto w-full max-w-[1800px] relative z-10">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
            <aside class="lg:sticky lg:top-24">
                <?php require __DIR__ . '/../student-dashboard/partials/nav.php'; ?>
            </aside>

            <div class="min-w-0 space-y-8">
                <div class="mb-2 flex justify-start">
                    <a class="group inline-flex items-center gap-2 rounded-full bg-white/60 backdrop-blur-md border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 shadow-sm transition-all hover:bg-white hover:text-emerald-600" href="/">
                        <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> <?= e(t('feedback.back_home')); ?>
                    </a>
                </div>

                <?php if ($success || $error): ?>
                    <div class="mb-0" data-aos="fade-down">
                        <?php if ($success): ?>
                            <div class="rounded-2xl border-l-4 border-l-emerald-500 bg-emerald-50/90 backdrop-blur-sm p-4 shadow-sm flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-check"></i></div>
                                <p class="text-sm font-bold text-emerald-800"><?= e($success); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="rounded-2xl border-l-4 border-l-rose-500 bg-rose-50/90 backdrop-blur-sm p-4 shadow-sm flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                <p class="text-sm font-bold text-rose-800"><?= e($error); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="glass-card rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden flex flex-col md:flex-row" data-aos="fade-up">
                    
                    <div class="md:w-5/12 bg-emerald-600 p-8 md:p-10 relative overflow-hidden flex flex-col justify-center text-white">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-700"></div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl translate-x-1/2 -translate-y-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 bg-teal-900/20 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>
                        
                        <div class="relative z-10">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl backdrop-blur-sm flex items-center justify-center text-3xl mb-6 shadow-inner">
                                <i class="fa-regular fa-comment-dots"></i>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-black mb-4 leading-tight tracking-tight">
                                <?= e(t('feedback.hero_title_1')); ?> <br> <span class="text-emerald-200"><?= e(t('feedback.hero_title_2')); ?></span>
                            </h2>
                            <p class="text-emerald-50 font-medium leading-relaxed text-sm mb-8">
                                <?= t('feedback.hero_copy', ['name' => '<strong>' . e($fullName) . '</strong>']); ?>
                            </p>
                            
                            <div class="bg-black/10 rounded-2xl p-5 border border-white/10 backdrop-blur-sm">
                                <div class="flex items-center gap-3 text-emerald-100 text-sm font-bold mb-2">
                                    <i class="fa-solid fa-shield-heart text-emerald-300"></i> <?= e(t('feedback.secure_title')); ?>
                                </div>
                                <p class="text-xs text-emerald-50/80"><?= e(t('feedback.secure_copy')); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="md:w-7/12 p-8 md:p-12 bg-white">
                        <div class="mb-8 border-b border-slate-100 pb-5">
                            <h3 class="text-2xl font-black text-slate-800"><?= e(t('feedback.form_title')); ?></h3>
                            <p class="text-sm font-medium text-slate-500 mt-1"><?= e(t('feedback.form_subtitle')); ?></p>
                        </div>

                        <form id="feedbackForm" action="/api/index.php?resource=feedbacks&method=save" method="POST" class="space-y-8">
                            <?= csrf_input(); ?>
                            <input type="hidden" name="redirect_to" value="<?= e(page_url('feedback')); ?>">
                            <input type="hidden" name="rating" id="rating_value" value="0" required>

                            <div class="space-y-3">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest"><?= e(t('feedback.rating_label')); ?> <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-2" id="star-container">
                                    <i class="fa-regular fa-star text-3xl text-slate-300 rating-star" data-value="1" title="<?= e(t('feedback.rating.1')); ?>"></i>
                                    <i class="fa-regular fa-star text-3xl text-slate-300 rating-star" data-value="2" title="<?= e(t('feedback.rating.2')); ?>"></i>
                                    <i class="fa-regular fa-star text-3xl text-slate-300 rating-star" data-value="3" title="<?= e(t('feedback.rating.3')); ?>"></i>
                                    <i class="fa-regular fa-star text-3xl text-slate-300 rating-star" data-value="4" title="<?= e(t('feedback.rating.4')); ?>"></i>
                                    <i class="fa-regular fa-star text-3xl text-slate-300 rating-star" data-value="5" title="<?= e(t('feedback.rating.5')); ?>"></i>
                                </div>
                                <p id="rating-text" class="text-sm font-bold text-amber-500 h-5"></p>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between items-end">
                                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest"><?= e(t('feedback.content_label')); ?> <span class="text-rose-500">*</span></label>
                                    <span class="text-[10px] font-bold text-slate-400"><?= e(t('feedback.content_min')); ?></span>
                                </div>
                                <div class="relative">
                                    <textarea name="content" id="feedback_content" rows="5" required placeholder="<?= e(t('feedback.content_placeholder')); ?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 text-slate-800 text-sm font-bold border border-slate-200 outline-none focus-emerald transition-all resize-none shadow-inner"></textarea>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" id="submitBtn" class="w-full bg-slate-900 hover:bg-emerald-600 text-white font-black py-4 rounded-2xl shadow-lg transition-all hover:-translate-y-1 text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-paper-plane"></i> <?= e(t('feedback.submit')); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$modalPath = __DIR__ . '/../notification/confirm_modal.php';
if(file_exists($modalPath)) require $modalPath; 
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.rating-star');
        const ratingInput = document.getElementById('rating_value');
        const ratingText = document.getElementById('rating-text');
        
        const ratingDescriptions = {
            1: <?= json_encode(t('feedback.rating_text.1'), JSON_UNESCAPED_UNICODE); ?>,
            2: <?= json_encode(t('feedback.rating_text.2'), JSON_UNESCAPED_UNICODE); ?>,
            3: <?= json_encode(t('feedback.rating_text.3'), JSON_UNESCAPED_UNICODE); ?>,
            4: <?= json_encode(t('feedback.rating_text.4'), JSON_UNESCAPED_UNICODE); ?>,
            5: <?= json_encode(t('feedback.rating_text.5'), JSON_UNESCAPED_UNICODE); ?>
        };

        // Hàm tô màu sao
        function highlightStars(value) {
            stars.forEach(star => {
                if (star.getAttribute('data-value') <= value) {
                    star.classList.remove('fa-regular', 'text-slate-300');
                    star.classList.add('fa-solid', 'text-amber-400');
                } else {
                    star.classList.remove('fa-solid', 'text-amber-400');
                    star.classList.add('fa-regular', 'text-slate-300');
                }
            });
            
            // Cập nhật text
            if(value > 0) {
                ratingText.textContent = ratingDescriptions[value];
            } else {
                ratingText.textContent = "";
            }
        }

        // Gắn sự kiện cho từng ngôi sao
        stars.forEach(star => {
            // Khi di chuột qua
            star.addEventListener('mouseover', function() {
                const val = this.getAttribute('data-value');
                highlightStars(val);
            });

            // Khi đưa chuột ra ngoài (Trả về giá trị đã click)
            star.addEventListener('mouseout', function() {
                highlightStars(ratingInput.value);
            });

            // Khi click chọn sao
            star.addEventListener('click', function() {
                ratingInput.value = this.getAttribute('data-value');
                highlightStars(ratingInput.value);
                
                // Hiệu ứng "nảy" cho sao vừa click
                this.classList.add('scale-125');
                setTimeout(() => {
                    this.classList.remove('scale-125');
                }, 200);
            });
        });

        // Xử lý Submit Form với Confirmation Modal (Nếu bạn có file confirm_modal.php)
        const feedbackForm = document.getElementById('feedbackForm');
        if (feedbackForm) {
            feedbackForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const currentRating = parseInt(ratingInput.value);
                const contentLen = document.getElementById('feedback_content').value.trim().length;

                // Validate JS cơ bản
                if (currentRating === 0) {
                    alert(<?= json_encode(t('feedback.validate_rating'), JSON_UNESCAPED_UNICODE); ?>);
                    return;
                }
                if (contentLen < 10) {
                    alert(<?= json_encode(t('feedback.validate_content'), JSON_UNESCAPED_UNICODE); ?>);
                    return;
                }

                // Gọi Confirm Modal
                if(typeof showConfirm === 'function') {
                    showConfirm(
                        'success',
                        <?= json_encode(t('feedback.confirm_title'), JSON_UNESCAPED_UNICODE); ?>,
                        <?= json_encode(t('feedback.confirm_message'), JSON_UNESCAPED_UNICODE); ?>,
                        () => feedbackForm.submit()
                    );
                } else {
                    feedbackForm.submit();
                }
            });
        }
    });
</script>
