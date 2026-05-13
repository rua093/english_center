<style>
    .focus-lime:focus { border-color: #a3e635; box-shadow: 0 0 0 4px rgba(163, 230, 53, 0.1); }
    .form-card { background: #ffffff; border-radius: 2rem; border: 1px solid #e2e8f0; }
    .section-header { border-bottom: 2px dashed #f1f5f9; padding-bottom: 1.25rem; margin-bottom: 1.5rem; }
    .section-title { font-weight: 900; color: #1e293b; text-transform: uppercase; display: flex; align-items: center; gap: 0.75rem; }
    .title-number { background: #e11d48; color: white; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; font-size: 1rem; }
    
    /* Custom Checkbox/Radio Styling */
    .custom-option { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; border-radius: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; }
    .custom-option:hover { border-color: #a3e635; background: #f7fee7; }
    .custom-option input:checked + span { font-weight: 800; color: #e11d48; }
    .custom-option:has(input:checked) { border-color: #e11d48; background: #fff1f2; box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.1); }

    .animate-card {
        animation: cardSlideIn 0.75s cubic-bezier(0.2, 0.9, 0.2, 1) both;
        will-change: transform, opacity;
    }

    .animate-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 36px -18px rgba(15, 23, 42, 0.35);
    }

    .animate-card:nth-of-type(1) { animation-delay: 0.15s; }
    .animate-card:nth-of-type(2) { animation-delay: 0.38s; }
    .animate-card:nth-of-type(3) { animation-delay: 0.61s; }
    .animate-card:nth-of-type(4) { animation-delay: 0.84s; }

    .animate-option {
        animation: optionSlideIn 0.5s ease both;
        will-change: transform, opacity;
    }

    .animate-option:hover {
        transform: translateY(-2px) scale(1.01);
    }

    .animate-option:nth-of-type(1) { animation-delay: 0.16s; }
    .animate-option:nth-of-type(2) { animation-delay: 0.28s; }
    .animate-option:nth-of-type(3) { animation-delay: 0.4s; }
    .animate-option:nth-of-type(4) { animation-delay: 0.52s; }
    .animate-option:nth-of-type(5) { animation-delay: 0.64s; }

    .hero-card {
        animation: heroFloatIn 0.9s ease both;
    }

    .hero-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.36) 50%, transparent 100%);
        transform: translateX(-120%);
        animation: shimmerSweep 5.5s ease-in-out infinite;
        pointer-events: none;
    }

    .submit-animated {
        animation: buttonPulseIn 0.7s ease both;
    }

    .submit-animated:hover {
        transform: translateY(-2px) scale(1.01);
    }

    @keyframes cardSlideIn {
        from { opacity: 0; transform: translateX(-56px) translateY(12px) scale(0.98); filter: blur(2px); }
        to { opacity: 1; transform: translateX(0) translateY(0) scale(1); filter: blur(0); }
    }

    @keyframes optionSlideIn {
        from { opacity: 0; transform: translateX(-28px) translateY(10px); }
        to { opacity: 1; transform: translateX(0) translateY(0); }
    }

    @keyframes heroFloatIn {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes shimmerSweep {
        0%, 65% { transform: translateX(-120%); opacity: 0; }
        20% { opacity: 1; }
        40% { transform: translateX(120%); opacity: 0; }
        100% { transform: translateX(120%); opacity: 0; }
    }

    @keyframes buttonPulseIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-card,
        .animate-option,
        .hero-card,
        .submit-animated {
            animation: none !important;
            transition: none !important;
        }

        .hero-card::after {
            animation: none !important;
        }
    }
</style>

<?php
$success = get_flash('home_success');
$error = get_flash('home_error');
$prefillPayload = api_decode_payload((string) ($_GET['prefill'] ?? ''));
if (!is_array($prefillPayload)) {
    $prefillPayload = [];
}

$prefillValue = static function (string $key, string $fallback = '') use ($prefillPayload): string {
    return trim((string) ($prefillPayload[$key] ?? $fallback));
};

$prefillLeadId = max(0, (int) ($prefillPayload['lead_id'] ?? 0));
$prefillStudentName = $prefillValue('student_name');
$prefillStudentDob = $prefillValue('student_dob', $prefillValue('dob'));
$prefillParentName = $prefillValue('parent_name');
$prefillParentPhone = $prefillValue('parent_phone', $prefillValue('phone'));
$prefillParentEmail = $prefillValue('parent_email');
$prefillStudentHobbies = $prefillValue('student_hobbies', $prefillValue('interests'));
$prefillStudentSchool = $prefillValue('student_school', $prefillValue('school_name'));
$prefillStudentGrade = $prefillValue('student_grade', $prefillValue('current_grade'));
$prefillCurrentLevel = $prefillValue('current_level');
$prefillStudentGender = $prefillValue('student_gender', $prefillValue('gender'));
$prefillStudentPersonality = $prefillValue('student_personality', $prefillValue('personality'));
?>

<main class="py-12 md:py-16 font-jakarta">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <div class="mb-10 text-center bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden hero-card">
            <div class="absolute top-0 left-0 w-2 h-full bg-rose-600"></div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-left flex items-center gap-4">
                    <img src="assets/images/logo_remove.png" alt="<?= e(t('consult.logo_alt')); ?>" class="h-16 w-auto">
                    <div>
                        <h2 class="text-xl font-black text-rose-600 tracking-tight"><?= e(t('consult.center_name')); ?></h2>
                        <p class="text-sm font-bold text-slate-500">Hotline: <a href="tel:0899925259" class="text-slate-800 hover:text-rose-600">0899 925 259</a></p>
                    </div>
                </div>
                <div class="text-right">
                    <h1 class="text-2xl md:text-3xl font-black text-slate-800 uppercase"><?= e(t('consult.title')); ?></h1>
                    <p class="text-sm font-medium text-slate-500 mt-1"><?= e(t('consult.subtitle')); ?></p>
                </div>
            </div>
        </div>

        <form action="/api/index.php?resource=leads&method=submit-consultation" method="POST" class="space-y-8" id="consultationForm">
            <?= csrf_input(); ?>
            <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? page_url('register-consultation')); ?>">
            <?php if ($prefillLeadId > 0): ?>
                <input type="hidden" name="lead_id" value="<?= $prefillLeadId; ?>">
            <?php endif; ?>
            
            <!-- <section class="form-card p-6 md:p-8 shadow-md animate-card">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Ngày tư vấn *</label>
                        <input type="date" name="consultation_date" value="<?= date('Y-m-d') ?>" required class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Nhân viên tư vấn *</label>
                        <input type="text" name="consultant_name" required placeholder="Nhập tên nhân viên..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                </div>
            </section> -->

            <section class="form-card p-6 md:p-8 shadow-md animate-card">
                <div class="section-header">
                    <h2 class="section-title"><span class="title-number">I</span> <?= e(t('consult.student_info')); ?></h2>
                </div>
                
                <div class="grid md:grid-cols-12 gap-6 mb-6">
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.student_name')); ?></label>
                        <input type="text" name="student_name" required value="<?= e($prefillStudentName); ?>" placeholder="Nguyễn Văn A" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.gender')); ?></label>
                        <div class="flex gap-4">
                            <label class="custom-option animate-option flex-1 justify-center">
                                <input type="radio" name="student_gender" value="Nam" class="w-4 h-4 accent-rose-600" required <?= $prefillStudentGender === 'Nam' ? 'checked' : ''; ?>>
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.gender_male')); ?></span>
                            </label>
                            <label class="custom-option animate-option flex-1 justify-center">
                                <input type="radio" name="student_gender" value="Nữ" class="w-4 h-4 accent-rose-600" <?= $prefillStudentGender === 'Nữ' ? 'checked' : ''; ?>>
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.gender_female')); ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.birthdate')); ?></label>
                        <input type="date" name="student_dob" required value="<?= e($prefillStudentDob); ?>" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.hobbies')); ?></label>
                        <input type="text" name="student_hobbies" required value="<?= e($prefillStudentHobbies); ?>" placeholder="<?= e(t('consult.hobbies_placeholder')); ?>" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                </div>

                <div class="grid md:grid-cols-12 gap-6 mb-8">
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.school')); ?></label>
                        <input type="text" name="student_school" required value="<?= e($prefillStudentSchool); ?>" placeholder="<?= e(t('consult.school_placeholder')); ?>" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.grade')); ?></label>
                        <input type="text" name="student_grade" required value="<?= e($prefillStudentGrade); ?>" placeholder="<?= e(t('consult.grade_placeholder')); ?>" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.personality')); ?></label>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <label class="custom-option animate-option">
                            <input type="radio" name="student_personality" value="Hướng nội" class="w-4 h-4 accent-rose-600" required <?= $prefillStudentPersonality === 'Hướng nội' ? 'checked' : ''; ?>>
                            <span class="text-sm font-medium text-slate-600"><?= e(t('consult.personality_introvert')); ?></span>
                        </label>
                        <label class="custom-option animate-option">
                            <input type="radio" name="student_personality" value="Hướng ngoại" class="w-4 h-4 accent-rose-600" <?= $prefillStudentPersonality === 'Hướng ngoại' ? 'checked' : ''; ?>>
                            <span class="text-sm font-medium text-slate-600"><?= e(t('consult.personality_extrovert')); ?></span>
                        </label>
                        <label class="custom-option animate-option">
                            <input type="radio" name="student_personality" value="Trung bình" class="w-4 h-4 accent-rose-600" <?= $prefillStudentPersonality === 'Trung bình' ? 'checked' : ''; ?>>
                            <span class="text-sm font-medium text-slate-600"><?= e(t('consult.personality_balanced')); ?></span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="form-card p-6 md:p-8 shadow-md animate-card">
                <div class="section-header">
                    <h2 class="section-title"><span class="title-number">II</span> <?= e(t('consult.parent_info')); ?></h2>
                </div>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.parent_name')); ?></label>
                        <input type="text" name="parent_name" required value="<?= e($prefillParentName); ?>" placeholder="<?= e(t('consult.parent_name_placeholder')); ?>" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Số điện thoại liên hệ *</label>
                        <input type="tel" inputmode="numeric" pattern="[0-9]*" name="parent_phone" required value="<?= e($prefillParentPhone); ?>" placeholder="09xx xxx xxx" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.parent_email')); ?></label>
                        <input type="email" name="parent_email" value="<?= e($prefillParentEmail); ?>" placeholder="phuhuynh@example.com" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>

                    <div class="space-y-3 pt-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1"><?= e(t('consult.source')); ?></label>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="custom-option animate-option">
                                <input type="checkbox" name="source_channels[]" value="Người quen giới thiệu" class="w-4 h-4 accent-rose-600 rounded">
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.source_referral')); ?></span>
                            </label>
                            <label class="custom-option animate-option">
                                <input type="checkbox" name="source_channels[]" value="Tờ rơi/Quảng cáo" class="w-4 h-4 accent-rose-600 rounded">
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.source_ads')); ?></span>
                            </label>
                            <label class="custom-option animate-option">
                                <input type="checkbox" name="source_channels[]" value="Mạng xã hội" class="w-4 h-4 accent-rose-600 rounded">
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.source_social')); ?></span>
                            </label>
                            <div class="relative group">
                                <div class="custom-option focus-within:border-rose-600 focus-within:bg-rose-50">
                                    <input type="checkbox" id="source_other_check" name="source_channels[]" value="Khác" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600 whitespace-nowrap"><?= e(t('consult.other')); ?></span>
                                    <input type="text" name="source_other_detail" placeholder="<?= e(t('consult.other_placeholder')); ?>" class="w-full bg-transparent border-b border-slate-300 outline-none text-sm font-bold text-slate-700 ml-2 focus:border-rose-600 placeholder:font-normal">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="form-card p-6 md:p-8 shadow-md animate-card">
                <div class="section-header">
                    <h2 class="section-title"><span class="title-number">III</span> <?= e(t('consult.goals')); ?></h2>
                </div>
                
                <div class="grid lg:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-rose-600 uppercase mb-3"><?= e(t('consult.current_level')); ?></h3>
                        <div class="space-y-3">
                            <label class="custom-option animate-option">
                                <input type="radio" name="current_level" value="Chưa tiếp xúc" class="w-4 h-4 accent-rose-600" required <?= $prefillCurrentLevel === 'Chưa tiếp xúc' ? 'checked' : ''; ?>>
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.level_none')); ?></span>
                            </label>
                            <label class="custom-option animate-option">
                                <input type="radio" name="current_level" value="Biết từ vựng cơ bản" class="w-4 h-4 accent-rose-600" <?= $prefillCurrentLevel === 'Biết từ vựng cơ bản' ? 'checked' : ''; ?>>
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.level_vocab')); ?></span>
                            </label>
                            <label class="custom-option animate-option">
                                <input type="radio" name="current_level" value="Nghe hiểu được, phản xạ chậm" class="w-4 h-4 accent-rose-600" <?= $prefillCurrentLevel === 'Nghe hiểu được, phản xạ chậm' ? 'checked' : ''; ?>>
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.level_slow')); ?></span>
                            </label>
                            <label class="custom-option animate-option">
                                <input type="radio" name="current_level" value="Có nền tảng, giao tiếp cơ bản" class="w-4 h-4 accent-rose-600" <?= $prefillCurrentLevel === 'Có nền tảng, giao tiếp cơ bản' ? 'checked' : ''; ?>>
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.level_basic')); ?></span>
                            </label>
                            <label class="custom-option animate-option">
                                <input type="radio" name="current_level" value="Học tốt, muốn nâng cao" class="w-4 h-4 accent-rose-600" <?= $prefillCurrentLevel === 'Học tốt, muốn nâng cao' ? 'checked' : ''; ?>>
                                <span class="text-sm font-medium text-slate-600"><?= e(t('consult.level_advanced')); ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="space-y-4">
                            <h3 class="text-sm font-black text-rose-600 uppercase mb-3"><?= e(t('consult.available_time')); ?></h3>
                            <div class="grid grid-cols-3 gap-3 mb-3">
                                <label class="custom-option animate-option justify-center"><input type="checkbox" name="available_shifts[]" value="Sáng" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600"><?= e(t('consult.shift_morning')); ?></span></label>
                                <label class="custom-option animate-option justify-center"><input type="checkbox" name="available_shifts[]" value="Chiều" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600"><?= e(t('consult.shift_afternoon')); ?></span></label>
                                <label class="custom-option animate-option justify-center"><input type="checkbox" name="available_shifts[]" value="Tối" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600"><?= e(t('consult.shift_evening')); ?></span></label>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="custom-option animate-option justify-center"><input type="checkbox" name="available_days[]" value="Ngày trong tuần" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600"><?= e(t('consult.weekdays')); ?></span></label>
                                <label class="custom-option animate-option justify-center"><input type="checkbox" name="available_days[]" value="Cuối tuần" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600"><?= e(t('consult.weekend')); ?></span></label>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-sm font-black text-rose-600 uppercase mb-3"><?= e(t('consult.expectation_title')); ?></h3>
                            <div class="space-y-3">
                                <label class="custom-option animate-option">
                                    <input type="checkbox" name="parent_expectations[]" value="Tiến bộ giao tiếp" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600"><?= e(t('consult.expect_communication')); ?></span>
                                </label>
                                <label class="custom-option animate-option">
                                    <input type="checkbox" name="parent_expectations[]" value="Điểm cao tại trường" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600"><?= e(t('consult.expect_school_score')); ?></span>
                                </label>
                                <label class="custom-option animate-option">
                                    <input type="checkbox" name="parent_expectations[]" value="Lộ trình chứng chỉ quốc tế" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600"><?= e(t('consult.expect_certificate')); ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="text-center pt-4">
                <button type="submit" class="w-full md:w-auto px-16 py-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-black uppercase tracking-widest shadow-xl shadow-rose-600/30 transition-all hover:-translate-y-1 flex justify-center items-center gap-3 mx-auto submit-animated">
                    <?= e(t('consult.submit')); ?>
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </button>
            </div>

        </form>
    </div>
</main>

<?php $showConfirmTestButtons = false; require __DIR__ . '/../notification/confirm_modal.php'; ?>
<?php $notifyShowTestButtons = false; require __DIR__ . '/../notification/notification.php'; ?>

<script>
    // Logic nhỏ: Nếu gõ text vào ô "Khác", tự động check vào checkbox "Khác"
    const sourceOtherInput = document.querySelector('input[name="source_other_detail"]');
    const sourceOtherCheck = document.getElementById('source_other_check');
    const consultationForm = document.getElementById('consultationForm');
    
    if (sourceOtherInput && sourceOtherCheck) {
        sourceOtherInput.addEventListener('input', function() {
            if(this.value.trim().length > 0) {
                sourceOtherCheck.checked = true;
                this.required = true;
            } else {
                sourceOtherCheck.checked = false;
                this.required = false;
            }
        });
    }

    if (sourceOtherCheck && sourceOtherInput) {
        sourceOtherCheck.addEventListener('change', function() {
            sourceOtherInput.required = this.checked;
            if (!this.checked) {
                sourceOtherInput.value = '';
            }
        });
    }

    if (consultationForm) {
        consultationForm.addEventListener('submit', function(event) {
            event.preventDefault();

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            const requiredCheckboxGroups = [
                'source_channels[]',
                'parent_expectations[]'
            ];
            const checkboxGroupLabels = {
                'source_channels[]': <?= json_encode(t('consult.source'), JSON_UNESCAPED_UNICODE); ?>,
                'parent_expectations[]': <?= json_encode(t('consult.expectation_title'), JSON_UNESCAPED_UNICODE); ?>
            };

            for (const groupName of requiredCheckboxGroups) {
                const groupCheckboxes = Array.from(this.querySelectorAll(`input[type="checkbox"][name="${groupName}"]`));
                if (groupCheckboxes.length > 0 && !groupCheckboxes.some((checkbox) => checkbox.checked)) {
                    groupCheckboxes[0].focus();
                    const groupLabel = checkboxGroupLabels[groupName] || groupName.replace('[]', '');
                    if (typeof showNotify === 'function') {
                        showNotify('warning', <?= json_encode(t('consult.select_group_required'), JSON_UNESCAPED_UNICODE); ?> + groupLabel);
                    } else {
                        alert(<?= json_encode(t('consult.select_group_required'), JSON_UNESCAPED_UNICODE); ?> + groupLabel);
                    }
                    return;
                }
            }

            const availableTimeCheckboxes = Array.from(this.querySelectorAll('input[type="checkbox"][name="available_shifts[]"], input[type="checkbox"][name="available_days[]"]'));
            if (availableTimeCheckboxes.length > 0 && !availableTimeCheckboxes.some((checkbox) => checkbox.checked)) {
                availableTimeCheckboxes[0].focus();
                if (typeof showNotify === 'function') {
                    showNotify('warning', <?= json_encode(t('consult.select_group_required'), JSON_UNESCAPED_UNICODE); ?> + <?= json_encode(t('consult.available_time'), JSON_UNESCAPED_UNICODE); ?>);
                } else {
                    alert(<?= json_encode(t('consult.select_group_required'), JSON_UNESCAPED_UNICODE); ?> + <?= json_encode(t('consult.available_time'), JSON_UNESCAPED_UNICODE); ?>);
                }
                return;
            }

            if (sourceOtherCheck && sourceOtherCheck.checked && sourceOtherInput && sourceOtherInput.value.trim() === '') {
                sourceOtherInput.focus();
                if (typeof showNotify === 'function') {
                    showNotify('warning', <?= json_encode(t('consult.other_required'), JSON_UNESCAPED_UNICODE); ?>);
                } else {
                    alert(<?= json_encode(t('consult.other_required'), JSON_UNESCAPED_UNICODE); ?>);
                }
                return;
            }

            if (typeof showConfirm === 'function') {
                showConfirm(
                    'success',
                    <?= json_encode(t('consult.confirm_title'), JSON_UNESCAPED_UNICODE); ?>,
                    <?= json_encode(t('consult.confirm_message'), JSON_UNESCAPED_UNICODE); ?>,
                    () => consultationForm.submit()
                );
            } else {
                consultationForm.submit();
            }
        });
    }

    <?php if (!empty($success)): ?>
    if (typeof showNotify === 'function') {
        showNotify('success', <?= json_encode($success, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
    }
    <?php endif; ?>

    <?php if (!empty($error)): ?>
    if (typeof showNotify === 'function') {
        showNotify('error', <?= json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
    }
    <?php endif; ?>
</script>
