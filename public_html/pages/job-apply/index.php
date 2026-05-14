
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<main class="relative overflow-hidden py-12 md:py-16 font-jakarta bg-lime-100">
    <div class="absolute inset-0 z-0 pointer-events-none opacity-[0.08]" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-72 pointer-events-none bg-gradient-to-b from-lime-200/75 via-lime-100/45 to-transparent"></div>
    <div class="relative z-10 container mx-auto px-4 max-w-7xl">
        
<section id="gioi-thieu" class="mb-16" data-aos="fade-up" data-aos-delay="180">
            <div class="relative overflow-hidden rounded-[2.5rem] shadow-2xl shadow-slate-200/50">
                
                <div class="absolute inset-0 z-0">
                    <img src="/assets/images/recruit.jpg" alt="<?= e(t('job.apply.image_alt')); ?>" class="w-full h-full object-cover brightness-110 contrast-105 saturate-110">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-900/70 via-slate-800/45 to-rose-900/25 mix-blend-multiply"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-white/15 via-transparent to-white/5"></div>
                </div>

                <div class="relative z-10 p-8 md:p-12 lg:p-16">
                    <div class="max-w-4xl mx-auto text-center">
                        
                        <div class="mb-12">
                            <span class="inline-flex items-center gap-2 text-[10px] font-black text-rose-200 bg-rose-500/20 border border-rose-400/30 px-4 py-2 rounded-full uppercase tracking-[0.2em] mb-6 backdrop-blur-sm shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                                <?= e(t('job.apply.about_badge')); ?>
                            </span>
                            
                            <h2 class="text-3xl md:text-5xl font-black text-white leading-tight tracking-tight mb-5" data-aos="fade-right" data-aos-delay="420">
                                <?= e(t('job.apply.hero_title')); ?> <br class="hidden md:block">
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-300 to-rose-500"><?= e(t('job.apply.hero_highlight')); ?></span>
                            </h2>
                            
                            <p class="text-slate-300 font-medium text-sm md:text-base leading-relaxed max-w-2xl mx-auto mb-8" data-aos="fade-up" data-aos-delay="600">
                                <?= t('job.apply.hero_copy'); ?>
                            </p>

                            <button onclick="document.getElementById('form-section').scrollIntoView({ behavior: 'smooth', block: 'start' })" class="inline-flex items-center justify-center gap-3 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-black py-4 px-10 rounded-full shadow-lg shadow-rose-500/30 transition-all hover:scale-105 hover:-translate-y-1 uppercase tracking-widest text-xs group border border-rose-400/50">
                                <span><?= e(t('job.apply.join_team')); ?></span>
                                <i class="fa-solid fa-arrow-down group-hover:translate-y-1 transition-transform"></i>
                            </button>
                        </div>

                        <div class="grid md:grid-cols-3 gap-5 md:gap-6 text-left mt-8">
                            <div class="p-6 bg-white/10 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl transition-all hover:-translate-y-1 hover:bg-white/15" data-aos="fade-up" data-aos-delay="300">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0 mb-4 text-white shadow-inner">
                                    <i class="fa-solid fa-award text-xl"></i>
                                </div>
                                <h4 class="font-black text-white mb-2 uppercase text-[13px] tracking-wide"><?= e(t('job.apply.benefit_1_title')); ?></h4>
                                <p class="text-slate-300 font-medium text-xs leading-relaxed"><?= e(t('job.apply.benefit_1_copy')); ?></p>
                            </div>

                            <div class="p-6 bg-white/10 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl transition-all hover:-translate-y-1 hover:bg-white/15" data-aos="fade-up" data-aos-delay="460">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0 mb-4 text-white shadow-inner">
                                    <i class="fa-solid fa-lightbulb text-xl"></i>
                                </div>
                                <h4 class="font-black text-white mb-2 uppercase text-[13px] tracking-wide"><?= e(t('job.apply.benefit_2_title')); ?></h4>
                                <p class="text-slate-300 font-medium text-xs leading-relaxed"><?= e(t('job.apply.benefit_2_copy')); ?></p>
                            </div>

                            <div class="p-6 bg-white/10 backdrop-blur-md rounded-2xl border border-white/10 shadow-xl transition-all hover:-translate-y-1 hover:bg-white/15" data-aos="fade-up" data-aos-delay="620">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0 mb-4 text-white shadow-inner">
                                    <i class="fa-solid fa-hands-helping text-xl"></i>
                                </div>
                                <h4 class="font-black text-white mb-2 uppercase text-[13px] tracking-wide"><?= e(t('job.apply.benefit_3_title')); ?></h4>
                                <p class="text-slate-300 font-medium text-xs leading-relaxed"><?= e(t('job.apply.benefit_3_copy')); ?></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Divider -->
        <div class="mb-12">
            <div class="w-20 h-1 bg-gradient-to-r from-rose-600 to-orange-600 mx-auto rounded-full"></div>
        </div>

        <div id="form-section" class="mb-12 text-center" data-aos="fade-down" data-aos-duration="650" data-aos-delay="320">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4"><?= e(t('job.apply.form_title')); ?> <span class="text-rose-600"><?= e(t('job.apply.form_highlight')); ?></span></h1>
            <p class="text-slate-500 font-medium"><?= e(t('job.apply.form_subtitle')); ?></p>
        </div>

        <form action="save_application.php" method="POST" enctype="multipart/form-data">
            <div class="grid lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-8 space-y-8">
                    
                    <div class="form-card animate-card p-8 md:p-10 shadow-xl shadow-slate-200/50" data-animate-card>
                        <h2 class="section-title" data-aos="fade-right" data-aos-delay="320"><?= e(t('job.apply.personal_info')); ?></h2>
                        
                        <div class="grid md:grid-cols-2 gap-5 mb-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2"><?= e(t('job.apply.full_name')); ?></label>
                                <input type="text" name="full_name" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2"><?= e(t('job.apply.birthdate')); ?></label>
                                <input type="date" name="dob" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                        </div>

                        <div class="space-y-5 mb-6">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2"><?= e(t('job.apply.phone')); ?></label>
                                <input type="tel" inputmode="numeric" pattern="[0-9]*" name="phone" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2"><?= e(t('job.apply.email')); ?></label>
                                <input type="email" name="email" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                        </div>

                        <div class="space-y-1.5 mb-4 border-t border-slate-100 pt-6">
                            <label class="text-xs font-black text-slate-400 uppercase ml-2"><?= e(t('job.apply.address')); ?></label>
                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <select id="province" name="province" class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold appearance-none cursor-pointer">
                                    <option value="" disabled selected><?= e(t('job.apply.choose_province')); ?></option>
                                    </select>
                                <select id="district" name="district" disabled class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold appearance-none cursor-not-allowed">
                                    <option value=""><?= e(t('job.apply.choose_unit_type')); ?></option>
                                </select>
                                <select id="ward" name="ward" disabled class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold appearance-none cursor-not-allowed">
                                    <option value=""><?= e(t('job.apply.choose_ward')); ?></option>
                                </select>
                            </div>
                            <input type="text" name="address_detail" placeholder="<?= e(t('job.apply.address_placeholder')); ?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                        </div>
                    </div>

                    <div class="form-card p-8 md:p-10 shadow-xl shadow-slate-200/50">
                        <div class="flex justify-between items-center mb-6" data-aos="fade-up" data-aos-delay="340">
                            <h2 class="section-title mb-0"><?= e(t('job.apply.experience')); ?></h2>
                            <button type="button" onclick="addExperience()" class="btn-add"><i class="fa-solid fa-plus mr-1"></i> <?= e(t('job.apply.add_experience')); ?></button>
                        </div>
                        <div id="experience-container">
                            <div class="repeater-box">
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <input type="text" name="exp_company[]" placeholder="<?= e(t('job.apply.company_placeholder')); ?>" class="px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm">
                                    <input type="text" name="exp_position[]" placeholder="<?= e(t('job.apply.position_placeholder')); ?>" class="px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm">
                                </div>
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div class="relative">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-black text-slate-400 uppercase"><?= e(t('job.apply.from_date')); ?></label>
                                        <input type="date" name="exp_start[]" class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm text-slate-600">
                                    </div>
                                    <div class="relative">
                                        <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-black text-slate-400 uppercase"><?= e(t('job.apply.to_date')); ?></label>
                                        <input type="date" name="exp_end[]" class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm text-slate-600">
                                    </div>
                                </div>
                                <textarea name="exp_detail[]" placeholder="<?= e(t('job.apply.work_desc_placeholder')); ?>" class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm h-24 resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                <div class="form-card animate-card p-8 md:p-10 shadow-xl shadow-slate-200/50" data-animate-card>
                    <h2 class="section-title" data-aos="fade-right" data-aos-delay="320"><?= e(t('job.apply.skills')); ?></h2>
                    
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2" data-aos="fade-up" data-aos-delay="380">
                            <h3 class="sub-title text-rose-600"><i class="fa-solid fa-laptop-code mr-2"></i><?= e(t('job.apply.professional_skills')); ?></h3>
                            <button type="button" onclick="addProSkill()" class="text-xs font-bold text-lime-600 bg-lime-100 px-3 py-1.5 rounded-lg hover:bg-lime-200 transition-colors"><i class="fa-solid fa-plus mr-1"></i> <?= e(t('job.apply.add_line')); ?></button>
                        </div>
                        <div id="pro-skill-container" class="space-y-3">
                            <div class="relative group">
                                <input type="text" name="skill_pro[]" placeholder="<?= e(t('job.apply.pro_skill_placeholder')); ?>" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 border border-transparent outline-none focus-lime font-bold text-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2" data-aos="fade-up" data-aos-delay="380">
                            <h3 class="sub-title text-emerald-600"><i class="fa-solid fa-users mr-2"></i><?= e(t('job.apply.other_skills')); ?></h3>
                            <button type="button" onclick="addOtherSkill()" class="text-xs font-bold text-lime-600 bg-lime-100 px-3 py-1.5 rounded-lg hover:bg-lime-200 transition-colors"><i class="fa-solid fa-plus mr-1"></i> <?= e(t('job.apply.add_line')); ?></button>
                        </div>
                        <div id="other-skill-container" class="space-y-3">
                            <div class="relative group">
                                <input type="text" name="skill_other[]" placeholder="<?= e(t('job.apply.other_skill_placeholder')); ?>" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 border border-transparent outline-none focus-lime font-bold text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="form-card animate-card p-8 md:p-10 shadow-xl shadow-slate-200/50" data-animate-card>
                        <h2 class="section-title" data-aos="fade-right" data-aos-delay="320"><?= e(t('job.apply.bio')); ?></h2>
                        <textarea name="bio" required placeholder="<?= e(t('job.apply.bio_placeholder')); ?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 outline-none focus-lime font-bold h-32 resize-none"></textarea>
                    </div>

                </div>

                <div class="lg:col-span-4 space-y-8">
                    <div class="form-card animate-card p-8 shadow-xl border-t-8 border-rose-600" data-animate-card>
                        <h3 class="text-xl font-black text-slate-800 mb-6 uppercase tracking-tight" data-aos="fade-right" data-aos-delay="320"><?= e(t('job.apply.job_details')); ?></h3>
                        
                        <div class="space-y-6">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-1"><?= e(t('job.apply.start_date')); ?></label>
                                <input type="date" name="start_date" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border-none outline-none focus-lime font-bold">
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-1"><?= e(t('job.apply.salary')); ?></label>
                                <div class="relative">
                                    <input type="text" id="salary_input" name="salary" required placeholder="15,000,000" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-none outline-none focus-lime font-bold pr-14">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 font-black text-slate-400 text-xs">VNĐ</span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-1"><?= e(t('job.apply.cv_upload')); ?></label>
                                <div class="group relative w-full h-32 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center hover:border-lime-400 transition-colors cursor-pointer bg-slate-50">
                                    <input type="file" name="cv_file" required class="absolute inset-0 opacity-0 cursor-pointer">
                                    <div class="text-center">
                                        <i class="fa-solid fa-file-pdf text-2xl text-slate-300 group-hover:text-rose-500 transition-colors"></i>
                                        <p class="text-[10px] font-black text-slate-400 mt-2"><?= e(t('job.apply.choose_file')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-10 bg-rose-600 hover:bg-rose-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-rose-600/20 transition-all hover:-translate-y-1 uppercase tracking-widest text-sm flex justify-center items-center gap-3">
                            <?= e(t('job.apply.submit')); ?>
                            <div class="w-2 h-2 rounded-full bg-lime-400"></div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 650,
                    once: true,
                    offset: 0,
                    easing: 'ease-out-cubic'
                });
            }
        });
    </script>
</main>
