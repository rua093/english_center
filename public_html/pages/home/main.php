<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
$homeSuccess = get_flash('home_success');
$homeError = get_flash('home_error');
?>

<main class="font-jakarta relative">

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

    <section id="hero-video" class="relative w-full h-[80vh] min-h-[600px] flex items-center justify-center mb-32 md:mb-40">
        <div class="absolute inset-0 z-0 overflow-hidden bg-black">
            <video autoplay loop muted playsinline class="absolute top-1/2 left-1/2 min-w-full min-h-full w-auto h-auto -translate-x-1/2 -translate-y-1/2 object-cover">
                <source src="assets/videodemo/iilavideo.mp4" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/70 via-blue-950/20 to-transparent w-full"></div>
        </div>

       <div class="relative z-10 w-full max-w-[1450px] mx-auto px-6 sm:px-10 flex flex-col -mt-20">
            <div class="max-w-2xl" data-aos="fade-right">
                <h1 class="text-4xl md:text-5xl font-black text-white leading-tight uppercase drop-shadow-lg">
                    GREATER YOU EVERYDAY <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-300 to-white">TRƯỞNG THÀNH HƠN</span> MỖI NGÀY
                </h1>
                <p class="mt-4 text-base md:text-lg text-sky-100 font-medium max-w-lg drop-shadow-md">
                    Đồng hành cùng mỗi học viên để khơi dậy tiềm năng và đam mê trên hành trình học tập trọn đời.
                </p>
                <div class="mt-8">
                    <a href="#" class="group inline-flex items-center gap-3 bg-[#2e3192]/80 hover:bg-[#2e3192] backdrop-blur-md text-white px-6 py-2.5 rounded-full text-sm font-bold uppercase transition-all border border-white/20 shadow-xl">
                        XEM VIDEO 
                        <span class="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,1)]"></span>
                    </a>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 translate-y-1/2 z-30 flex flex-col items-center px-4 sm:px-6">
            <div class="w-full max-w-[1180px] bg-[#2e3192] rounded-3xl shadow-[0_18px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden border border-blue-400/25 ring-1 ring-white/10">
                <div class="h-1 w-full bg-gradient-to-r from-amber-400 via-cyan-300 to-sky-400"></div>
                <div class="grid gap-0 lg:grid-cols-[1.35fr_1fr] items-stretch">
                    <div class="p-6 md:p-8 lg:p-10 lg:border-r border-white/10 relative overflow-hidden flex items-center">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.14),transparent_38%)]"></div>
                        <div class="relative z-10 max-w-3xl">
                            <p class="text-amber-100 font-semibold text-sm md:text-base leading-relaxed max-w-2xl">
                                Tìm các khóa học phù hợp với bạn và giúp con đường học vấn của bạn thành công
                            </p>
                        </div>
                    </div>
                    <div class="p-6 md:p-8 lg:p-10 bg-white/8 backdrop-blur-sm flex items-center relative overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute -top-8 right-10 h-20 w-20 rounded-none border border-white/10 bg-white/5 rotate-12"></div>
                            <div class="absolute bottom-8 right-16 h-12 w-12 rounded-none bg-amber-400/15 rotate-45"></div>
                            <div class="absolute top-1/2 left-6 h-14 w-14 rounded-none border border-cyan-300/20 bg-cyan-300/10 -rotate-12"></div>
                        </div>
                        <div class="relative z-10 w-full grid gap-4 sm:grid-cols-2">
                            <div class="relative">
                                <select class="w-full bg-white text-blue-950 font-bold rounded-2xl px-5 py-3 outline-none appearance-none cursor-pointer text-sm shadow-inner border border-white/60">
                                    <option value="" disabled selected>Độ tuổi</option>
                                    <option>Mầm non</option>
                                    <option>Tiểu học</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                            <div class="relative">
                                <select class="w-full bg-white text-blue-950 font-bold rounded-2xl px-5 py-3 outline-none appearance-none cursor-pointer text-sm shadow-inner border border-white/60">
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
                <a href="#" class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-black uppercase text-[10px] md:text-xs px-8 py-2.5 rounded-full shadow-lg border-2 border-white transition-transform hover:-translate-y-1 flex items-center gap-2">
                    KIỂM TRA TRÌNH ĐỘ MIỄN PHÍ <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                </a>
            </div>
        </div>
    </section>

    <section id="trang-chu" class="relative bg-transparent pt-16 lg:pt-24 lg:pb-12 overflow-hidden border-b border-blue-100/50">
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute w-[600px] h-[600px] bg-blue-400/20 blur-[120px] rounded-full -top-40 -left-40"></div>
            <div class="absolute w-[500px] h-[500px] bg-cyan-400/20 blur-[120px] rounded-full bottom-[-150px] right-[-150px]"></div>
        </div>

        <div class="relative z-10 max-w-[1450px] mx-auto px-4 sm:px-6 flex flex-col lg:flex-row gap-10 lg:gap-14 items-center lg:items-stretch">
            <div class="hidden lg:flex lg:w-5/12 relative items-end justify-center pt-10" data-aos="fade-right" data-aos-duration="1200">
                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 w-[90%] h-[80%] bg-gradient-to-t from-blue-300/40 to-transparent rounded-[3rem] blur-[60px] -z-10"></div>
                <img src="assets/images/student_girl.png" alt="Học sinh tiêu biểu" class="w-full max-w-[550px] object-contain relative z-10 drop-shadow-[0_20px_40px_rgba(30,58,138,0.25)]">
                
                <div class="absolute top-1/4 -left-4 bg-white/95 backdrop-blur-md px-5 py-4 rounded-2xl shadow-xl border border-blue-50 z-20 animate-bounce" style="animation-duration: 3s;">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-500 flex items-center justify-center text-xl shadow-inner"><i class="fa-solid fa-star"></i></div>
                        <div>
                            <h4 class="text-sm font-black text-blue-950 uppercase">Chất lượng</h4>
                            <p class="text-[11px] font-bold text-slate-500">Chuẩn quốc tế</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-7/12 flex flex-col gap-5 md:gap-6 pb-16 lg:py-6 z-20">
                <div class="grid sm:grid-cols-2 gap-5 md:gap-6 h-full min-h-[180px]">
                    <div class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-br from-sky-400 to-blue-500 text-white shadow-[0_15px_30px_rgba(14,165,233,0.3)] overflow-hidden flex flex-col justify-center" data-aos="fade-up">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <i class="fa-solid fa-rocket absolute -right-4 -bottom-4 text-[8rem] opacity-20 group-hover:scale-110 transition-transform duration-700"></i>
                        <h1 class="text-3xl md:text-4xl lg:text-4xl xl:text-5xl font-black leading-[1.15] text-blue-950 relative z-10 drop-shadow-sm text-left">Khát Vọng<br>Là Khởi Đầu</h1>
                    </div>
                    <div class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-bl from-blue-900 to-blue-800 text-white shadow-[0_15px_30px_rgba(30,58,138,0.3)] overflow-hidden flex flex-col justify-center" data-aos="fade-up" data-aos-delay="100">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <i class="fa-solid fa-trophy absolute -right-4 -bottom-4 text-[8rem] opacity-10 group-hover:scale-110 transition-transform duration-700"></i>
                        <h2 class="text-3xl md:text-4xl lg:text-4xl xl:text-5xl font-black leading-[1.15] relative z-10 drop-shadow-sm text-left text-blue-50">Của Mọi<br>Thành Tựu</h2>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5 md:gap-6">
                    <a href="#thi-thu" class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-br from-rose-600 to-red-500 text-white shadow-[0_15px_30px_rgba(225,29,72,0.3)] overflow-hidden transition-all hover:-translate-y-2 h-48 md:h-56 flex flex-col justify-end" data-aos="fade-up" data-aos-delay="200">
                        <div class="absolute top-6 left-6 bg-white text-red-600 text-[10px] sm:text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-md">NEW</div>
                        <i class="fa-solid fa-chalkboard-user absolute -right-6 -top-6 text-[10rem] opacity-15 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-700"></i>
                        <h3 class="text-2xl md:text-3xl font-black relative z-10 leading-[1.2] w-11/12 drop-shadow-md">Thi Thử Nhận Kết Quả Ngay</h3>
                    </a>
                    <a href="#tai-lieu" class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-[0_15px_30px_rgba(37,99,235,0.3)] overflow-hidden transition-all hover:-translate-y-2 h-48 md:h-56 flex flex-col justify-end" data-aos="fade-up" data-aos-delay="300">
                        <i class="fa-solid fa-book-open absolute -right-6 -bottom-6 text-[10rem] opacity-15 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-700"></i>
                        <h3 class="text-2xl md:text-3xl font-black relative z-10 leading-[1.2] w-11/12 drop-shadow-md">Tài Liệu Học Tiếng Anh</h3>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-5" data-aos="fade-up" data-aos-delay="400">
                    <a href="#ovi" class="group relative rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-blue-100 shadow-[0_10px_20px_rgba(30,58,138,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-blue-400 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-laptop-code text-xl"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-sm leading-tight">Cổng OVI</h4><span class="text-[10px] font-semibold text-slate-500 mt-1 block">Hệ thống học tập</span>
                    </a>
                    <a href="#he" class="group relative rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-teal-100 shadow-[0_10px_20px_rgba(20,184,166,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-teal-400 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-3 group-hover:bg-teal-600 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-sun text-xl animate-[spin_10s_linear_infinite]"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-sm leading-tight">Tiếng Anh Hè</h4><span class="text-[10px] font-semibold text-slate-500 mt-1 block">Chương trình 2026</span>
                    </a>
                    <a href="#doanh-nghiep" class="group relative rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-amber-100 shadow-[0_10px_20px_rgba(245,158,11,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-amber-400 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:bg-amber-500 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-briefcase text-xl"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-sm leading-tight">Doanh Nghiệp</h4><span class="text-[10px] font-semibold text-slate-500 mt-1 block">Giải pháp đào tạo</span>
                    </a>
                    <a href="#thanh-tich" class="group relative rounded-2xl p-5 bg-white/90 backdrop-blur-xl border border-emerald-100 shadow-[0_10px_20px_rgba(16,185,129,0.05)] transition-all hover:-translate-y-2 hover:shadow-xl hover:border-emerald-400 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:bg-emerald-500 group-hover:text-white transition-colors shadow-sm"><i class="fa-solid fa-medal text-xl"></i></div>
                        <h4 class="font-extrabold text-blue-950 text-sm leading-tight">Thành Tích</h4><span class="text-[10px] font-semibold text-slate-500 mt-1 block">Học viên xuất sắc</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

	<?php if ($homeUser && (string) ($homeUser['role'] ?? '') === 'teacher'): ?>
		<section id="lich-day-noi-bo" class="py-12 relative z-20 bg-transparent" aria-label="Widget giáo viên">
			<div class="mx-auto w-full max-w-6xl px-4 sm:px-6" data-aos="fade-up">
				<div class="rounded-[2rem] border border-blue-100 bg-white/80 backdrop-blur-md p-6 md:p-8 shadow-xl shadow-blue-900/5">
					<div class="flex items-center justify-between mb-6">
						<h2 class="text-2xl font-extrabold text-[#2e3192]">Lịch dạy 7 ngày tới</h2>
						<a class="text-sm font-bold text-[#00d4ff] hover:text-[#2e3192] transition-colors hover:underline" href="<?= e(page_url('profile')); ?>">Xem tất cả</a>
					</div>
					<?php if (empty($teacherSchedules)): ?>
						<div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white/50 py-10 text-center">
							<i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
							<p class="text-slate-500 font-medium">Chưa có lịch dạy trong 7 ngày tới.</p>
						</div>
					<?php else: ?>
						<ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
							<?php foreach ($teacherSchedules as $schedule): ?>
								<li class="group rounded-2xl border border-slate-100 bg-white/90 p-5 transition-all hover:border-blue-300 hover:shadow-lg hover:-translate-y-1">
									<strong class="block text-lg text-[#2e3192] group-hover:text-[#00d4ff] transition-colors"><?= e((string) $schedule['class_name']); ?></strong>
									<div class="mt-3 flex flex-col gap-2 text-sm text-slate-500 font-medium">
										<span class="flex items-center gap-2"><i class="fa-regular fa-calendar text-blue-400 w-4"></i> <?= e((string) $schedule['study_date']); ?></span>
										<span class="flex items-center gap-2"><i class="fa-regular fa-clock text-blue-400 w-4"></i> <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?></span>
										<span class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-blue-400 w-4"></i> <?= e((string) $schedule['room_name']); ?></span>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section id="gioi-thieu" class="relative py-28 overflow-hidden bg-transparent">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-gradient-to-br from-blue-300/40 to-sky-200/40 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-gradient-to-tr from-cyan-200/40 to-blue-200/40 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 md:gap-24 items-center">
            <div class="relative" data-aos="fade-right">
                <div class="absolute -bottom-6 -right-6 w-full h-full rounded-[2.5rem] bg-gradient-to-br from-blue-600/10 to-cyan-500/10 border border-blue-900/5"></div>
                <div class="relative rounded-[2.5rem] overflow-hidden shadow-[0_20px_50px_rgba(30,58,138,0.15)] group border-[6px] border-white/80">
                    <img src="/assets/images/center.jpg" alt="Vị trí trung tâm" class="w-full h-[500px] object-cover transform group-hover:scale-105 transition duration-700 ease-in-out">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-950/60 via-blue-950/20 to-transparent opacity-80"></div>
                </div>
                <div class="absolute -bottom-8 right-4 md:-right-4 bg-white/95 backdrop-blur-md px-6 py-4 rounded-2xl shadow-[0_15px_40px_rgba(30,58,138,0.15)] border border-blue-50 hover:-translate-y-1 transition-transform cursor-default z-20">
                    <div class="flex items-center gap-4">
                        <div class="relative flex h-12 w-12 items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-30"></span>
                            <div class="relative w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-blue-600 font-black mb-0.5 flex items-center gap-1.5">Vị trí trung tâm</p>
                            <h4 class="text-sm font-black text-blue-950">Quảng Phú – Đà Nẵng</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-7" data-aos="fade-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/80 border border-blue-100 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="text-blue-800 text-xs font-bold uppercase tracking-wider">Về Nhuệ Minh Edu</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold leading-[1.15] text-blue-950">Nâng tầm ngoại ngữ,<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">Khơi nguồn tự tin</span></h2>
                <p class="text-lg text-slate-600 leading-relaxed font-medium">Trung tâm ngoại ngữ hiện đại với không gian học tập truyền cảm hứng, cam kết mang lại giá trị thực tế, giúp học viên phát triển toàn diện 4 kỹ năng và sẵn sàng hội nhập.</p>

                <div class="grid sm:grid-cols-2 gap-5 mt-6">
                    <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-blue-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-map-location-dot"></i></div>
                        <h4 class="font-extrabold text-blue-950 mb-1 text-base">Vị trí thuận lợi</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">Dễ dàng di chuyển, gần khu dân cư & trường học.</p>
                    </div>
                    <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-sm border border-teal-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 text-white flex items-center justify-center mb-4 shadow-sm group-hover:scale-110 transition-transform"><i class="fa-solid fa-shield-halved"></i></div>
                        <h4 class="font-extrabold text-teal-950 mb-1 text-base">Môi trường an toàn</h4>
                        <p class="text-sm text-slate-600 leading-relaxed">Không gian học tập hiện đại, thân thiện và an ninh.</p>
                    </div>
                </div>
                <div class="pt-4">
                    <a href="#lien-he" class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full bg-gradient-to-r from-blue-600 to-sky-500 text-white font-bold shadow-[0_10px_20px_rgba(37,99,235,0.3)] transition-all hover:-translate-y-1 hover:shadow-[0_15px_25px_rgba(37,99,235,0.4)]">
                        Khám phá ngay <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <style>
    /* Hiệu ứng quay của vòng quỹ đạo */
    @keyframes spin-orbit {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    /* Hiệu ứng quay ngược của các node để giữ chữ luôn thẳng đứng */
    @keyframes spin-orbit-reverse {
        from { transform: rotate(0deg); }
        to { transform: rotate(-360deg); }
    }
    
    .orbit-spin {
        animation: spin-orbit 40s linear infinite;
    }
    .orbit-reverse-spin {
        animation: spin-orbit-reverse 40s linear infinite;
    }
    
    /* Tạm dừng toàn bộ vòng quay khi di chuột vào */
    .orbit-wrapper:hover .orbit-spin,
    .orbit-wrapper:hover .orbit-reverse-spin {
        animation-play-state: paused;
    }
</style>

<section id="su-menh" class="py-32 relative overflow-hidden bg-transparent z-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-[#2e3192]">Sứ Mệnh <span class="text-red-600">Toàn Cầu</span></h2>
            <p class="mt-4 text-slate-600 font-medium max-w-2xl mx-auto text-lg">Kiến tạo thế hệ công dân làm chủ tương lai thông qua ngôn ngữ và kỹ năng toàn diện.</p>
        </div>
        
        <div class="relative w-full max-w-[1260px] mx-auto min-h-[620px] md:min-h-[940px] flex items-center justify-center orbit-wrapper mt-10 md:mt-2">
            
            <div class="absolute w-[430px] h-[430px] md:w-[770px] md:h-[770px] rounded-full border-2 border-dashed border-blue-400/50 orbit-spin z-10">
                
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50">
                    <div class="orbit-reverse-spin group cursor-pointer">
                        <div class="flex items-center gap-4 bg-white/95 backdrop-blur-md p-3 pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-amber-300">
                            <div class="w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-3xl shrink-0 bg-amber-50 text-amber-500 shadow-inner">💡</div>
                            <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[140px] max-h-[36px] group-hover:max-w-[340px] group-hover:max-h-[170px]">
                                <h4 class="font-black text-[#2e3192] text-sm md:text-lg whitespace-nowrap">Sáng Tạo</h4>
                                <p class="text-xs md:text-sm text-slate-600 font-medium mt-1 w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                    Xây dựng không gian học ngoại ngữ thân thiện, hiệu quả và đầy cảm hứng.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute top-1/2 right-0 translate-x-1/2 -translate-y-1/2 z-50">
                    <div class="orbit-reverse-spin group cursor-pointer">
                        <div class="flex items-center gap-4 bg-white/95 backdrop-blur-md p-3 pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-blue-300">
                            <div class="w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-3xl shrink-0 bg-blue-50 text-blue-500 shadow-inner">🗣️</div>
                            <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[140px] max-h-[36px] group-hover:max-w-[340px] group-hover:max-h-[170px]">
                                <h4 class="font-black text-[#2e3192] text-sm md:text-lg whitespace-nowrap">Tự Tin</h4>
                                <p class="text-xs md:text-sm text-slate-600 font-medium mt-1 w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                    Làm chủ tiếng Anh từ những câu đơn giản đến hội thoại thực tế đời sống.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 z-50">
                    <div class="orbit-reverse-spin group cursor-pointer">
                        <div class="flex items-center gap-4 bg-white/95 backdrop-blur-md p-3 pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-emerald-300">
                            <div class="w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-3xl shrink-0 bg-emerald-50 text-emerald-500 shadow-inner">🎯</div>
                            <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[140px] max-h-[36px] group-hover:max-w-[340px] group-hover:max-h-[170px]">
                                <h4 class="font-black text-[#2e3192] text-sm md:text-lg whitespace-nowrap">Toàn Diện</h4>
                                <p class="text-xs md:text-sm text-slate-600 font-medium mt-1 w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                    Đào tạo bài bản 4 kỹ năng Nghe – Nói – Đọc – Viết cho mọi lứa tuổi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute top-1/2 left-0 -translate-x-1/2 -translate-y-1/2 z-50">
                    <div class="orbit-reverse-spin group cursor-pointer">
                        <div class="flex items-center gap-4 bg-white/95 backdrop-blur-md p-3 pr-6 rounded-full shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl hover:border-purple-300">
                            <div class="w-14 h-14 md:w-16 md:h-16 rounded-full flex items-center justify-center text-3xl shrink-0 bg-purple-50 text-purple-500 shadow-inner">🤝</div>
                            <div class="overflow-hidden transition-[max-width,max-height] duration-500 ease-in-out max-w-[140px] max-h-[36px] group-hover:max-w-[340px] group-hover:max-h-[170px]">
                                <h4 class="font-black text-[#2e3192] text-sm md:text-lg whitespace-nowrap">Cam Kết</h4>
                                <p class="text-xs md:text-sm text-slate-600 font-medium mt-1 w-[260px] opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100 leading-relaxed">
                                    Theo sát lộ trình, khơi dậy niềm yêu thích với phương châm "Dám nói".
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="relative z-20 w-64 h-64 md:w-[450px] md:h-[450px] rounded-full border-[10px] md:border-[14px] border-white shadow-[0_20px_60px_rgba(30,58,138,0.2)] overflow-hidden bg-white flex items-center justify-center group" data-aos="zoom-in">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800" alt="Trung tâm" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-0 bg-blue-900/10 group-hover:bg-transparent transition-colors"></div>
            </div>

        </div>
    </div>
</section>                            
								
	<section id="khoa-hoc" class="py-20 md:py-28 relative overflow-hidden bg-transparent">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        <div class="mb-14 text-center" data-aos="fade-up">
            <h2 class="text-3xl md:text-5xl font-black text-[#2e3192] uppercase tracking-tight">
                KHOÁ HỌC DÀNH CHO <span class="inline-block mt-2 md:mt-0 rounded-full bg-red-600 px-6 py-2 text-white shadow-lg transform -rotate-2">MỌI MỤC TIÊU</span>
            </h2>
            <p class="mt-6 text-lg text-slate-600 max-w-3xl mx-auto font-medium">
                Dễ dàng lựa chọn khóa học tiếng Anh phù hợp cho riêng mình với chương trình học đa dạng, được thiết kế phù hợp với nhu cầu và trình độ thực tế.
            </p>
        </div>

        <div class="rounded-[3rem] bg-white/40 backdrop-blur-md border border-white p-6 md:p-8 lg:p-10 shadow-[0_15px_40px_rgba(30,58,138,0.06)]" data-aos="zoom-in">
            <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white/90 shadow-lg border border-white transition-all duration-300 hover:-translate-y-3 hover:shadow-xl">
                    <div class="relative bg-blue-100 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=600&h=400" alt="Giao tiếp" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-4 right-4 bg-white/90 text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Level</span>
                            <span class="block text-2xl font-black leading-none">1-2</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">Giao tiếp<br>Phản xạ</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Phát triển phản xạ nghe nói tự nhiên và tự tin giao tiếp trong các tình huống thực tế sau 8-12 tuần.</p>
                        <div class="mt-5 pt-4 border-t-2 border-slate-100 flex justify-between items-end">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">Học phí từ</span>
                                <span class="text-xl font-black text-[#2e3192]">3.200.000đ</span>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white/90 shadow-lg border border-white transition-all duration-300 hover:-translate-y-3 hover:shadow-xl">
                    <div class="relative bg-indigo-100 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600&h=400" alt="IELTS Foundation" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-4 right-4 bg-white/90 text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Target</span><span class="block text-2xl font-black leading-none">4.5+</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">IELTS<br>Foundation</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Củng cố nền tảng ngữ pháp, từ vựng và làm quen với format 4 kỹ năng.</p>
                    </div>
                </article>
                
                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white/90 shadow-lg border border-white transition-all duration-300 hover:-translate-y-3 hover:shadow-xl">
                    <div class="relative bg-blue-50 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&q=80&w=600&h=400" alt="IELTS Intensive" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute top-4 right-4 bg-white/90 text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Target</span><span class="block text-2xl font-black leading-none">6.5+</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">IELTS<br>Intensive</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Tối ưu chiến lược làm bài, sửa lỗi sai trực tiếp 1-1 và nâng band điểm thần tốc.</p>
                    </div>
                </article>

                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white/90 shadow-lg border border-white transition-all duration-300 hover:-translate-y-3 hover:shadow-xl">
                    <div class="relative bg-slate-100 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=600&h=400" alt="Doanh nghiệp" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute top-4 right-4 bg-white/90 text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Cho</span><span class="block text-xl font-black leading-none mt-1">Người lớn</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">Business<br>English</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Tiếng Anh ứng dụng trong môi trường công sở: email, thuyết trình, đàm phán.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

    <section id="ngoai-khoa" class="py-16 sm:py-24 bg-transparent relative overflow-hidden">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="mb-12 text-center md:text-left">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-800 mb-2">
                    Có những mùa hè trôi qua, có những mùa hè con mang theo mãi mãi...
                </h2>
                <p class="text-slate-600 font-medium text-sm md:text-base">Khám phá các hoạt động ngoại khóa nổi bật trong tháng này.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <?php
                $activities = [
                    ['img' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=800', 'title' => 'Tăng tốc Anh ngữ', 'desc' => '8 tuần – làm chủ nửa năm kiến thức'],
                    ['img' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?q=80&w=800', 'title' => 'Niềm vui trọn vẹn', 'desc' => 'Bạn thật – trải nghiệm thật – ký ức thật'],
                    ['img' => 'https://images.unsplash.com/photo-1587691592099-24045742c181?q=80&w=800', 'title' => 'Bước ngoặt trưởng thành', 'desc' => 'Dám nghĩ – dám nói – dám kết nối'],
                    ['img' => 'https://images.unsplash.com/photo-1609220136736-443140cffec6?q=80&w=800', 'title' => 'Điểm chạm gia đình', 'desc' => 'Cùng con lớn lên trong từng khoảnh khắc']
                ];
                foreach ($activities as $act): ?>
                <div class="group bg-white/80 backdrop-blur-md hover:bg-white rounded-3xl p-3 border border-white shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 cursor-pointer flex flex-col">
                    <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden mb-4">
                        <img src="<?= $act['img'] ?>" alt="<?= $act['title'] ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                    </div>
                    <div class="px-2 pb-3 text-center flex-1 flex flex-col justify-start">
                        <h3 class="text-[#0d3b66] font-bold text-lg leading-tight mb-2 group-hover:text-blue-600 transition-colors"><?= $act['title'] ?></h3>
                        <p class="text-slate-500 text-sm font-medium leading-snug px-1"><?= $act['desc'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
	
    <section id="giao-vien" class="py-20 md:py-28 relative overflow-hidden bg-transparent border-t border-white/30">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-5xl font-black text-blue-600 uppercase leading-tight tracking-tight">
                    Hơn 3.100 Giáo viên và trợ giảng <br>
                    <span class="relative inline-block text-white px-8 py-2 mt-4">
                        <span class="relative z-10">Chuẩn quốc tế</span>
                        <div class="absolute inset-0 bg-red-600 rounded-full -rotate-1 shadow-lg"></div>
                    </span>
                </h2>
            </div>

            <div class="bg-blue-500/90 backdrop-blur-md rounded-t-[3rem] p-8 md:p-12 shadow-xl" data-aos="fade-up">
                <div class="grid lg:grid-cols-3 gap-8 items-center">
                    <div class="lg:col-span-2">
                        <p class="text-white font-extrabold text-xl md:text-2xl leading-snug">
                            Đội ngũ giảng dạy chuẩn quốc tế lớn nhất Việt Nam sẵn sàng cùng Phụ huynh & Học viên chinh phục mọi mục tiêu.
                        </p>
                    </div>
                    <div class="flex justify-center lg:justify-end">
                        <div class="flex -space-x-4">
                            <div class="w-14 h-14 rounded-full border-4 border-blue-600 bg-slate-200 overflow-hidden shadow-lg"><img src="https://i.pravatar.cc/100?u=1" alt="T1"></div>
                            <div class="w-14 h-14 rounded-full border-4 border-blue-600 bg-slate-200 overflow-hidden shadow-lg"><img src="https://i.pravatar.cc/100?u=2" alt="T2"></div>
                            <div class="w-14 h-14 rounded-full border-4 border-blue-600 bg-white text-blue-600 flex items-center justify-center text-xs font-black shadow-lg">+3k</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md border-x border-b border-white rounded-b-[3rem] p-6 md:p-10 shadow-xl" data-aos="fade-up">
                <div class="swiper teacherSwiper pb-12">
                    <div class="swiper-wrapper">
                        <?php 
                        $sample_teachers = [
                            ['name' => 'ANNE KENTHILL', 'exp' => '+8', 'img' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400'],
                            ['name' => 'SCOTT PORTER', 'exp' => '+12', 'img' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400'],
                            ['name' => 'PETER HARBISON', 'exp' => '+10', 'img' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400'],
                            ['name' => 'SARAH JENNER', 'exp' => '+6', 'img' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400'],
                        ];
                        foreach ($sample_teachers as $t): 
                        ?>
                        <div class="swiper-slide h-auto">
                            <article class="h-full flex flex-col rounded-[2rem] bg-white border-4 border-white hover:border-blue-300 overflow-hidden transition-all duration-500 hover:-translate-y-3 hover:shadow-xl group">
                                <div class="relative aspect-[3/4] overflow-hidden bg-slate-200">
                                    <img src="<?= $t['img'] ?>" alt="<?= $t['name'] ?>" class="w-full h-full object-cover grayscale opacity-90 group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-700 group-hover:scale-105">
                                    <div class="absolute top-4 right-4 bg-red-600 text-white rounded-xl p-2 text-center min-w-[52px] shadow-lg">
                                        <span class="block text-xl font-black leading-none"><?= $t['exp'] ?></span><span class="block text-[7px] font-bold uppercase mt-1">Năm</span>
                                    </div>
                                </div>
                                <div class="p-5 text-center flex-1 flex flex-col justify-center bg-white">
                                    <h4 class="text-sm font-black text-blue-600 uppercase"><?= $t['name'] ?></h4>
                                </div>
                            </article>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination-teacher mt-10"></div>
                </div>
            </div>
        </div>
    </section>

	<section id="portal" class="relative py-20 md:py-32 overflow-hidden bg-transparent border-t border-white/30">
		<div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6">
			<div class="mb-16 text-center max-w-2xl mx-auto" data-aos="fade-down">
				<h2 class="text-3xl md:text-4xl font-black text-[#2e3192] uppercase tracking-tight">
					TÍNH năng nội bộ <span class="text-blue-500">mạnh mẽ</span>
				</h2>
			</div>

			<div class="grid gap-8 grid-cols-1 md:grid-cols-3 items-center">
				<article class="group rounded-[2rem] bg-white/80 backdrop-blur-md p-8 shadow-lg border border-white transition-all hover:-translate-y-3 hover:shadow-2xl" data-aos="fade-up">
					<div class="relative z-10 text-center">
						<div class="mx-auto relative mb-8 h-24 w-24">
							<div class="absolute inset-0 bg-blue-100 rounded-3xl rotate-6 group-hover:rotate-12 group-hover:bg-blue-200 transition-all"></div>
							<div class="absolute inset-0 bg-white rounded-3xl shadow-md overflow-hidden border border-slate-100"><i class="fa-solid fa-graduation-cap text-4xl text-blue-500 flex items-center justify-center w-full h-full"></i></div>
						</div>
						<h3 class="text-xl font-extrabold text-[#2e3192]">Học tập thông minh</h3>
						<p class="mt-4 text-slate-600 font-medium">Theo dõi lộ trình, nộp bài tập và tương tác trực tiếp với giáo viên qua cổng trực tuyến.</p>
					</div>
				</article>

                <article class="group rounded-[2rem] bg-[#2e3192] p-8 shadow-2xl text-center transform md:-translate-y-6 transition-all hover:-translate-y-8 border-4 border-white" data-aos="fade-up" data-aos-delay="100">
					<div class="relative z-10">
						<div class="mx-auto relative mb-8 h-28 w-28">
							<div class="absolute inset-0 bg-white/20 backdrop-blur-md rounded-[2rem] -rotate-6 transition-all group-hover:-rotate-12"></div>
							<div class="absolute inset-0 bg-white rounded-[2rem] shadow-xl overflow-hidden border-2 border-blue-400"><i class="fa-solid fa-users-gear text-5xl text-blue-500 flex items-center justify-center w-full h-full"></i></div>
						</div>
						<h3 class="text-xl font-extrabold text-white">Quản lý toàn diện</h3>
						<p class="mt-4 text-blue-100 font-medium">Điều phối lớp học, điểm danh và phê duyệt yêu cầu học vụ chỉ với một chạm.</p>
					</div>
				</article>

                <article class="group rounded-[2rem] bg-white/80 backdrop-blur-md p-8 shadow-lg border border-white transition-all hover:-translate-y-3 hover:shadow-2xl" data-aos="fade-up" data-aos-delay="200">
					<div class="relative z-10 text-center">
						<div class="mx-auto relative mb-8 h-24 w-24">
							<div class="absolute inset-0 bg-blue-100 rounded-3xl rotate-6 transition-all group-hover:rotate-12"></div>
							<div class="absolute inset-0 bg-white rounded-3xl shadow-md overflow-hidden border border-slate-100"><i class="fa-solid fa-file-invoice-dollar text-4xl text-blue-500 flex items-center justify-center w-full h-full"></i></div>
						</div>
						<h3 class="text-xl font-extrabold text-[#2e3192]">Thanh toán tiện lợi</h3>
						<p class="mt-4 text-slate-600 font-medium">Minh bạch hóa học phí, lưu trữ lịch sử giao dịch và xuất hóa đơn điện tử tức thì.</p>
					</div>
				</article>
			</div>
		</div>
	</section>

	<section id="lien-he" class="relative py-20 md:py-32 overflow-hidden bg-transparent border-t border-white/30">
        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 sm:px-6">
            <div class="mb-14 text-center" data-aos="fade-up">
                <h2 class="text-3xl md:text-5xl font-black text-[#0c4a6e] uppercase tracking-tight">ĐĂNG KÝ NHẬN TƯ VẤN NGAY</h2>
                <p class="mt-5 text-[#0284c7] font-semibold text-lg">Đội ngũ chuyên viên sẽ liên hệ và hỗ trợ bạn trong thời gian sớm nhất</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 items-stretch">
                <div class="md:col-span-2 bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-xl border border-white" data-aos="fade-right">
                    <form class="flex flex-col gap-5" action="#" method="post">
                        <?= csrf_input(); ?>
                        
                        <div>
                            <input class="w-full bg-white/95 text-blue-900 placeholder-slate-400 font-semibold rounded-full px-6 py-4 outline-none focus:bg-white focus:ring-4 focus:ring-blue-300/50 transition-all shadow-inner" type="text" name="full_name" placeholder="Họ Và Tên *" required>
                        </div>

			<!-- <div class="grid gap-3" data-role-panels>
				<article class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm" data-role="student">
					<h3>Học viên</h3>
					<p>Theo dõi lịch học, tiến độ, bài tập và học phí trong một không gian duy nhất.</p>
				</article>
				<article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-role="teacher">
					<h3>Giáo viên</h3>
					<p>Quản lý bài tập, chấm điểm nhanh và theo dõi kết quả học viên theo lớp.</p>
				</article>
				<article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-role="staff">
					<h3>Giáo vụ</h3>
					<p>Điều phối lớp học, lịch học, quy trình phê duyệt và hoạt động nội bộ.</p>
				</article>
				<article class="hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-role="admin">
					<h3>Admin</h3>
					<p>Quản trị toàn bộ hệ thống, phân quyền chi tiết và theo dõi hiệu quả vận hành.</p>
				</article>
			</div>
		</div>
	</section>

	<section class="py-10 md:py-14 bg-slate-900 text-slate-100" id="lien-he">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
			<h2>Sẵn sàng nâng cấp lộ trình học tiếng Anh?</h2>
			<p>Để lại thông tin theo nhu cầu của bạn: tư vấn học viên hoặc ứng tuyển giáo viên.</p>

			<?php if ($homeSuccess): ?>
				<div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-800"><?= e($homeSuccess); ?></div>
			<?php endif; ?>
			<?php if ($homeError): ?>
				<div class="mt-4 rounded-xl border border-rose-300 bg-rose-100 px-4 py-3 text-sm font-semibold text-rose-800"><?= e($homeError); ?></div>
			<?php endif; ?>

			<div class="mt-6 grid gap-4 lg:grid-cols-2">
				<form class="grid gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4" action="/api/leads/submit" method="post">
					<?= csrf_input(); ?>
					<h3 class="text-base font-extrabold text-white">Form tư vấn học viên</h3>
					<label class="grid gap-1 text-left text-sm">
						Họ và tên
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="full_name" placeholder="Nguyen Van A" required>
					</label>
					<label class="grid gap-1 text-left text-sm">
						Số điện thoại
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="tel" name="phone" placeholder="09xxxxxxxx" required>
					</label>
					<label class="grid gap-1 text-left text-sm">
						Email
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="email" name="email" placeholder="ban@example.com">
					</label>
					<div class="grid gap-3 md:grid-cols-2">
						<label class="grid gap-1 text-left text-sm">
							Tuổi
							<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="number" min="0" name="age" placeholder="15">
						</label>
						<label class="grid gap-1 text-left text-sm">
							Mục tiêu điểm
							<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="target_score" placeholder="IELTS 6.5">
						</label>
					</div>
					<label class="grid gap-1 text-left text-sm">
						Chương trình quan tâm
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="target_program" placeholder="IELTS Foundation">
					</label>
					<label class="grid gap-1 text-left text-sm">
						Lịch mong muốn
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="desired_schedule" placeholder="Toi T2-T4-T6">
					</label>
					<label class="grid gap-1 text-left text-sm">
						Ghi chú
						<textarea class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" name="note" rows="3" placeholder="Nhu cau hoc tap va thong tin bo sung"></textarea>
					</label>
					<button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Gửi tư vấn học viên</button>
				</form>

				<form class="grid gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4" action="/api/applications/submit" method="post">
					<?= csrf_input(); ?>
					<h3 class="text-base font-extrabold text-white">Form ứng tuyển giáo viên</h3>
					<label class="grid gap-1 text-left text-sm">
						Họ và tên
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="full_name" placeholder="Tran Thi B" required>
					</label>
					<label class="grid gap-1 text-left text-sm">
						Số điện thoại
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="tel" name="phone" placeholder="09xxxxxxxx" required>
					</label>
					<label class="grid gap-1 text-left text-sm">
						Email
						<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="email" name="email" placeholder="teacher@example.com">
					</label>
					<div class="grid gap-3 md:grid-cols-2">
						<label class="grid gap-1 text-left text-sm">
							Vị trí ứng tuyển
							<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="applying_position" placeholder="IELTS Teacher">
						</label>
						<label class="grid gap-1 text-left text-sm">
							Bằng cấp
							<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="degree" placeholder="TESOL/CELTA">
						</label>
					</div>
					<div class="grid gap-3 md:grid-cols-2">
						<label class="grid gap-1 text-left text-sm">
							Số năm kinh nghiệm
							<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="number" min="0" name="experience_years" placeholder="3">
						</label>
						<label class="grid gap-1 text-left text-sm">
							Lịch có thể dạy
							<input class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" type="text" name="available_schedule" placeholder="Toi T2-T6">
						</label>
					</div>
					<label class="grid gap-1 text-left text-sm">
						Giới thiệu bản thân
						<textarea class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" name="intro" rows="3" placeholder="Kinh nghiem day hoc va diem manh chuyen mon"></textarea>
					</label>
					<button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800">Gửi hồ sơ ứng tuyển</button>
				</form>
			</div>
		</div>
	</section> -->
                        <input class="w-full bg-slate-50 text-blue-900 rounded-full px-6 py-4 outline-none focus:ring-4 focus:ring-blue-200 transition-all" type="text" name="full_name" placeholder="Họ Và Tên *" required>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <input class="w-full bg-slate-50 text-blue-900 rounded-full px-6 py-4 outline-none focus:ring-4 focus:ring-blue-200" type="tel" name="phone" placeholder="Điện thoại *" required>
                            <select name="learning_mode" class="w-full bg-slate-50 text-blue-900 rounded-full px-6 py-4 outline-none focus:ring-4 focus:ring-blue-200" required>
                                <option value="" disabled selected hidden>Hình thức học *</option>
                                <option value="offline">Học tại trung tâm</option>
                            </select>
                        </div>
                        <input class="w-full bg-slate-50 text-blue-900 rounded-full px-6 py-4 outline-none focus:ring-4 focus:ring-blue-200" type="email" name="email" placeholder="Địa chỉ email">
                        <button type="submit" class="mt-4 bg-[#2e3192] hover:bg-blue-700 text-white font-black rounded-full py-5 px-4 text-xl uppercase transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                            Gửi yêu cầu
                        </button>
                    </form>
                </div>

                <div class="flex flex-col gap-5 h-full" data-aos="fade-left">
                    <div class="flex-1 bg-white/80 backdrop-blur-xl rounded-[2rem] p-6 border border-white shadow-xl flex flex-col justify-center gap-4">
                        <h3 class="text-[#0c4a6e] font-extrabold text-center text-lg uppercase">Hỗ trợ trực tuyến</h3>
                        <a href="#" class="bg-red-50 text-red-600 rounded-full py-3 px-4 text-center font-bold">HOTLINE: 028.7308.3333</a>
                        <a href="#" class="bg-blue-50 text-blue-600 rounded-full py-3 px-4 text-center font-bold">Zalo / Messenger</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dynamicBg = document.getElementById('dynamic-scroll-bg');
            if(dynamicBg) {
                window.addEventListener('scroll', () => {
                    // Lấy vị trí cuộn hiện tại
                    const scrollTop = window.scrollY || document.documentElement.scrollTop;
                    // Lấy tổng chiều cao có thể cuộn
                    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
                    
                    // Tránh lỗi chia cho 0 nếu trang quá ngắn
                    if (maxScroll <= 0) return;
                    
                    const scrollPercent = scrollTop / maxScroll;
                    
                    // Di chuyển nền gradient lên trên (Tối đa 75% vì lớp fixed cao 400vh)
                    const translateY = scrollPercent * 75;
                    dynamicBg.style.transform = `translateY(-${translateY}%)`;
                });
            }
        });
    </script>
</main>