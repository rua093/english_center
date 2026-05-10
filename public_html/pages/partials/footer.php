</main> 
<footer class="bg-blue-950 text-blue-200/70 pt-16 pb-7 relative overflow-hidden font-jakarta">
    
    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-blue-400 to-transparent opacity-60"></div>
    
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-full h-64 bg-blue-600/10 blur-[120px] pointer-events-none"></div>

    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12 mb-12">
            
            <div class="lg:col-span-4 flex flex-col pr-0 lg:pr-10">
                <a href="#top" class="inline-flex items-center gap-3 mb-5 group">
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform">
                        NM
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-black text-white tracking-tight uppercase leading-none">Nhuệ Minh</span>
                        <span class="text-blue-400 font-bold text-[10px] tracking-widest uppercase mt-1">Language Center</span>
                    </div>
                </a>
                <p class="text-sm leading-relaxed mb-6 font-medium">
                    Hệ thống đào tạo Anh ngữ chuẩn quốc tế, giúp học viên bứt phá giới hạn và tự tin kết nối toàn cầu.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30 hover:scale-105 transition-all duration-300">
                        <i class="fa-brands fa-facebook-f text-base"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-lg bg-gradient-to-tr from-pink-500 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-pink-500/30 hover:scale-105 transition-all duration-300">
                        <i class="fa-brands fa-instagram text-lg"></i>
                    </a>
                   <a href="#" class="w-10 h-10 rounded-lg bg-red-600 flex items-center justify-center text-white shadow-lg shadow-red-500/30 hover:scale-105 transition-all duration-300">
                        <i class="fa-brands fa-youtube text-base"></i>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h4 class="text-white font-black mb-6 text-xs uppercase tracking-widest relative">
                    Khám phá
                    <span class="absolute -bottom-2 left-0 w-6 h-0.5 bg-blue-400"></span>
                </h4>
                <ul class="space-y-3 text-xs font-bold mt-6">
                    <li><a href="<?= e(page_url('home') . '#gioi-thieu'); ?>" class="hover:text-blue-400 transition-all flex items-center gap-2 group"><i class="fa-solid fa-chevron-right text-[8px] opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>Về Nhuệ Minh</a></li>
                    <li><a href="<?= e(page_url('home') . '#khoa-hoc'); ?>" class="hover:text-blue-400 transition-all flex items-center gap-2 group"><i class="fa-solid fa-chevron-right text-[8px] opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>Các khóa học</a></li>
                    <li><a href="<?= e(page_url('home') . '#feed-back-student'); ?>" class="hover:text-blue-400 transition-all flex items-center gap-2 group"><i class="fa-solid fa-chevron-right text-[8px] opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>Thành tích</a></li>
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h4 class="text-white font-black mb-6 text-xs uppercase tracking-widest relative">
                    Nhanh truy cập
                    <span class="absolute -bottom-2 left-0 w-6 h-0.5 bg-blue-400"></span>
                </h4>
                <ul class="space-y-3 mt-6">
                    <li>
                        <a href="<?= e(page_url('job-apply')); ?>" class="hover:text-blue-400 transition-all flex items-center gap-2 group">
                            <i class="fa-solid fa-briefcase text-[10px] opacity-60"></i>
                            <span class="text-xs font-bold">Tuyển dụng</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(page_url('documents')); ?>" class="hover:text-blue-400 transition-all flex items-center gap-2 group">
                            <i class="fa-solid fa-file-lines text-[10px] opacity-60"></i>
                            <span class="text-xs font-bold">Tài liệu học tập</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(page_url('home') . '#ngoai-khoa'); ?>" class="hover:text-blue-400 transition-all flex items-center gap-2 group">
                            <i class="fa-solid fa-person-hiking text-[10px] opacity-60"></i>
                            <span class="text-xs font-bold">Hoạt động ngoại khoá</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h4 class="text-white font-black mb-6 text-xs uppercase tracking-tight relative">
                    Liên hệ ngay
                    <span class="absolute -bottom-2 left-0 w-6 h-0.5 bg-blue-400"></span>
                </h4>
                <div class="space-y-4 mt-6">
                    <div class="flex gap-3 items-start group">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500 flex items-center justify-center text-white flex-shrink-0 shadow-md shadow-emerald-500/30 group-hover:scale-105 group-hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-location-dot text-sm"></i>
                        </div>
                        <span class="text-[13px] font-bold leading-relaxed pt-1">Thôn Phú Thạnh - Phường Quảng Phú, TP. Đà Nẵng</span>
                    </div>
                    <div class="flex gap-3 items-center group">
                        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white flex-shrink-0 shadow-md shadow-blue-500/30 group-hover:scale-105 group-hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-phone-volume text-sm"></i>
                        </div>
                        <a href="tel:02873083333" class="text-lg font-black text-white hover:text-blue-400 transition-all">
                            028 7308 3333
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">
                &copy; <?= date('Y'); ?> <span class="text-blue-400">Nhuệ Minh Edu.</span>
            </p>

            <div class="flex gap-6 text-[10px] font-black uppercase tracking-widest text-slate-500">
                <a href="#" class="hover:text-white transition-colors">Bảo mật</a>
                <a href="#" class="hover:text-white transition-colors">Điều khoản</a>
            </div>
            
            <!-- <a href="#top" class="group flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-[10px] font-black text-blue-400 hover:bg-blue-400 hover:text-blue-950 transition-all duration-500">
                <span>LÊN ĐẦU TRANG</span>
                <i class="fa-solid fa-arrow-up transition-transform group-hover:-translate-y-1"></i>
            </a> -->
        </div>
    </div>
</footer>

<?php $mainScriptAsset = getVersion('js', 'main.js'); ?>
<?php if ($mainScriptAsset !== ''): ?>
    <script src="<?= e($mainScriptAsset); ?>"></script>
<?php endif; ?>
</body>
</html>
