<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
$homeCourses = $homeCourses ?? [];

$homeFormatFeedbackDate = static function (?string $value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return 'Không rõ thời gian';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('d/m/Y H:i', $timestamp);
};
?>

<main class="font-jakarta relative overflow-hidden">

    <div id="dynamic-scroll-bg" class="fixed top-0 left-0 w-full h-[400vh] z-[-2] pointer-events-none will-change-transform" 
         style="background: linear-gradient(to bottom, 
            #ffffff 0%,      /* Trắng */
            #e0f2fe 25%,     /* Xanh biển nhạt (Sky 100) */
            #ecfccb 50%,     /* Xanh lá chuối non (Lime 100) */
            #fee2e2 75%,     /* Đỏ nhạt / Hồng phấn (Red 100) */
            #ffffff 100%     /* Về lại Trắng */
         );">
    </div>
    
    <div class="fixed inset-0 z-[-1] pointer-events-none opacity-[0.06]" 
         style="background-image: radial-gradient(#1e3a8a 2px, transparent 2px); background-size: 30px 30px;">
    </div>

    <section id="hero-video" class="relative w-full h-[80vh] min-h-[500px] md:min-h-[600px] flex items-center justify-center mb-16 sm:mb-20 md:mb-24">
        <div class="absolute inset-0 z-0 overflow-hidden bg-black">
            <video autoplay loop muted playsinline class="absolute top-1/2 left-1/2 min-w-full min-h-full w-auto h-auto -translate-x-1/2 -translate-y-1/2 object-cover">
                <source src="assets/videodemo/iilavideo.mp4" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/80 md:from-blue-950/70 via-blue-950/40 md:via-blue-950/20 to-transparent w-full"></div>
        </div>

       <div class="relative z-10 w-full max-w-[1450px] mx-auto px-4 sm:px-6 md:px-10 flex flex-col -mt-16 md:-mt-20">
            <div class="max-w-2xl" data-aos="fade-right">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight uppercase drop-shadow-lg">
                    GREATER YOU EVERYDAY <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-300 to-white">TRƯỞNG THÀNH HƠN</span> MỖI NGÀY
                </h1>
                <p class="mt-3 sm:mt-4 text-sm sm:text-base md:text-lg text-sky-100 font-medium max-w-lg drop-shadow-md">
                    Đồng hành cùng mỗi học viên để khơi dậy tiềm năng và đam mê trên hành trình học tập trọn đời.
                </p>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 translate-y-1/2 z-30 flex flex-col items-center px-4 sm:px-6">
            <div class="w-full max-w-[1180px] bg-gradient-to-br from-red-200 via-rose-200 to-lime-200 rounded-2xl sm:rounded-3xl shadow-[0_18px_60px_rgba(0,0,0,0.18)] flex flex-col overflow-hidden border border-lime-400/35 ring-1 ring-white/10">
                <div class="h-1 w-full bg-gradient-to-r from-amber-400 via-cyan-300 to-sky-400"></div>
                <div class="grid gap-0 lg:grid-cols-[1.35fr_1fr] items-stretch">
                    <div class="p-4 sm:p-6 md:p-8 lg:p-10 lg:border-r border-lime-200/80 relative overflow-hidden flex items-center">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.58),transparent_38%)]"></div>
                        <div class="relative z-10 max-w-3xl">
                            <p class="text-slate-800 font-semibold text-xs sm:text-sm md:text-base leading-relaxed max-w-2xl">
                                Tìm các khóa học phù hợp với bạn và giúp con đường học vấn của bạn thành công
                            </p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6 md:p-8 lg:p-10 bg-white/72 backdrop-blur-sm flex items-center relative overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute -top-8 right-10 h-16 w-16 sm:h-20 sm:w-20 rounded-none border border-lime-300/70 bg-white/50 rotate-12"></div>
                            <div class="absolute bottom-8 right-16 h-10 w-10 sm:h-12 sm:w-12 rounded-none bg-lime-400/35 rotate-45"></div>
                            <div class="absolute top-1/2 left-6 h-12 w-12 sm:h-14 sm:w-14 rounded-none border border-lime-300/60 bg-lime-100/50 -rotate-12"></div>
                        </div>
                        <div class="relative z-10 w-full grid gap-3 sm:gap-4 sm:grid-cols-2">
                            <div class="relative">
                                <select class="w-full bg-white text-slate-800 font-bold rounded-xl sm:rounded-2xl px-4 sm:px-5 py-2.5 sm:py-3 outline-none appearance-none cursor-pointer text-xs sm:text-sm shadow-inner border border-lime-200/90">
                                    <option value="" disabled selected>Độ tuổi</option>
                                    <option>Mầm non</option>
                                    <option>Tiểu học</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                            <div class="relative">
                                <select class="w-full bg-white text-slate-800 font-bold rounded-xl sm:rounded-2xl px-4 sm:px-5 py-2.5 sm:py-3 outline-none appearance-none cursor-pointer text-xs sm:text-sm shadow-inner border border-lime-200/90">
                                    <option value="" disabled selected>Chương trình học</option>
                                    <option>IELTS</option>
                                    <option>Giao tiếp</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="<?= e(page_url('register-consultation')); ?>" class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-black uppercase text-[10px] sm:text-xs px-6 sm:px-8 py-2.5 rounded-full shadow-lg border-2 border-white transition-transform hover:-translate-y-1 flex items-center gap-2">
                    KIỂM TRA TRÌNH ĐỘ MIỄN PHÍ <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                </a>
            </div>
        </div>
    </section>

    <section id="trang-chu" class="relative bg-transparent pt-12 lg:pt-16 lg:pb-8 overflow-hidden border-b border-blue-100/50">
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute w-[300px] md:w-[600px] h-[300px] md:h-[600px] bg-blue-400/20 blur-[80px] md:blur-[120px] rounded-full -top-20 md:-top-40 -left-20 md:-left-40"></div>
            <div class="absolute w-[250px] md:w-[500px] h-[250px] md:h-[500px] bg-cyan-400/20 blur-[80px] md:blur-[120px] rounded-full bottom-[-80px] md:bottom-[-150px] right-[-80px] md:right-[-150px]"></div>
        </div>

        <div class="relative z-10 max-w-[1450px] mx-auto px-4 sm:px-6 flex flex-col lg:flex-row gap-8 lg:gap-14 items-center lg:items-stretch">
            <div class="hidden lg:flex lg:w-5/12 relative items-center justify-center lg:-mt-20" data-aos="fade-right" data-aos-duration="1200">
                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 w-[90%] h-[80%] bg-gradient-to-t from-blue-300/40 to-transparent rounded-[3rem] blur-[60px] -z-10"></div>
                <img src="assets/images/student_girl.png" alt="Học sinh tiêu biểu" class="w-full max-w-[550px] object-contain relative z-10 drop-shadow-[0_20px_40px_rgba(30,58,138,0.25)]">
                
                <div class="absolute top-1/4 -left-4 bg-white/95 backdrop-blur-md px-4 sm:px-5 py-3 sm:py-4 rounded-2xl shadow-xl border border-blue-50 z-20 animate-bounce" style="animation-duration: 2s;">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-yellow-100 text-yellow-500 flex items-center justify-center text-lg sm:text-xl shadow-inner"><i class="fa-solid fa-star"></i></div>
                        <div>
                            <h4 class="text-xs sm:text-sm font-black text-blue-950 uppercase">Chất lượng</h4>
                            <p class="text-[9px] sm:text-[11px] font-bold text-slate-500">Chuẩn quốc tế</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-7/12 flex flex-col gap-4 sm:gap-5 md:gap-6 pb-12 lg:py-6 z-20">
                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5 md:gap-6 h-full min-h-[140px] sm:min-h-[180px]">
                    <div class="group relative rounded-[1.5rem] sm:rounded-[2rem] p-6 sm:p-8 md:p-10 bg-gradient-to-br from-sky-400 to-blue-500 text-white shadow-[0_15px_30px_rgba(14,165,233,0.3)] overflow-hidden flex flex-col justify-center" data-aos="fade-up">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <i class="fa-solid fa-rocket absolute -right-2 sm:-right-4 -bottom-2 sm:-bottom-4 text-[6rem] sm:text-[8rem] opacity-20 group-hover:scale-110 transition-transform duration-700"></i>
                        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-4xl xl:text-5xl font-black leading-[1.15] text-blue-950 relative z-10 drop-shadow-sm text-left">Khát Vọng<br>Là Khởi Đầu</h1>
                    </div>
                    <div class="group relative rounded-[1.5rem] sm:rounded-[2rem] p-6 sm:p-8 md:p-10 bg-gradient-to-bl from-blue-900 to-blue-800 text-white shadow-[0_15px_30px_rgba(30,58,138,0.3)] overflow-hidden flex flex-col justify-center" data-aos="fade-up" data-aos-delay="100">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <i class="fa-solid fa-trophy absolute -right-2 sm:-right-4 -bottom-2 sm:-bottom-4 text-[6rem] sm:text-[8rem] opacity-10 group-hover:scale-110 transition-transform duration-700"></i>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-4xl xl:text-5xl font-black leading-[1.15] relative z-10 drop-shadow-sm text-left text-blue-50">Của Mọi<br>Thành Tựu</h2>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5 md:gap-6">
                    <a href="#thi-thu" class="group relative rounded-[1.5rem] sm:rounded-[2rem] p-6 sm:p-8 md:p-10 bg-gradient-to-br from-rose-600 to-red-500 text-white shadow-[0_15px_30px_rgba(225,29,72,0.3)] overflow-hidden transition-all hover:-translate-y-2 h-40 sm:h-48 md:h-56 flex flex-col justify-end" data-aos="fade-up" data-aos-delay="200">
                        <div class="absolute top-4 sm:top-6 left-4 sm:left-6 bg-white text-red-600 text-[9px] sm:text-[10px] lg:text-xs font-black px-3 sm:px-4 py-1 sm:py-1.5 rounded-full uppercase tracking-widest shadow-md">NEW</div>
                        <i class="fa-solid fa-chalkboard-user absolute -right-4 sm:-right-6 -top-4 sm:-top-6 text-[8rem] sm:text-[10rem] opacity-15 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-700"></i>
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-black relative z-10 leading-[1.2] w-11/12 drop-shadow-md">Thi Thử Nhận Kết Quả Ngay</h3>
                    </a>
                    <a href="#tai-lieu" class="group relative rounded-[1.5rem] sm:rounded-[2rem] p-6 sm:p-8 md:p-10 bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-[0_15px_30px_rgba(37,99,235,0.3)] overflow-hidden transition-all hover:-translate-y-2 h-40 sm:h-48 md:h-56 flex flex-col justify-end" data-aos="fade-up" data-aos-delay="300">
                        <i class="fa-solid fa-book-open absolute -right-4 sm:-right-6 -bottom-4 sm:-bottom-6 text-[8rem] sm:text-[10rem] opacity-15 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-700"></i>
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-black relative z-10 leading-[1.2] w-11/12 drop-shadow-md">Tài Liệu Học Tiếng Anh</h3>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 md:gap-5" data-aos="fade-up" data-aos-delay="400">
                    <a href="#ovi" class="group relative rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-blue-100 shadow-[0_10px_20px_rgba(30,58,138,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-blue-400 flex flex-col items-center text-center">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2 sm:mb-3 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-laptop-code text-lg sm:text-xl"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-xs sm:text-sm leading-tight">Cổng OVI</h4><span class="text-[9px] sm:text-[10px] font-semibold text-slate-500 mt-1 block">Hệ thống học tập</span>
                    </a>
                    <a href="#he" class="group relative rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-teal-100 shadow-[0_10px_20px_rgba(20,184,166,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-teal-400 flex flex-col items-center text-center">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-2 sm:mb-3 group-hover:bg-teal-600 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-sun text-lg sm:text-xl animate-[spin_6s_linear_infinite]"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-xs sm:text-sm leading-tight">Tiếng Anh Hè</h4><span class="text-[9px] sm:text-[10px] font-semibold text-slate-500 mt-1 block">Chương trình 2026</span>
                    </a>
                    <a href="#doanh-nghiep" class="group relative rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-amber-100 shadow-[0_10px_20px_rgba(245,158,11,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-amber-400 flex flex-col items-center text-center">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-2 sm:mb-3 group-hover:bg-amber-500 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-briefcase text-lg sm:text-xl"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-xs sm:text-sm leading-tight">Doanh Nghiệp</h4><span class="text-[9px] sm:text-[10px] font-semibold text-slate-500 mt-1 block">Giải pháp đào tạo</span>
                    </a>
                    <a href="#thanh-tich" class="group relative rounded-2xl p-4 sm:p-5 bg-white/90 backdrop-blur-xl border border-emerald-100 shadow-[0_10px_20px_rgba(16,185,129,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-emerald-400 flex flex-col items-center text-center">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2 sm:mb-3 group-hover:bg-emerald-500 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-medal text-lg sm:text-xl"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-xs sm:text-sm leading-tight">Thành Tích</h4><span class="text-[9px] sm:text-[10px] font-semibold text-slate-500 mt-1 block">Học viên xuất sắc</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/social_contact.php'; ?>

    <section id="gioi-thieu" class="relative py-16 sm:py-20 md:py-28 overflow-hidden bg-transparent">
        <div class="absolute top-[-5%] right-[-5%] w-[250px] sm:w-[400px] lg:w-[500px] h-[250px] sm:h-[400px] lg:h-[500px] bg-gradient-to-br from-blue-300/40 to-sky-200/40 rounded-full blur-2xl sm:blur-3xl mix-blend-multiply pointer-events-none"></div>
        <div class="absolute bottom-[-5%] left-[-5%] w-[200px] sm:w-[300px] lg:w-[400px] h-[200px] sm:h-[300px] lg:h-[400px] bg-gradient-to-tr from-cyan-200/40 to-blue-200/40 rounded-full blur-2xl sm:blur-3xl mix-blend-multiply pointer-events-none"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-10 sm:gap-16 md:gap-24 items-center">
            <div class="relative px-4 sm:px-0" data-aos="fade-right">
                <div class="absolute -bottom-4 -right-4 sm:-bottom-6 sm:-right-6 w-full h-full rounded-[2rem] sm:rounded-[2.5rem] bg-gradient-to-br from-blue-600/10 to-cyan-500/10 border border-blue-900/5"></div>
                <div class="relative rounded-[2rem] sm:rounded-[2.5rem] overflow-hidden shadow-[0_20px_50px_rgba(30,58,138,0.15)] group border-[4px] sm:border-[6px] border-white/80">
                    <img src="/assets/images/center.jpg" alt="Vị trí trung tâm" class="w-full h-[300px] sm:h-[400px] lg:h-[500px] object-cover transform group-hover:scale-105 transition duration-700 ease-in-out">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-950/60 via-blue-950/20 to-transparent opacity-80"></div>
                </div>
                <div class="absolute -bottom-6 right-2 sm:-bottom-8 sm:right-4 md:-right-4 bg-white/95 backdrop-blur-md px-4 sm:px-6 py-3 sm:py-4 rounded-2xl shadow-[0_15px_40px_rgba(30,58,138,0.15)] border border-blue-50 hover:-translate-y-1 transition-transform cursor-default z-20">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="relative flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-30"></span>
                            <div class="relative w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shadow-md">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-[9px] sm:text-[10px] uppercase tracking-widest text-blue-600 font-black mb-0.5 flex items-center gap-1.5">Vị trí trung tâm</p>
                            <h4 class="text-xs sm:text-sm font-black text-blue-950">Quảng Phú – Đà Nẵng</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5 sm:space-y-7 mt-8 lg:mt-0" data-aos="fade-left">
                <div class="inline-flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-2 sm:py-3 rounded-full bg-gradient-to-r from-white to-blue-50 border border-blue-200 shadow-md shadow-blue-100/60 ring-1 ring-white/70">
                    <span class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-blue-500 animate-pulse shadow-[0_0_0_6px_rgba(59,130,246,0.12)]"></span>
                    <span class="text-blue-900 text-xs sm:text-sm md:text-base font-black uppercase tracking-[0.22em]">Về Nhuệ Minh Edu</span>
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold leading-[1.15] text-blue-950">Nâng tầm ngoại ngữ,<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">Khơi nguồn tự tin</span></h2>
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed font-medium">Trung tâm ngoại ngữ hiện đại với không gian học tập truyền cảm hứng, cam kết mang lại giá trị thực tế, giúp học viên phát triển toàn diện 4 kỹ năng và sẵn sàng hội nhập.</p>

                <div class="grid sm:grid-cols-2 gap-4 sm:gap-5 mt-4 sm:mt-6">
                    <div class="bg-white/80 backdrop-blur-sm p-5 sm:p-6 rounded-2xl shadow-sm border border-blue-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center mb-3 sm:mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h4 class="font-extrabold text-blue-950 mb-1 text-sm sm:text-base">Vị trí thuận lợi</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">Dễ dàng di chuyển, gần khu dân cư & trường học.</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-5 sm:p-6 rounded-2xl shadow-sm border border-teal-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 text-white flex items-center justify-center mb-3 sm:mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-shield-halved"></i></div>
                        <h4 class="font-extrabold text-teal-950 mb-1 text-sm sm:text-base">Môi trường an toàn</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">Không gian học tập hiện đại, thân thiện và an ninh.</p>
                    </div>
                </div>
                <div class="pt-2 sm:pt-4">
                    <a href="#lien-he" class="inline-flex items-center justify-center gap-2 sm:gap-3 px-6 sm:px-8 py-3 sm:py-4 rounded-full bg-gradient-to-r from-blue-600 to-sky-500 text-white font-bold text-sm sm:text-base shadow-[0_10px_20px_rgba(37,99,235,0.3)] transition-all hover:-translate-y-1 hover:shadow-[0_15px_25px_rgba(37,99,235,0.4)]">
                        Khám phá ngay <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="su-menh" class="pt-12 pb-8 md:pt-20 md:pb-10 relative overflow-hidden bg-transparent z-10">
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden hidden sm:block">
            <div class="absolute top-6 left-4 sm:top-10 sm:left-8 md:top-12 md:left-12 flex flex-col gap-4 text-slate-300 rotate-[-8deg]">
                <i class="fa-solid fa-paper-plane text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-earth-americas text-5xl md:text-6xl ml-6 opacity-55"></i>
                <i class="fa-solid fa-book-open text-4xl md:text-5xl ml-10 opacity-55"></i>
            </div>
            <div class="absolute top-6 right-4 sm:top-10 sm:right-8 md:top-12 md:right-12 flex flex-col gap-4 items-end text-slate-300 rotate-[10deg]">
                <i class="fa-solid fa-graduation-cap text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-lightbulb text-5xl md:text-6xl mr-6 opacity-55"></i>
                <i class="fa-solid fa-rocket text-4xl md:text-5xl mr-10 opacity-55"></i>
            </div>
            <div class="absolute bottom-6 left-4 sm:bottom-10 sm:left-8 md:bottom-12 md:left-12 flex flex-col gap-4 text-slate-300 rotate-[8deg]">
                <i class="fa-solid fa-comments text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-pen-nib text-5xl md:text-6xl ml-6 opacity-55"></i>
                <i class="fa-solid fa-book text-4xl md:text-5xl ml-10 opacity-55"></i>
            </div>
            <div class="absolute bottom-6 right-4 sm:bottom-10 sm:right-8 md:bottom-12 md:right-12 flex flex-col gap-4 items-end text-slate-300 rotate-[-10deg]">
                <i class="fa-solid fa-users text-4xl md:text-5xl opacity-55"></i>
                <i class="fa-solid fa-star text-5xl md:text-6xl mr-6 opacity-55"></i>
                <i class="fa-solid fa-compass text-4xl md:text-5xl mr-10 opacity-55"></i>
            </div>
            <i class="absolute top-[18%] left-[18%] fa-solid fa-laptop-code text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[-14deg]"></i>
            <i class="absolute top-[28%] right-[22%] fa-solid fa-book text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[12deg]"></i>
            <i class="absolute top-[42%] left-[9%] fa-solid fa-comments text-slate-300 text-4xl md:text-6xl opacity-[0.40] rotate-[-6deg]"></i>
            <i class="absolute top-[46%] right-[10%] fa-solid fa-globe text-slate-300 text-4xl md:text-6xl opacity-[0.40] rotate-[8deg]"></i>
            <i class="absolute top-[56%] left-[26%] fa-solid fa-lightbulb text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[-18deg]"></i>
            <i class="absolute top-[60%] right-[28%] fa-solid fa-paper-plane text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[16deg]"></i>
            <i class="absolute bottom-[28%] left-[14%] fa-solid fa-pen-nib text-slate-300 text-3xl md:text-5xl opacity-[0.40] rotate-[10deg]"></i>
            <i class="absolute bottom-[24%] right-[16%] fa-solid fa-graduation-cap text-slate-300 text-4xl md:text-6xl opacity-[0.40] rotate-[-12deg]"></i>
            <i class="absolute bottom-[40%] left-[42%] fa-solid fa-star text-slate-300 text-2xl md:text-4xl opacity-[0.40] rotate-[20deg]"></i>
            <i class="absolute top-[34%] left-[46%] fa-solid fa-earth-americas text-slate-300 text-3xl md:text-5xl opacity-[0.36] rotate-[-10deg]"></i>
        </div>
        
        <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
            <div class="text-center mb-8 md:mb-10" data-aos="fade-up">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-tight text-[#2e3192]">Sứ Mệnh <span class="text-red-600">Toàn Cầu</span></h2>
                <p class="mt-3 sm:mt-4 text-slate-600 font-medium max-w-2xl mx-auto text-base sm:text-lg">Kiến tạo thế hệ công dân làm chủ tương lai thông qua ngôn ngữ và kỹ năng toàn diện.</p>
            </div>
            
            <div class="relative w-full max-w-[1260px] mx-auto min-h-[400px] sm:min-h-[620px] md:min-h-[940px] flex items-center justify-center orbit-wrapper mt-6 sm:mt-10 md:mt-2" style="transform: scale(0.95);">
                <div class="absolute w-[320px] h-[320px] sm:w-[430px] sm:h-[430px] md:w-[770px] md:h-[770px] rounded-full border-2 border-dashed border-blue-400/50 orbit-spin z-10">
                    
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-amber-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-amber-50 text-amber-500 shadow-inner">💡</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Sáng Tạo</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Xây dựng không gian học ngoại ngữ thân thiện, hiệu quả và đầy cảm hứng.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute top-1/2 right-0 translate-x-1/2 -translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-blue-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-blue-50 text-blue-500 shadow-inner">🗣️</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Tự Tin</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Làm chủ tiếng Anh từ những câu đơn giản đến hội thoại thực tế đời sống.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-emerald-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-emerald-50 text-emerald-500 shadow-inner">🎯</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Toàn Diện</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Đào tạo bài bản 4 kỹ năng Nghe – Nói – Đọc – Viết cho mọi lứa tuổi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute top-1/2 left-0 -translate-x-1/2 -translate-y-1/2 z-50">
                        <div class="orbit-reverse-spin group cursor-pointer">
                            <div class="flex items-center gap-2 sm:gap-4 bg-white/95 backdrop-blur-md p-2 sm:p-3 pr-4 sm:pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-purple-300">
                                <div class="w-10 h-10 sm:w-14 sm:h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-xl sm:text-3xl shrink-0 bg-purple-50 text-purple-500 shadow-inner">🤝</div>
                                <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[100px] sm:max-w-[140px] max-h-[28px] sm:max-h-[36px] group-hover:max-w-[280px] sm:group-hover:max-w-[340px] group-hover:max-h-[140px] sm:group-hover:max-h-[170px]">
                                    <h4 class="font-black text-[#2e3192] text-xs sm:text-sm md:text-lg whitespace-nowrap">Cam Kết</h4>
                                    <p class="text-[10px] sm:text-xs md:text-sm text-slate-600 font-medium mt-1 w-[200px] sm:w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                        Theo sát lộ trình, khơi dậy niềm yêu thích với phương châm "Dám nói".
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-20 w-44 h-44 sm:w-64 sm:h-64 md:w-[450px] md:h-[450px] rounded-full border-[6px] sm:border-[10px] md:border-[14px] border-white shadow-[0_20px_60px_rgba(30,58,138,0.2)] overflow-hidden bg-white flex items-center justify-center group" data-aos="zoom-in">
                    <img src="assets/images/mission.jpg" alt="Trung tâm" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-blue-900/10 group-hover:bg-transparent transition-colors"></div>
                </div>

            </div>
        </div>
    </section>                            
								
    <section id="khoa-hoc" class="pt-6 pb-14 md:pt-8 md:pb-18 relative overflow-hidden bg-transparent">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
            <div class="mb-10 sm:mb-14 text-center" data-aos="fade-up">
                <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-[#2e3192] uppercase tracking-tight">
                    KHOÁ HỌC DÀNH CHO <span class="inline-block mt-2 md:mt-0 rounded-full bg-red-600 px-4 sm:px-6 py-1.5 sm:py-2 text-white shadow-lg transform -rotate-2">MỌI MỤC TIÊU</span>
                </h2>
                <p class="mt-4 sm:mt-6 text-sm sm:text-base md:text-lg text-slate-700 max-w-3xl mx-auto font-semibold">
                    Dễ dàng lựa chọn khóa học tiếng Anh phù hợp cho riêng mình với chương trình học đa dạng, được thiết kế phù hợp với nhu cầu và trình độ thực tế.
                </p>
            </div>

            <div class="rounded-[2rem] sm:rounded-[3rem] bg-white/40 backdrop-blur-md border border-white p-4 sm:p-6 md:p-8 lg:p-10 shadow-[0_15px_40px_rgba(30,58,138,0.06)] overflow-hidden" data-aos="zoom-in">
                <?php if (empty($homeCourses)): ?>
                    <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-14 text-center text-slate-500 font-medium">
                        Hiện chưa có khóa học nào trong hệ thống.
                    </div>
                <?php else: ?>
                    <div class="grid gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4 mobile-swipe-track">
                        <?php foreach ($homeCourses as $course): ?>
                            <?php
                            $courseTitle = (string) ($course['title'] ?? '');
                            $courseSlug = (string) ($course['slug'] ?? '');
                            $courseImage = (string) ($course['image'] ?? '');
                            $courseDesc = trim((string) ($course['short_desc'] ?? ''));
                            $courseLink = page_url('courses', ['course' => $courseSlug]);
                            ?>
                            <article class="mobile-swipe-card group flex flex-col overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] bg-white/90 shadow-lg border border-rose-100/70 transition-all duration-300 hover:-translate-y-3 hover:shadow-xl hover:shadow-rose-100/50">
                                <div class="relative h-48 sm:h-56 overflow-hidden bg-gradient-to-br from-rose-50 via-white to-emerald-50">
                                    <?php if ($courseImage !== ''): ?>
                                        <img src="<?= e($courseImage); ?>" alt="<?= e($courseTitle); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-red-500/35 via-rose-400/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-rose-50 via-white to-emerald-50">
                                            <div class="text-center text-red-500">
                                                <div class="mx-auto mb-3 flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-white/90 shadow-lg ring-1 ring-rose-100">
                                                    <i class="fa-solid fa-book-open text-2xl sm:text-3xl text-red-400"></i>
                                                </div>
                                                <div class="text-[10px] sm:text-xs font-black uppercase tracking-[0.22em] text-slate-700">Ảnh khóa học</div>
                                            </div>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-t from-red-500/12 via-rose-400/8 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <?php endif; ?>
                                    <div class="absolute top-3 sm:top-4 right-3 sm:right-4 bg-gradient-to-r from-red-500 to-emerald-500 text-white rounded-xl sm:rounded-2xl px-2 sm:px-3 py-1.5 sm:py-2 text-center shadow-md backdrop-blur-sm ring-1 ring-white/70">
                                        <span class="block text-[8px] sm:text-[10px] uppercase font-bold opacity-90">Buổi học</span>
                                        <span class="block text-xl sm:text-2xl font-black leading-none"><?= (int) ($course['total_sessions'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col p-5 sm:p-6">
                                    <div class="inline-flex w-fit rounded-full bg-gradient-to-r from-emerald-50 to-rose-50 px-2.5 py-1 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700 ring-1 ring-emerald-100/80">
                                        <?= e((string) ($course['level'] ?? 'Khóa học')); ?>
                                    </div>
                                    <h3 class="mt-2 sm:mt-3 text-lg sm:text-xl font-extrabold uppercase leading-tight text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-rose-500 to-emerald-500 transition-colors"><?= e($courseTitle); ?></h3>
                                    <p class="mt-2 sm:mt-3 text-xs sm:text-sm font-semibold text-slate-700 flex-1 leading-relaxed">
                                        <?= e($courseDesc !== '' ? $courseDesc : 'Chương trình học được xây dựng theo lộ trình rõ ràng, phù hợp cho từng học viên.'); ?>
                                    </p>
                                    <div class="mt-4 sm:mt-5 pt-3 sm:pt-4 border-t-2 border-slate-100 flex flex-col gap-3 sm:gap-4">
                                        <div class="flex items-end justify-between gap-3 sm:gap-4">
                                            <div>
                                                <span class="block text-[10px] sm:text-xs font-bold text-slate-700 uppercase tracking-wide">Học phí từ</span>
                                                <span class="text-lg sm:text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-emerald-500"><?= e((string) ($course['price'] ?? '0đ')); ?></span>
                                            </div>
                                            <div class="text-right text-[10px] sm:text-xs font-semibold text-slate-700">
                                                <div><?= (int) ($course['roadmap_count'] ?? 0); ?> lộ trình</div>
                                                <div><?= (int) ($course['class_count'] ?? 0); ?> lớp học</div>
                                            </div>
                                        </div>
                                        <a href="<?= e($courseLink); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-red-500 via-rose-500 to-emerald-500 px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:from-red-600 hover:to-emerald-600 hover:shadow-lg">
                                            Xem chi tiết <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="ngoai-khoa" class="py-12 sm:py-16 bg-transparent relative overflow-hidden">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="mb-10 sm:mb-12 text-center md:text-left" data-aos="fade-up">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-800 mb-2">
                    Có những mùa hè trôi qua, có những mùa hè con mang theo mãi mãi...
                </h2>
                <p class="text-slate-600 font-medium text-sm md:text-base">Khám phá các hoạt động ngoại khóa nổi bật trong tháng này.</p>
            </div>

            <?php $activities = $homeActivities ?? []; ?>
            <div class="overflow-hidden pb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="grid gap-5 sm:gap-6 lg:gap-8 sm:grid-cols-2 lg:grid-cols-4 mobile-swipe-track">
                    <?php if (empty($activities)): ?>
                        <div class="sm:col-span-2 lg:col-span-4 rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-14 text-center text-slate-500 font-medium">
                            Hiện chưa có hoạt động ngoại khoá nào trong hệ thống.
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <?php
                            $activityTitle = (string) ($act['activity_name'] ?? '');
                            $activityDesc = trim((string) ($act['description'] ?? ''));
                            $activityImage = trim((string) ($act['image_thumbnail'] ?? ''));
                            $activityLink = page_url('activities-home-detail', ['id' => (int) ($act['id'] ?? 0)]);
                            ?>
                            <a href="<?= e($activityLink); ?>" class="mobile-swipe-card group bg-white/80 backdrop-blur-md hover:bg-white rounded-2xl sm:rounded-3xl p-3 border border-white shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 cursor-pointer flex flex-col">
                                <div class="relative w-full aspect-[4/3] rounded-xl sm:rounded-2xl overflow-hidden mb-3 sm:mb-4 bg-slate-100">
                                    <?php if ($activityImage !== ''): ?>
                                        <img src="<?= e($activityImage); ?>" alt="<?= e($activityTitle); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-sky-100 via-white to-lime-100 text-slate-400">
                                            <i class="fa-solid fa-rocket text-2xl sm:text-3xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="px-2 pb-2 sm:pb-3 text-center flex-1 flex flex-col justify-start">
                                    <h3 class="text-[#0d3b66] font-bold text-base sm:text-lg leading-tight mb-1.5 sm:mb-2 group-hover:text-blue-600 transition-colors"><?= e($activityTitle); ?></h3>
                                    <p class="text-slate-500 text-xs sm:text-sm font-medium leading-snug px-1 line-clamp-2">
                                        <?= e($activityDesc !== '' ? $activityDesc : 'Khám phá hoạt động ngoại khoá hấp dẫn dành cho học viên.'); ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 sm:mt-8 flex justify-center" data-aos="fade-up">
                <a href="<?= e(page_url('activities-home')); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-6 py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                    Xem thêm <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
                </a>
            </div>
        </div>
    </section>
	
    <section id="giao-vien" class="py-12 sm:py-14 md:py-20 relative overflow-hidden bg-transparent z-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="mb-10 sm:mb-16 flex flex-col lg:flex-row gap-6 sm:gap-8 items-start lg:items-center justify-between" data-aos="fade-up">
            
            <div class="lg:w-1/2 max-w-2xl">
                <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-blue-800 leading-[1.18] md:leading-[1.12] tracking-tight max-w-xl">
                    Hơn 3.100 Giáo viên và trợ giảng <br>
                    <span class="text-blue-600">Chuẩn quốc tế</span>
                </h2>
            </div>
            
            <div class="lg:w-1/2 flex items-start sm:items-center gap-4 sm:gap-6">
                <p class="text-slate-600 font-medium text-xs sm:text-sm leading-relaxed border-l-2 border-slate-300 pl-3 sm:pl-4 md:pl-6 text-left">
                    100% giáo viên nước ngoài được đảm bảo bởi International House - tổ chức uy tín hàng đầu thế giới về chuẩn đào tạo giáo viên nghiêm ngặt (như CELTA, DELTA)
                </p>
                <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 shrink-0 bg-[#2e3192] rounded-full flex items-center justify-center text-white font-bold text-lg sm:text-xl md:text-2xl shadow-md">
                    ih
                </div>
            </div>
        </div>

        <div class="w-full" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper teacherSwiper pb-12 sm:pb-16">
                <div class="swiper-wrapper">
                    <?php
                    $teachers = array_slice($homeTeachers ?? [], 0, 5);
                    if (empty($teachers)):
                    ?>
                    <div class="swiper-slide h-auto">
                        <div class="rounded-2xl sm:rounded-3xl border border-dashed border-slate-200 bg-white/80 p-6 sm:p-8 text-center text-slate-500 font-medium text-sm sm:text-base">
                            Hiện chưa có giáo viên nào trong hệ thống.
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                    <?php
                    $teacherName = (string) ($teacher['full_name'] ?? '');
                    $teacherRole = 'Giáo viên';
                    $teacherAvatar = trim((string) ($teacher['avatar'] ?? ''));
                    if ($teacherAvatar !== '' && function_exists('normalize_public_file_url')) {
                        $teacherAvatar = normalize_public_file_url($teacherAvatar);
                    }
                    if ($teacherAvatar === '') {
                        $teacherAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($teacherName !== '' ? $teacherName : 'Teacher') . '&background=2e3192&color=fff&size=600&bold=true';
                    }
                    ?>
                    <div class="swiper-slide h-auto">
                        <article class="flex flex-col gap-3 sm:gap-4 group cursor-pointer">
                            <div class="relative w-full aspect-[4/5] rounded-2xl sm:rounded-3xl overflow-hidden bg-slate-200 shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
                                <img src="<?= e($teacherAvatar) ?>" alt="<?= e($teacherName) ?>" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                                <div class="absolute inset-0 bg-blue-900/0 group-hover:bg-blue-900/10 transition-colors duration-300"></div>
                            </div>
                            <div class="px-1 text-left">
                                <h4 class="text-base sm:text-lg md:text-xl font-extrabold text-slate-800 group-hover:text-blue-600 transition-colors"><?= e($teacherName) ?></h4>
                                <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5 sm:mt-1"><?= e($teacherRole) ?></p>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="swiper-pagination-teacher mt-6 sm:mt-8 flex justify-center"></div>
            </div>
        </div>

        <div class="mt-6 sm:mt-8 flex justify-center">
            <a href="<?= e(page_url('dashboard-teacher')); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-5 sm:px-6 py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                Xem thêm <i class="fa-solid fa-arrow-right text-[10px] sm:text-xs"></i>
            </a>
        </div>

    </div>
</section>
 <section id="danh-gia" class="relative py-12 sm:py-14 md:py-20 overflow-hidden bg-transparent">
        <div class="absolute inset-0 pointer-events-none hidden sm:block">
            <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-cyan-200/30 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-rose-200/30 blur-3xl"></div>
        </div>

        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 relative z-10">
            <div class="mb-8 sm:mb-10" data-aos="fade-up">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-emerald-100 bg-white/80 px-3 sm:px-4 py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-black uppercase tracking-[0.35em] text-emerald-600 shadow-sm">
                        <i class="fa-regular fa-comment-dots"></i> Đánh giá từ người dùng
                    </div>
                    <h2 class="mt-3 sm:mt-4 text-2xl sm:text-3xl md:text-5xl font-black tracking-tight text-slate-900">Học viên nói gì về <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-cyan-500">trung tâm</span></h2>
                    <p class="mt-3 sm:mt-4 text-slate-600 font-medium text-sm sm:text-base md:text-lg leading-relaxed">Những phản hồi thật từ học viên và phụ huynh là thước đo rõ nhất cho chất lượng đào tạo và trải nghiệm học tập tại trung tâm.</p>
                </div>
            </div>

            <div class="swiper feedbackSwiper" data-aos="fade-up" data-aos-delay="120">
                <div class="swiper-wrapper pb-10 sm:pb-14">
                    <?php if (empty($homeFeedbacks)): ?>
                        <div class="swiper-slide h-auto">
                            <div class="rounded-2xl sm:rounded-[2rem] border border-dashed border-slate-300 bg-white/80 p-6 sm:p-8 text-center text-slate-500 font-medium text-sm sm:text-base">
                                Hiện chưa có đánh giá nào được duyệt để hiển thị.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($homeFeedbacks as $feedback): ?>
                            <?php
                            $feedbackName = (string) ($feedback['full_name'] ?? 'Học viên');
                            $feedbackClass = (string) ($feedback['course_name'] ?? '');
                            $feedbackTeacher = (string) ($feedback['teacher_name'] ?? '');
                            $feedbackContent = trim((string) ($feedback['comment'] ?? ''));
                            $feedbackRating = max(0, min(5, (int) ($feedback['rating'] ?? 0)));
                            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($feedbackName !== '' ? $feedbackName : 'User') . '&background=0f766e&color=fff&size=256&bold=true';
                            ?>
                            <div class="swiper-slide h-auto">
                                <article class="flex h-full min-h-[120px] flex-col rounded-[1.5rem] sm:rounded-[2rem] border border-white bg-white/90 p-4 sm:p-5 md:p-6 shadow-[0_15px_40px_rgba(15,23,42,0.08)] transition-all hover:-translate-y-1 md:min-h-[180px]">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                            <img src="<?= e($avatarUrl); ?>" alt="<?= e($feedbackName); ?>" class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl object-cover ring-2 ring-emerald-50 shrink-0">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-sm sm:text-base font-black text-slate-900"><?= e($feedbackName); ?></h3>
                                                <p class="text-[9px] sm:text-[10px] md:text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600">Học viên / Phụ huynh</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-1 shrink-0">
                                            <div class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[8px] sm:text-[9px] md:text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                                <i class="fa-regular fa-clock text-[8px] sm:text-[9px]"></i>
                                                <?= e($homeFormatFeedbackDate((string) ($feedback['created_at'] ?? ''))); ?>
                                            </div>
                                            <div class="flex items-center gap-0.5 sm:gap-1 text-amber-400">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?= $i <= $feedbackRating ? 'fa-solid' : 'fa-regular'; ?> fa-star text-[9px] sm:text-[10px] md:text-sm"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="mt-3 sm:mt-4 flex-1 text-xs sm:text-sm md:text-base leading-relaxed text-slate-600 line-clamp-3">
                                        “<?= e($feedbackContent !== '' ? $feedbackContent : 'Trải nghiệm học tập tại trung tâm rất tốt.'); ?>”
                                    </p>

                                    <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] md:text-[11px] font-bold uppercase tracking-widest text-slate-500">
                                        <?php if ($feedbackClass !== ''): ?>
                                            <span class="rounded-full bg-emerald-50 px-2 sm:px-3 py-0.5 sm:py-1 text-emerald-700"><?= e($feedbackClass); ?></span>
                                        <?php endif; ?>
                                        <?php if ($feedbackTeacher !== ''): ?>
                                            <span class="rounded-full bg-cyan-50 px-2 sm:px-3 py-0.5 sm:py-1 text-cyan-700">GV: <?= e($feedbackTeacher); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="swiper-pagination-feedback flex justify-center"></div>
            </div>
        </div>
    </section>

    <section id="lien-he" class="relative py-10 sm:py-14 md:py-20 overflow-hidden bg-transparent z-10">
        <div class="mx-auto w-full max-w-[1400px] flex flex-col lg:flex-row">

            <div class="w-full lg:w-1/2 flex flex-col justify-center px-4 sm:px-6 py-10 sm:py-16 lg:px-16 xl:px-32 z-10" data-aos="fade-right">
                
                <h2 class="text-2xl sm:text-[28px] md:text-[36px] font-bold text-[#185b9d] mb-4 sm:mb-5 tracking-tight">
                    Tư vấn và kiểm tra miễn phí
                </h2>

                <p class="max-w-xl text-slate-600 text-sm sm:text-base md:text-lg font-medium leading-relaxed mb-6 sm:mb-8">
                    Đăng ký ngay để được đội ngũ tư vấn hỗ trợ lộ trình học phù hợp, kiểm tra trình độ và nhận gợi ý khóa học tối ưu nhất.
                </p>

                <div class="inline-flex flex-col items-start gap-4 max-w-[420px]">
                    <a href="<?= e(page_url('register-consultation')); ?>" class="group relative inline-flex items-center justify-center rounded-full sm:rounded-[1.5rem] bg-[#2e3192] px-6 sm:px-8 py-4 sm:py-5 text-white font-black uppercase tracking-[0.18em] shadow-[0_18px_40px_rgba(46,49,146,0.28)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_rgba(46,49,146,0.38)] active:translate-y-0 focus:outline-none focus:ring-4 focus:ring-blue-400/30 w-full sm:w-auto">
                        <span class="absolute inset-x-4 top-0 h-2 rounded-full bg-white/25 blur-sm"></span>
                        <span class="relative flex items-center gap-2 sm:gap-3 text-sm sm:text-base md:text-lg">
                            <i class="fa-solid fa-pen-to-square transition-transform duration-300 group-hover:rotate-[-8deg]"></i>
                            Đăng ký ngay
                        </span>
                    </a>

                    <div class="flex items-center gap-2 sm:gap-3 text-xs sm:text-sm text-slate-500 font-medium bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-2 sm:py-3 shadow-sm">
                        <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <span>Chỉ mất vài giây để mở form đăng ký tư vấn.</span>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/2 relative min-h-[300px] sm:min-h-[400px] lg:min-h-[600px] mt-8 lg:mt-0" data-aos="fade-left">
                
                <img src="assets/images/tu_van_student.jpg" alt="Học sinh" class="absolute inset-0 w-full h-full object-cover object-top lg:object-center rounded-3xl lg:rounded-none">

                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-[#f4f7fb] to-transparent pointer-events-none lg:hidden"></div>

                <div class="absolute inset-y-0 left-0 w-24 md:w-32 bg-gradient-to-r from-[#f4f7fb] to-transparent pointer-events-none hidden lg:block"></div>

                <div class="absolute inset-y-0 right-0 w-24 md:w-32 bg-gradient-to-l from-[#f4f7fb] to-transparent pointer-events-none hidden lg:block"></div>

            </div>

        </div>
    </section>

</main>