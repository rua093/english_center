</main> 
<footer class="bg-[#050814] text-slate-400 pt-24 pb-10 relative overflow-hidden border-t border-white/5 font-jakarta">
    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#00d4ff]/40 to-transparent opacity-50"></div>
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#2e3192]/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-[#00d4ff]/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-12 mb-20">
            
            <div class="lg:col-span-4 flex flex-col pr-0 lg:pr-6">
                <a href="#top" class="inline-flex items-center gap-4 mb-6 group">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-[#00d4ff] to-[#008fb3] flex items-center justify-center text-[#2e3192] font-black text-2xl shadow-[0_10px_20px_rgba(0,212,255,0.3)] group-hover:rotate-12 group-hover:scale-105 transition-all duration-300">
                        NM
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-black text-white tracking-tight uppercase leading-none">Nhuệ Minh</span>
                        <span class="text-[#00d4ff] font-bold text-sm tracking-widest uppercase mt-1">Education</span>
                    </div>
                </a>
                <p class="text-slate-400 text-sm leading-relaxed mb-8 font-medium">
                    Chúng tôi không chỉ dạy tiếng Anh, chúng tôi xây dựng nền tảng tư duy toàn cầu, khơi nguồn tự tin và kiến tạo tương lai cho thế hệ trẻ Việt Nam.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-11 h-11 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#2e3192] hover:text-white hover:border-[#2e3192] hover:-translate-y-1 transition-all duration-300 shadow-sm group">
                        <i class="fa-brands fa-facebook-f text-lg group-hover:scale-110 transition-transform"></i>
                    </a>
                    <a href="#" class="w-11 h-11 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-gradient-to-tr hover:from-pink-500 hover:to-orange-500 hover:text-white hover:border-transparent hover:-translate-y-1 transition-all duration-300 shadow-sm group">
                        <i class="fa-brands fa-instagram text-xl group-hover:scale-110 transition-transform"></i>
                    </a>
                    <a href="#" class="w-11 h-11 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:bg-[#ff0000] hover:text-white hover:border-transparent hover:-translate-y-1 transition-all duration-300 shadow-sm group">
                        <i class="fa-brands fa-youtube text-lg group-hover:scale-110 transition-transform"></i>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2 md:pt-4">
                <h4 class="text-white font-black mb-6 text-sm uppercase tracking-widest relative inline-block">
                    Khám phá
                    <span class="absolute -bottom-2 left-0 w-1/2 h-1 bg-[#00d4ff] rounded-full"></span>
                </h4>
                <ul class="space-y-4 text-sm font-medium mt-8">
                    <li><a href="#" class="text-slate-400 hover:text-[#00d4ff] transition-all flex items-center gap-2 group"><i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00d4ff]"></i>Về Nhuệ Minh</a></li>
                    <li><a href="#" class="text-slate-400 hover:text-[#00d4ff] transition-all flex items-center gap-2 group"><i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00d4ff]"></i>Đội ngũ giáo viên</a></li>
                    <li><a href="#" class="text-slate-400 hover:text-[#00d4ff] transition-all flex items-center gap-2 group"><i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00d4ff]"></i>Các khóa học</a></li>
                    <li><a href="#" class="text-slate-400 hover:text-[#00d4ff] transition-all flex items-center gap-2 group"><i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#00d4ff]"></i>Thành tích học viên</a></li>
                </ul>
            </div>

            <div class="lg:col-span-3 md:pt-4">
                <h4 class="text-white font-black mb-6 text-sm uppercase tracking-widest relative inline-block">
                    Hệ thống
                    <span class="absolute -bottom-2 left-0 w-1/2 h-1 bg-[#00d4ff] rounded-full"></span>
                </h4>
                <ul class="space-y-4 text-sm font-medium mt-8">
                    <?php if (!is_logged_in()): ?>
                        <li>
                            <a href="<?= e(page_url('login')); ?>" class="group flex items-center gap-3 text-slate-400 hover:text-white transition-all">
                                <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center group-hover:bg-[#00d4ff] group-hover:text-[#2e3192] transition-colors"><i class="fa-solid fa-user-graduate text-xs"></i></div>
                                <span>Cổng học viên</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="#" class="group flex items-center gap-3 text-slate-400 hover:text-white transition-all">
                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center group-hover:bg-[#00d4ff] group-hover:text-[#2e3192] transition-colors"><i class="fa-regular fa-calendar-days text-xs"></i></div>
                            <span>Lịch khai giảng</span>
                        </a>
                    </li>
                    <?php if (can_access_page('dashboard-admin')): ?>
                        <li>
                            <a href="/admin" class="group flex items-center gap-3 text-[#00d4ff] hover:text-white transition-all">
                                <div class="w-8 h-8 rounded-full bg-[#00d4ff]/20 flex items-center justify-center group-hover:bg-[#00d4ff] group-hover:text-[#2e3192] transition-colors"><i class="fa-solid fa-shield-halved text-xs"></i></div>
                                <span class="font-bold">Khu vực quản trị</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="lg:col-span-3 md:pt-4">
                <h4 class="text-white font-black mb-6 text-sm uppercase tracking-widest relative inline-block">
                    Liên hệ
                    <span class="absolute -bottom-2 left-0 w-1/2 h-1 bg-[#00d4ff] rounded-full"></span>
                </h4>
                <div class="space-y-5 text-sm font-medium mt-8">
                    <div class="flex gap-4 items-start group cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-[#00d4ff] flex-shrink-0 group-hover:bg-[#00d4ff] group-hover:text-[#2e3192] transition-all shadow-sm">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <span class="leading-relaxed pt-1 text-slate-400 group-hover:text-white transition-colors">Thôn Phú Thạnh - Phường Quảng Phú, TP. Đà Nẵng</span>
                    </div>
                    <div class="flex gap-4 items-start group cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-[#00d4ff] flex-shrink-0 group-hover:bg-[#00d4ff] group-hover:text-[#2e3192] transition-all shadow-sm">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div class="flex flex-col pt-1">
                            <a href="tel:02873083333" class="text-slate-400 hover:text-[#00d4ff] transition-all text-lg font-bold">028 7308 3333</a>
                            <span class="text-xs text-slate-500">Miễn phí cước cuộc gọi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-6 relative">
            
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest text-center md:text-left">
                &copy; <?= date('Y'); ?> <span class="text-white">Nhuệ Minh Edu.</span> Crafted with <i class="fa-solid fa-heart text-red-500 mx-1"></i>
            </p>

            <div class="flex flex-wrap justify-center gap-x-8 gap-y-3 text-xs font-bold uppercase tracking-widest text-slate-500">
                <a href="#" class="hover:text-[#00d4ff] transition-colors">Bảo mật</a>
                <a href="#" class="hover:text-[#00d4ff] transition-colors">Điều khoản</a>
                <a href="#" class="hover:text-[#00d4ff] transition-colors">Sitemap</a>
            </div>
            
            <a href="#top" class="group flex items-center gap-3 px-5 py-2.5 bg-white/5 border border-white/10 rounded-full text-xs font-black tracking-widest hover:bg-[#00d4ff] hover:border-[#00d4ff] transition-all duration-300 shadow-sm absolute right-0 md:relative bottom-10 md:bottom-0">
                <span class="text-slate-400 group-hover:text-[#2e3192] transition-colors hidden sm:block">LÊN ĐẦU TRANG</span>
                <div class="w-8 h-8 rounded-full bg-[#2e3192] flex items-center justify-center text-white group-hover:bg-white group-hover:text-[#2e3192] transition-colors shadow-inner">
                    <i class="fa-solid fa-arrow-up"></i>
                </div>
            </a>
            
        </div>
    </div>
</footer>

<?php $mainScriptAsset = getVersion('js', 'main.js'); ?>
<?php if ($mainScriptAsset !== ''): ?>
    <script src="<?= e($mainScriptAsset); ?>"></script>
<?php endif; ?>
</body>
</html>