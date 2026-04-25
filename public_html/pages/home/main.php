<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
$homeCourses = $homeCourses ?? [];
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
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 translate-y-1/2 z-30 flex flex-col items-center px-4 sm:px-6">
            <div class="w-full max-w-[1180px] bg-gradient-to-br from-red-200 via-rose-200 to-lime-200 rounded-3xl shadow-[0_18px_60px_rgba(0,0,0,0.18)] flex flex-col overflow-hidden border border-lime-400/35 ring-1 ring-white/10">
                <div class="h-1 w-full bg-gradient-to-r from-amber-400 via-cyan-300 to-sky-400"></div>
                <div class="grid gap-0 lg:grid-cols-[1.35fr_1fr] items-stretch">
                    <div class="p-6 md:p-8 lg:p-10 lg:border-r border-lime-200/80 relative overflow-hidden flex items-center">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.58),transparent_38%)]"></div>
                        <div class="relative z-10 max-w-3xl">
                            <p class="text-slate-800 font-semibold text-sm md:text-base leading-relaxed max-w-2xl">
                                Tìm các khóa học phù hợp với bạn và giúp con đường học vấn của bạn thành công
                            </p>
                        </div>
                    </div>
                    <div class="p-6 md:p-8 lg:p-10 bg-white/72 backdrop-blur-sm flex items-center relative overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none">
                            <div class="absolute -top-8 right-10 h-20 w-20 rounded-none border border-lime-300/70 bg-white/50 rotate-12"></div>
                            <div class="absolute bottom-8 right-16 h-12 w-12 rounded-none bg-lime-400/35 rotate-45"></div>
                            <div class="absolute top-1/2 left-6 h-14 w-14 rounded-none border border-lime-300/60 bg-lime-100/50 -rotate-12"></div>
                        </div>
                        <div class="relative z-10 w-full grid gap-4 sm:grid-cols-2">
                            <div class="relative">
                                <select class="w-full bg-white text-slate-800 font-bold rounded-2xl px-5 py-3 outline-none appearance-none cursor-pointer text-sm shadow-inner border border-lime-200/90">
                                    <option value="" disabled selected>Độ tuổi</option>
                                    <option>Mầm non</option>
                                    <option>Tiểu học</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                            <div class="relative">
                                <select class="w-full bg-white text-slate-800 font-bold rounded-2xl px-5 py-3 outline-none appearance-none cursor-pointer text-sm shadow-inner border border-lime-200/90">
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
            <div class="hidden lg:flex lg:w-5/12 relative items-center justify-center lg:-mt-20" data-aos="fade-right" data-aos-duration="1200">
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
        <div class="fixed bottom-5 right-4 z-50 flex flex-col items-end gap-3 sm:bottom-6 sm:right-6">
            <a href="#hero-video" class="group flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-slate-800 text-white shadow-[0_10px_25px_rgba(15,23,42,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:bg-slate-700" aria-label="Đi tới hero video">
    			<i class="fa-solid fa-arrow-up text-[15px] sm:text-base transition-transform duration-300 group-hover:-translate-y-0.5"></i>
    		</a>
            <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-[#1877F2] text-white shadow-[0_10px_25px_rgba(24,119,242,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Facebook" style="animation-delay: 0s;">
    			<i class="fa-brands fa-facebook-f text-[15px] sm:text-base"></i>
    		</a>
            <a href="https://zalo.me/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-[#0068FF] text-white shadow-[0_10px_25px_rgba(0,104,255,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Zalo" style="animation-delay: 0.15s;">
    			<span class="text-[13px] sm:text-sm font-black leading-none tracking-tight">Z</span>
    		</a>
            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-gradient-to-br from-[#f09433] via-[#e6683c] via-[#dc2743] to-[#bc1888] text-white shadow-[0_10px_25px_rgba(220,39,67,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Instagram" style="animation-delay: 0.3s;">
    			<i class="fa-brands fa-instagram text-[15px] sm:text-base"></i>
    		</a>
            <a href="https://www.messenger.com/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-[#0084FF] text-white shadow-[0_10px_25px_rgba(0,132,255,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Messenger" style="animation-delay: 0.45s;">
    			<i class="fa-brands fa-facebook-messenger text-[15px] sm:text-base"></i>
    		</a>
    	</div>

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

    <section id="su-menh" class="pt-24 pb-12 md:pt-28 md:pb-16 relative overflow-hidden bg-transparent z-10">
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
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
                <h2 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-[#2e3192]">Sứ Mệnh <span class="text-red-600">Toàn Cầu</span></h2>
                <p class="mt-4 text-slate-600 font-medium max-w-2xl mx-auto text-lg">Kiến tạo thế hệ công dân làm chủ tương lai thông qua ngôn ngữ và kỹ năng toàn diện.</p>
            </div>
            
                <div class="relative w-full max-w-[1260px] mx-auto min-h-[620px] md:min-h-[940px] flex items-center justify-center orbit-wrapper mt-10 md:mt-2" style="transform: scale(0.95);">
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
                    <img src="assets/images/logo.jpg" alt="Trung tâm" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-blue-900/10 group-hover:bg-transparent transition-colors"></div>
                </div>

            </div>
        </div>
    </section>                            
								
    <section id="khoa-hoc" class="pt-8 pb-20 md:pt-10 md:pb-28 relative overflow-hidden bg-transparent">
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
            <?php if (empty($homeCourses)): ?>
                <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-14 text-center text-slate-500 font-medium">
                    Hiện chưa có khóa học nào trong hệ thống.
                </div>
            <?php else: ?>
                <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    <?php foreach ($homeCourses as $course): ?>
                        <?php
                        $courseTitle = (string) ($course['title'] ?? '');
                        $courseSlug = (string) ($course['slug'] ?? '');
                        $courseImage = (string) ($course['image'] ?? '');
                        $courseDesc = trim((string) ($course['short_desc'] ?? ''));
                        $courseLink = page_url('courses', ['course' => $courseSlug]);
                        ?>
                        <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white/90 shadow-lg border border-white transition-all duration-300 hover:-translate-y-3 hover:shadow-xl">
                            <div class="relative bg-blue-100 h-56 overflow-hidden">
                                <?php if ($courseImage !== ''): ?>
                                    <img src="<?= e($courseImage); ?>" alt="<?= e($courseTitle); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#e0f2fe] via-white to-[#ecfccb]">
                                        <div class="text-center text-[#2e3192]">
                                            <div class="mx-auto mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-white/90 shadow-lg ring-1 ring-blue-100">
                                                <i class="fa-solid fa-book-open text-3xl text-blue-500"></i>
                                            </div>
                                            <div class="text-xs font-black uppercase tracking-[0.22em] text-slate-500">Ảnh khóa học</div>
                                        </div>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <?php endif; ?>
                                <div class="absolute top-4 right-4 bg-white/90 text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                                    <span class="block text-[10px] uppercase font-bold opacity-90">Buổi học</span>
                                    <span class="block text-2xl font-black leading-none"><?= (int) ($course['total_sessions'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <div class="inline-flex w-fit rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-blue-700">
                                    <?= e((string) ($course['level'] ?? 'Khóa học')); ?>
                                </div>
                                <h3 class="mt-3 text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors"><?= e($courseTitle); ?></h3>
                                <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">
                                    <?= e($courseDesc !== '' ? $courseDesc : 'Chương trình học được xây dựng theo lộ trình rõ ràng, phù hợp cho từng học viên.'); ?>
                                </p>
                                <div class="mt-5 pt-4 border-t-2 border-slate-100 flex flex-col gap-4">
                                    <div class="flex items-end justify-between gap-4">
                                        <div>
                                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">Học phí từ</span>
                                            <span class="text-xl font-black text-[#2e3192]"><?= e((string) ($course['price'] ?? '0đ')); ?></span>
                                        </div>
                                        <div class="text-right text-xs font-semibold text-slate-400">
                                            <div><?= (int) ($course['roadmap_count'] ?? 0); ?> lộ trình</div>
                                            <div><?= (int) ($course['class_count'] ?? 0); ?> lớp học</div>
                                        </div>
                                    </div>
                                    <a href="<?= e($courseLink); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-4 py-3 text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                                        Xem chi tiết <i class="fa-solid fa-arrow-right text-xs"></i>
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

    <section id="ngoai-khoa" class="py-16 sm:py-24 bg-transparent relative overflow-hidden">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="mb-12 text-center md:text-left">
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-800 mb-2">
                    Có những mùa hè trôi qua, có những mùa hè con mang theo mãi mãi...
                </h2>
                <p class="text-slate-600 font-medium text-sm md:text-base">Khám phá các hoạt động ngoại khóa nổi bật trong tháng này.</p>
            </div>

            <?php $activities = $homeActivities ?? []; ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
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
                        <a href="<?= e($activityLink); ?>" class="group bg-white/80 backdrop-blur-md hover:bg-white rounded-3xl p-3 border border-white shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 cursor-pointer flex flex-col">
                            <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden mb-4 bg-slate-100">
                                <?php if ($activityImage !== ''): ?>
                                    <img src="<?= e($activityImage); ?>" alt="<?= e($activityTitle); ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-sky-100 via-white to-lime-100 text-slate-400">
                                        <i class="fa-solid fa-rocket text-3xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="px-2 pb-3 text-center flex-1 flex flex-col justify-start">
                                <h3 class="text-[#0d3b66] font-bold text-lg leading-tight mb-2 group-hover:text-blue-600 transition-colors"><?= e($activityTitle); ?></h3>
                                <p class="text-slate-500 text-sm font-medium leading-snug px-1">
                                    <?= e($activityDesc !== '' ? $activityDesc : 'Khám phá hoạt động ngoại khoá hấp dẫn dành cho học viên.'); ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="mt-8 flex justify-center">
                <a href="<?= e(page_url('activities-home')); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-6 py-3.5 text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                    Xem thêm <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </section>
	
    <section id="giao-vien" class="py-20 md:py-28 relative overflow-hidden bg-transparent z-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="mb-16 flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between" data-aos="fade-up">
            
            <div class="lg:w-1/2">
                <h2 class="text-3xl md:text-5xl font-black text-blue-800 leading-tight tracking-tight">
                    Hơn 3.100 Giáo viên và trợ giảng <br>
                    <span class="text-blue-600">Chuẩn quốc tế</span>
                </h2>
            </div>
            
            <div class="lg:w-1/2 flex items-start sm:items-center gap-4 sm:gap-6">
                <p class="text-slate-600 font-medium text-sm leading-relaxed border-l-2 border-slate-300 pl-4 sm:pl-6 text-left">
                    100% giáo viên nước ngoài được đảm bảo bởi International House - tổ chức uy tín hàng đầu thế giới về chuẩn đào tạo giáo viên nghiêm ngặt (như CELTA, DELTA)
                </p>
                <div class="w-14 h-14 sm:w-16 sm:h-16 shrink-0 bg-[#2e3192] rounded-full flex items-center justify-center text-white font-bold text-xl sm:text-2xl shadow-md">
                    ih
                </div>
            </div>
        </div>

        <div class="w-full" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper teacherSwiper pb-16">
                <div class="swiper-wrapper">
                    <?php
                    $teachers = array_slice($homeTeachers ?? [], 0, 5);
                    if (empty($teachers)):
                    ?>
                    <div class="swiper-slide h-auto">
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-white/80 p-8 text-center text-slate-500 font-medium">
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
                        <article class="flex flex-col gap-4 group cursor-pointer">
                            <div class="relative w-full aspect-[4/5] rounded-3xl overflow-hidden bg-slate-200 shadow-[0_10px_30px_rgba(0,0,0,0.05)]">
                                <img src="<?= e($teacherAvatar) ?>" alt="<?= e($teacherName) ?>" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                                <div class="absolute inset-0 bg-blue-900/0 group-hover:bg-blue-900/10 transition-colors duration-300"></div>
                            </div>
                            <div class="px-1 text-left">
                                <h4 class="text-lg md:text-xl font-extrabold text-slate-800 group-hover:text-blue-600 transition-colors"><?= e($teacherName) ?></h4>
                                <p class="text-sm font-medium text-slate-500 mt-1"><?= e($teacherRole) ?></p>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="swiper-pagination-teacher mt-8 flex justify-center"></div>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <a href="<?= e(page_url('dashboard-teacher')); ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2e3192] px-6 py-3.5 text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:bg-blue-600">
                Xem thêm <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

    </div>
</section>

	<!-- <section id="portal" class="relative py-20 md:py-32 overflow-hidden bg-transparent border-t border-white/30">
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
	</section> -->

	<section id="lien-he" class="relative py-20 md:py-32 overflow-hidden bg-transparent z-10">
        <!-- <section id="lien-he" class="relative bg-[#f4f7fb] overflow-hidden z-10"> -->
        <div class="mx-auto w-full max-w-[1400px] flex flex-col lg:flex-row">

            <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-16 lg:px-16 xl:px-32 z-10" data-aos="fade-right">
                
                <h2 class="text-[28px] md:text-[36px] font-bold text-[#185b9d] mb-8 tracking-tight">
                    Tư vấn và kiểm tra miễn phí
                </h2>

                <form class="flex flex-col w-full max-w-[420px]" action="#" method="post">
                    <?= csrf_input(); ?>

                    <input class="w-full bg-white text-slate-800 placeholder-slate-400 font-medium rounded-xl px-5 py-3.5 mb-4 outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 border border-slate-200 transition-all shadow-sm" 
                        type="text" name="full_name" placeholder="Họ tên phụ huynh" required>

                    <input class="w-full bg-white text-slate-800 placeholder-slate-400 font-medium rounded-xl px-5 py-3.5 mb-4 outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 border border-slate-200 transition-all shadow-sm" 
                        type="tel" name="phone" placeholder="Số điện thoại" required>

                    <div class="relative mb-4">
                        <select name="learning_mode" class="w-full bg-white text-slate-800 font-medium rounded-xl px-5 py-3.5 outline-none appearance-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 border border-slate-200 shadow-sm transition-all cursor-pointer" required>
                            <option value="" disabled selected hidden>Hình thức học</option>
                            <option value="offline">Học tại trung tâm</option>
                            <option value="online">Học trực tuyến</option>
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-slate-400 text-sm"></i>
                        </div>
                    </div>

                    <input class="w-full bg-white text-slate-800 placeholder-slate-400 font-medium rounded-xl px-5 py-3.5 mb-6 outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 border border-slate-200 transition-all shadow-sm" 
                        type="email" name="email" placeholder="Email">

                    <button type="submit" class="w-full bg-[#ffc107] hover:bg-[#ffb300] text-slate-900 font-semibold rounded-full py-3.5 px-6 text-lg transition-colors shadow-sm">
                        Đăng Ký
                    </button>

                    <p class="text-[11px] text-slate-600 mt-4 leading-snug">
                        * Thông tin được đồng ý tuân theo chính sách bảo mật và bảo vệ thông tin cá nhân.
                    </p>
                </form>
            </div>

            <div class="w-full lg:w-1/2 relative min-h-[400px] lg:min-h-[600px]" data-aos="fade-left">
                
                <img src="assets/images/tu_van_student.jpg" alt="Học sinh" class="absolute inset-0 w-full h-full object-cover object-top lg:object-center">

                <!-- Mờ bên trái -->
                <div class="absolute inset-y-0 left-0 w-24 md:w-32 bg-gradient-to-r from-[#f4f7fb] to-transparent pointer-events-none"></div>

                <!-- Mờ bên phải -->
                <div class="absolute inset-y-0 right-0 w-24 md:w-32 bg-gradient-to-l from-[#f4f7fb] to-transparent pointer-events-none"></div>

            </div>

        </div>
    </section>

   
</main>