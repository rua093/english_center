<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
$homeSuccess = get_flash('home_success');
$homeError = get_flash('home_error');
?>

<main class="font-jakarta bg-slate-50">
  <section id="trang-chu" class="relative bg-gradient-to-br from-white via-blue-50 to-cyan-50 pt-16 lg:pt-20 lg:pb-0 overflow-hidden border-b border-blue-100/50">

        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute w-[600px] h-[600px] bg-blue-400/20 blur-[120px] rounded-full -top-40 -left-40"></div>
            <div class="absolute w-[500px] h-[500px] bg-cyan-400/20 blur-[120px] rounded-full bottom-[-150px] right-[-150px]"></div>
        </div>

        <div class="relative z-10 max-w-[1450px] mx-auto px-4 sm:px-6 flex flex-col lg:flex-row gap-8 lg:gap-12">

            <div class="hidden lg:flex lg:w-5/12 relative items-end justify-center pt-10" data-aos="fade-right" data-aos-duration="1200">
                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 w-[90%] h-[70%] bg-gradient-to-t from-[#00d4ff]/40 to-transparent rounded-full blur-[80px] -z-10"></div>
                <img src="assets/images/student2.jpg" alt="Học sinh tiêu biểu" class="w-full max-w-[550px] object-contain relative z-10 drop-shadow-[0_20px_40px_rgba(0,0,0,0.3)]">
            </div>

            <div class="w-full lg:w-7/12 flex flex-col gap-5 md:gap-6 pb-16 lg:py-16 z-20">

                <div class="grid sm:grid-cols-2 gap-5 md:gap-6">
                    <div class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-br from-[#00d4ff] to-[#00b4d8] text-white shadow-[0_15px_30px_rgba(0,212,255,0.3)] overflow-hidden" data-aos="fade-up">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <h1 class="text-3xl md:text-4xl lg:text-4xl xl:text-5xl font-black leading-[1.15] text-[#2e3192] relative z-10 drop-shadow-sm text-left">
                            Khát Vọng<br>Là Khởi Đầu
                        </h1>
                    </div>

                    <div class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-bl from-[#2e3192] to-[#1a1c6b] text-white shadow-[0_15px_30px_rgba(46,49,146,0.3)] overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <h2 class="text-3xl md:text-4xl lg:text-4xl xl:text-5xl font-black leading-[1.15] relative z-10 drop-shadow-sm text-left">
                            Của Mọi<br>Thành Tựu
                        </h2>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5 md:gap-6">
                    <a href="#thi-thu" class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-br from-[#e62129] to-[#c11017] text-white shadow-[0_15px_30px_rgba(230,33,41,0.3)] overflow-hidden transition-all hover:-translate-y-2 h-48 md:h-56 flex flex-col justify-end" data-aos="fade-up" data-aos-delay="200">
                        <div class="absolute top-6 left-6 bg-white text-[#e62129] text-[10px] sm:text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-md">NEW</div>
                        <i class="fa-solid fa-chalkboard-user absolute -right-6 -top-6 text-[10rem] opacity-15 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-700"></i>
                        <h3 class="text-2xl md:text-3xl font-black relative z-10 leading-[1.2] w-11/12">Thi Thử Nhận Ngay Kết Quả</h3>
                    </a>

                    <a href="#tai-lieu" class="group relative rounded-[2rem] p-8 md:p-10 bg-gradient-to-br from-blue-700 to-blue-500 text-white shadow-[0_15px_30px_rgba(37,99,235,0.3)] overflow-hidden transition-all hover:-translate-y-2 h-48 md:h-56 flex flex-col justify-end" data-aos="fade-up" data-aos-delay="300">
                        <i class="fa-solid fa-book absolute -right-6 -bottom-6 text-[10rem] opacity-15 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-700"></i>
                        <h3 class="text-2xl md:text-3xl font-black relative z-10 leading-[1.2] w-11/12">Tài Liệu Học Tiếng Anh</h3>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-5" data-aos="fade-up" data-aos-delay="400">
                    <a href="#ovi" class="group relative rounded-2xl p-5 bg-white/80 backdrop-blur-xl border border-white shadow-lg transition-all hover:-translate-y-2 hover:shadow-xl hover:border-blue-300">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="fa-solid fa-users text-lg"></i>
                        </div>
                        <h4 class="font-bold text-[#2e3192] text-sm leading-tight">Đăng Nhập OVI</h4>
                    </a>

                    <a href="#he" class="group relative rounded-2xl p-5 bg-white/80 backdrop-blur-xl border border-white shadow-lg transition-all hover:-translate-y-2 hover:shadow-xl hover:border-cyan-300">
                        <div class="w-10 h-10 rounded-full bg-cyan-50 text-cyan-500 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-cyan-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-sparkles text-lg animate-pulse"></i>
                        </div>
                        <h4 class="font-bold text-[#2e3192] text-sm leading-tight">Tiếng Anh Hè 2026</h4>
                    </a>

                    <a href="#doanh-nghiep" class="group relative rounded-2xl p-5 bg-white/80 backdrop-blur-xl border border-white shadow-lg transition-all hover:-translate-y-2 hover:shadow-xl hover:border-amber-300">
                        <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-briefcase text-lg"></i>
                        </div>
                        <h4 class="font-bold text-[#2e3192] text-sm leading-tight">Doanh Nghiệp</h4>
                    </a>

                    <a href="#thanh-tich" class="group relative rounded-2xl p-5 bg-white/80 backdrop-blur-xl border border-white shadow-lg transition-all hover:-translate-y-2 hover:shadow-xl hover:border-emerald-300">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mb-3 group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-plane-departure text-lg"></i>
                        </div>
                        <h4 class="font-bold text-[#2e3192] text-sm leading-tight">Thành Tích</h4>
                    </a>
                </div>

            </div>
        </div>
    </section>

	<?php if ($homeUser && (string) ($homeUser['role'] ?? '') === 'teacher'): ?>
		<section id="lich-day-noi-bo" class="py-12 relative z-20 bg-white" aria-label="Widget giáo viên">
			<div class="mx-auto w-full max-w-6xl px-4 sm:px-6" data-aos="fade-up">
				<div class="rounded-[2rem] border border-blue-100 bg-gradient-to-br from-white to-blue-50/50 p-6 md:p-8 shadow-xl shadow-blue-900/5">
					<div class="flex items-center justify-between mb-6">
						<h2 class="text-2xl font-extrabold text-[#2e3192]">Lịch dạy 7 ngày tới</h2>
						<a class="text-sm font-bold text-[#00d4ff] hover:text-[#2e3192] transition-colors hover:underline" href="<?= e(page_url('profile')); ?>">Xem tất cả</a>
					</div>
					
					<?php if (empty($teacherSchedules)): ?>
						<div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white py-10 text-center">
							<i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
							<p class="text-slate-500 font-medium">Chưa có lịch dạy trong 7 ngày tới.</p>
						</div>
					<?php else: ?>
						<ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
							<?php foreach ($teacherSchedules as $schedule): ?>
								<li class="group rounded-2xl border border-slate-100 bg-white p-5 transition-all hover:border-blue-300 hover:shadow-lg hover:-translate-y-1">
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

	<!-- <section id="thong-ke" class="py-16 relative overflow-hidden bg-white border-y border-slate-100" aria-label="Uy tín và đối tác">
		<div class="mx-auto grid w-full max-w-6xl gap-6 px-4 sm:grid-cols-2 lg:grid-cols-4 sm:px-6">
			<div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 text-center shadow-sm hover:shadow-md hover:-translate-y-1 transition-all" data-aos="zoom-in" data-aos-delay="0"><strong class="block text-4xl font-black text-[#2e3192]">1.2K+</strong><span class="mt-2 block text-sm font-medium text-slate-500">Học viên theo học</span></div>
			<div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 text-center shadow-sm hover:shadow-md hover:-translate-y-1 transition-all" data-aos="zoom-in" data-aos-delay="100"><strong class="block text-4xl font-black text-[#2e3192]">95%</strong><span class="mt-2 block text-sm font-medium text-slate-500">Phụ huynh hài lòng</span></div>
			<div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 text-center shadow-sm hover:shadow-md hover:-translate-y-1 transition-all" data-aos="zoom-in" data-aos-delay="200"><strong class="block text-4xl font-black text-[#2e3192]">60+</strong><span class="mt-2 block text-sm font-medium text-slate-500">Giáo viên & Trợ giảng</span></div>
			<div class="rounded-2xl border border-slate-100 bg-slate-50 p-6 text-center shadow-sm hover:shadow-md hover:-translate-y-1 transition-all" data-aos="zoom-in" data-aos-delay="300"><strong class="block text-4xl font-black text-[#2e3192]">20+</strong><span class="mt-2 block text-sm font-medium text-slate-500">Đối tác học thuật</span></div>
		</div>
	</section> -->

	<section id="gioi-thieu" class="relative py-28 overflow-hidden bg-gradient-to-br from-blue-100 via-indigo-50 to-blue-200">
		<div class="absolute inset-0 opacity-15" 
			style="background-image: radial-gradient(#1e3a8a 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

		<div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-gradient-to-br from-blue-300 to-indigo-300 rounded-full blur-3xl opacity-50 mix-blend-multiply pointer-events-none"></div>
		<div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-gradient-to-tr from-cyan-200 to-blue-300 rounded-full blur-3xl opacity-50 mix-blend-multiply pointer-events-none"></div>

		<div class="relative z-10 max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 md:gap-24 items-center">

			<div class="relative" data-aos="fade-right">
				<div class="absolute -bottom-6 -left-6 w-full h-full rounded-[2.5rem] bg-blue-900/10 border border-blue-900/5"></div>
				
				<div class="relative rounded-[2.5rem] overflow-hidden shadow-[0_20px_50px_rgba(30,58,138,0.2)] group border-[6px] border-white">
					<img src="/assets/images/center.jpg" alt="Vị trí trung tâm"
						class="w-full h-[450px] object-cover transform group-hover:scale-105 transition duration-700 ease-in-out">
					<div class="absolute inset-0 bg-gradient-to-t from-blue-950/60 via-transparent to-transparent opacity-80"></div>
				</div>

				<div class="absolute -bottom-8 right-8 bg-indigo-50/95 backdrop-blur-md px-6 py-4 rounded-2xl shadow-[0_15px_40px_rgba(30,58,138,0.2)] border border-indigo-200 hover:-translate-y-1 transition-transform cursor-default z-20">
					<div class="flex items-center gap-4">
						<div class="relative flex h-12 w-12 items-center justify-center">
							<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-30"></span>
							<div class="relative w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white shadow-md">
								<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
							</div>
						</div>
						<div>
							<p class="text-[10px] uppercase tracking-widest text-indigo-600 font-black mb-0.5 flex items-center gap-1.5">
								<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
								Vị trí trung tâm
							</p>
							<h4 class="text-sm font-black text-blue-950">Phường Quảng Phú – Đà Nẵng</h4>
						</div>
					</div>
				</div>
			</div>

			<div class="space-y-8" data-aos="fade-left">
				
				<div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-blue-100 shadow-sm">
					<span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
					<span class="text-blue-800 text-xs font-bold uppercase tracking-wider">Về Nhuệ Minh</span>
				</div>

				<h2 class="text-4xl md:text-5xl font-extrabold leading-[1.15] text-blue-950">
					Nâng tầm ngoại ngữ,<br>
					<span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">
						Khơi nguồn tự tin
					</span>
				</h2>

				<p class="text-lg text-slate-600 leading-relaxed">
					Trung tâm ngoại ngữ hiện đại với không gian học tập truyền cảm hứng, 
					giúp học viên phát triển toàn diện kỹ năng và sự tự tin trong giao tiếp.
				</p>

				<div class="grid sm:grid-cols-2 gap-5 mt-8">
					
					<div class="bg-blue-50/90 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-blue-200 hover:-translate-y-2 hover:shadow-xl hover:bg-blue-100/90 transition-all duration-300 group">
						<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center mb-5 shadow-md group-hover:scale-110 transition-transform">
							<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
						</div>
						
						<h4 class="font-extrabold text-blue-950 mb-2 flex items-center gap-2 text-lg">
							Vị trí thuận lợi
						</h4>
						<p class="text-sm text-slate-600 leading-relaxed font-medium">Dễ dàng di chuyển, gần khu dân cư & trường học</p>
					</div>

					<div class="bg-teal-50/90 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-teal-200 hover:-translate-y-2 hover:shadow-xl hover:bg-teal-100/90 transition-all duration-300 group">
						<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-400 to-teal-600 text-white flex items-center justify-center mb-5 shadow-md group-hover:scale-110 transition-transform">
							<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
						</div>
						
						<h4 class="font-extrabold text-teal-950 mb-2 flex items-center gap-2 text-lg">
							Môi trường an toàn
						</h4>
						<p class="text-sm text-slate-600 leading-relaxed font-medium">Không gian học tập hiện đại, thân thiện, an ninh tốt</p>
					</div>

				</div>

				<div class="pt-2">
					<a href="#lien-he" class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-[0_10px_20px_rgba(37,99,235,0.3)] transition-all hover:scale-105 hover:shadow-[0_15px_25px_rgba(37,99,235,0.4)]">
						Khám phá ngay
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
					</a>
				</div>

			</div>

		</div>
	</section>

    <section id="su-menh" class="py-24 relative overflow-hidden bg-gradient-to-b from-sky-100 via-white to-white">
		<div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
			<div class="text-center mb-16" data-aos="fade-up">
				<h2 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-[#2e3192]">Sứ Mệnh <span class="text-red-600">Toàn Cầu</span></h2>
				<p class="mt-4 text-slate-600 font-medium max-w-2xl mx-auto text-lg">Kiến tạo thế hệ công dân làm chủ tương lai thông qua ngôn ngữ và kỹ năng toàn diện.</p>
			</div>
			
			<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
				<div class="bg-amber-50/100 p-8 rounded-[2.5rem] shadow-lg border border-amber-100 hover:-translate-y-3 hover:shadow-xl hover:shadow-amber-200/50 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="0">
					<div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-3xl mb-6 group-hover:bg-amber-500 group-hover:text-white group-hover:scale-110 transition-all">💡</div>
					<h4 class="text-xl font-black mb-3 text-[#2e3192]">Sáng Tạo</h4>
					<p class="text-sm leading-relaxed text-slate-600 font-medium">Xây dựng không gian học ngoại ngữ thân thiện, hiệu quả và đầy cảm hứng.</p>
				</div>
				
				<div class="bg-blue-50/100 p-8 rounded-[2.5rem] shadow-lg border border-blue-100 hover:-translate-y-3 hover:shadow-xl hover:shadow-blue-200/50 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="100">
					<div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl mb-6 group-hover:bg-blue-500 group-hover:text-white group-hover:scale-110 transition-all">🗣️</div>
					<h4 class="text-xl font-black mb-3 text-[#2e3192]">Tự Tin</h4>
					<p class="text-sm leading-relaxed text-slate-600 font-medium">Làm chủ tiếng Anh từ những câu đơn giản đến hội thoại thực tế đời sống.</p>
				</div>
				
				<div class="bg-emerald-50/100 p-8 rounded-[2.5rem] shadow-lg border border-emerald-100 hover:-translate-y-3 hover:shadow-xl hover:shadow-emerald-200/50 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="200">
					<div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-6 group-hover:bg-emerald-500 group-hover:text-white group-hover:scale-110 transition-all">🎯</div>
					<h4 class="text-xl font-black mb-3 text-[#2e3192]">Toàn Diện</h4>
					<p class="text-sm leading-relaxed text-slate-600 font-medium">Đào tạo bài bản 4 kỹ năng Nghe – Nói – Đọc – Viết cho mọi lứa tuổi.</p>
				</div>
				
				<div class="bg-purple-50/100 p-8 rounded-[2.5rem] shadow-lg border border-purple-100 hover:-translate-y-3 hover:shadow-xl hover:shadow-purple-200/50 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="300">
					<div class="w-16 h-16 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-3xl mb-6 group-hover:bg-purple-500 group-hover:text-white group-hover:scale-110 transition-all">🤝</div>
					<h4 class="text-xl font-black mb-3 text-[#2e3192]">Cam Kết</h4>
					<p class="text-sm leading-relaxed text-slate-600 font-medium">Theo sát lộ trình, khơi dậy niềm yêu thích với phương châm "Dám nói".</p>
				</div>
			</div>
		</div>
	</section>						
								
	<!-- <section id="khoa-hoc" class="py-20 md:py-28 relative overflow-hidden bg-white"> -->
	<section id="khoa-hoc" class="py-20 md:py-28 relative overflow-hidden bg-sky-100">
    <div class="absolute inset-0 bg-gradient-to-b from-white via-slate-50 to-white pointer-events-none"></div>

    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="mb-14 text-center" data-aos="fade-up">
            <h2 class="text-3xl md:text-5xl font-black text-[#2e3192] uppercase tracking-tight">
                KHOÁ HỌC DÀNH CHO <span class="inline-block mt-2 md:mt-0 rounded-full bg-red-600 px-6 py-2 text-white shadow-lg transform -rotate-2">MỌI MỤC TIÊU</span>
            </h2>
            <p class="mt-6 text-lg text-slate-600 max-w-3xl mx-auto font-medium">
                Dễ dàng lựa chọn khóa học tiếng Anh phù hợp cho riêng mình với chương trình học đa dạng, được thiết kế phù hợp với nhu cầu và trình độ thực tế.
            </p>
        </div>

        <div class="rounded-[3rem] bg-sky-200/80 backdrop-blur-sm border border-sky-300 p-6 md:p-8 lg:p-10 shadow-[0_15px_40px_rgba(30,58,138,0.06)]" data-aos="zoom-in">
            <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                
                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-lg border border-slate-100 transition-all duration-300 hover:-translate-y-3 hover:shadow-[0_20px_40px_rgba(30,58,138,0.12)]">
                    <div class="relative bg-blue-100 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=600&h=400" alt="Giao tiếp" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-4 right-4 bg-white text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Level</span>
                            <span class="block text-2xl font-black leading-none">1-2</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">Giao tiếp<br>Phản xạ</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Phát triển phản xạ nghe nói tự nhiên và tự tin giao tiếp trong các tình huống thực tế sau 8-12 tuần.</p>
                        <div class="mt-5 pt-4 border-t-2 border-slate-50 flex justify-between items-end">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">Học phí từ</span>
                                <span class="text-xl font-black text-[#2e3192]">3.200.000đ</span>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-lg border border-slate-100 transition-all duration-300 hover:-translate-y-3 hover:shadow-[0_20px_40px_rgba(30,58,138,0.12)]">
                    <div class="relative bg-indigo-100 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600&h=400" alt="IELTS Foundation" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-4 right-4 bg-white text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Target</span>
                            <span class="block text-2xl font-black leading-none">4.5+</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">IELTS<br>Foundation</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Củng cố nền tảng ngữ pháp, từ vựng và làm quen với format 4 kỹ năng trước khi bước vào giai đoạn luyện đề.</p>
                        <div class="mt-5 pt-4 border-t-2 border-slate-50 flex justify-between items-end">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">Học phí từ</span>
                                <span class="text-xl font-black text-[#2e3192]">5.800.000đ</span>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-lg border border-slate-100 transition-all duration-300 hover:-translate-y-3 hover:shadow-[0_20px_40px_rgba(30,58,138,0.12)]">
                    <div class="relative bg-blue-50 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&q=80&w=600&h=400" alt="IELTS Intensive" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-4 right-4 bg-white text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Target</span>
                            <span class="block text-2xl font-black leading-none">6.5+</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">IELTS<br>Intensive</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Tối ưu chiến lược làm bài, sửa lỗi sai trực tiếp 1-1 và nâng band điểm thần tốc với lộ trình cá nhân hóa.</p>
                        <div class="mt-5 pt-4 border-t-2 border-slate-50 flex justify-between items-end">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">Học phí từ</span>
                                <span class="text-xl font-black text-[#2e3192]">8.900.000đ</span>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-lg border border-slate-100 transition-all duration-300 hover:-translate-y-3 hover:shadow-[0_20px_40px_rgba(30,58,138,0.12)]">
                    <div class="relative bg-slate-100 h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=600&h=400" alt="Tiếng Anh Doanh Nghiệp" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="absolute top-4 right-4 bg-white text-[#2e3192] rounded-2xl px-3 py-2 text-center shadow-md backdrop-blur-sm">
                            <span class="block text-[10px] uppercase font-bold opacity-90">Cho</span>
                            <span class="block text-xl font-black leading-none mt-1">Người lớn</span>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight group-hover:text-blue-600 transition-colors">Business<br>English</h3>
                        <p class="mt-3 text-sm font-medium text-slate-500 flex-1 leading-relaxed">Tiếng Anh ứng dụng trong môi trường công sở: Viết email, thuyết trình, đàm phán và phỏng vấn.</p>
                        <div class="mt-5 pt-4 border-t-2 border-slate-50 flex justify-between items-end">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">Học phí từ</span>
                                <span class="text-xl font-black text-[#2e3192]">6.500.000đ</span>
                            </div>
                        </div>
                    </div>
                </article>

            </div>
        </div>
    </div>
</section>
	
    <section id="giao-vien" class="py-20 md:py-28 relative overflow-hidden bg-slate-50 border-t border-slate-200">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 relative z-10">
        
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl md:text-5xl font-black text-[#2e3192] uppercase leading-tight tracking-tight">
                Hơn 3.100 Giáo viên và trợ giảng <br>
                <span class="relative inline-block text-white px-8 py-2 mt-4">
                    <span class="relative z-10">Chuẩn quốc tế</span>
                    <div class="absolute inset-0 bg-red-600 rounded-full -rotate-1 shadow-lg"></div>
                </span>
            </h2>
        </div>

        <div class="bg-[#2e3192] rounded-t-[3rem] p-8 md:p-12 shadow-xl" data-aos="fade-up">
            <div class="grid lg:grid-cols-3 gap-8 items-center">
                <div class="lg:col-span-2">
                    <p class="text-white font-extrabold text-xl md:text-2xl leading-snug">
                        Đội ngũ giảng dạy chuẩn quốc tế lớn nhất Việt Nam sẵn sàng cùng Phụ huynh & Học viên chinh phục mọi mục tiêu.
                    </p>
                </div>
                <div class="flex justify-center lg:justify-end">
                    <div class="flex -space-x-4">
                        <div class="w-14 h-14 rounded-full border-4 border-[#2e3192] bg-slate-200 overflow-hidden shadow-lg"><img src="https://i.pravatar.cc/100?u=1" alt="T1"></div>
                        <div class="w-14 h-14 rounded-full border-4 border-[#2e3192] bg-slate-200 overflow-hidden shadow-lg"><img src="https://i.pravatar.cc/100?u=2" alt="T2"></div>
                        <div class="w-14 h-14 rounded-full border-4 border-[#2e3192] bg-slate-200 overflow-hidden shadow-lg"><img src="https://i.pravatar.cc/100?u=3" alt="T3"></div>
                        <div class="w-14 h-14 rounded-full border-4 border-[#2e3192] bg-white text-[#2e3192] flex items-center justify-center text-xs font-black shadow-lg">+3k</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border-x border-b border-slate-200 rounded-b-[3rem] p-6 md:p-10 shadow-xl" data-aos="fade-up">
            <div class="swiper teacherSwiper pb-12">
                <div class="swiper-wrapper">
                    
                    <?php 
                    $sample_teachers = [
                        ['name' => 'ANNE KENTHILL ELOISE', 'exp' => '+8', 'img' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400'],
                        ['name' => 'SCOTT DAVID PORTER', 'exp' => '+12', 'img' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400'],
                        ['name' => 'PETER RICHARD HARBISON', 'exp' => '+10', 'img' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400'],
                        ['name' => 'SARAH JENNER', 'exp' => '+6', 'img' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400'],
                        ['name' => 'MICHAEL VANCE', 'exp' => '+15', 'img' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=400'],
                    ];
                    foreach ($sample_teachers as $t): 
                    ?>
                    <div class="swiper-slide h-auto">
                        <article class="h-full flex flex-col rounded-[2rem] bg-slate-50 border-4 border-slate-100 hover:border-blue-400 overflow-hidden transition-all duration-500 hover:-translate-y-3 hover:shadow-2xl group">
                            <div class="relative aspect-[3/4] overflow-hidden bg-slate-200">
                                <img src="<?= $t['img'] ?>" alt="<?= $t['name'] ?>" class="w-full h-full object-cover grayscale opacity-90 group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#2e3192]/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="absolute top-4 right-4 bg-red-600 text-white rounded-xl p-2 text-center min-w-[52px] shadow-lg border-2 border-white/20">
                                    <span class="block text-xl font-black leading-none"><?= $t['exp'] ?></span>
                                    <span class="block text-[7px] font-bold uppercase leading-tight mt-1">Năm <br> kinh nghiệm</span>
                                </div>
                            </div>
                            <div class="p-5 text-center flex-1 flex flex-col justify-center bg-white group-hover:bg-blue-50 transition-colors">
                                <span class="text-[10px] font-black uppercase text-blue-600 tracking-widest">Giáo viên</span>
                                <h4 class="text-sm font-black text-[#2e3192] mt-1 uppercase line-clamp-2"><?= $t['name'] ?></h4>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>

                </div>
                <div class="swiper-pagination-teacher mt-10"></div>
            </div>

            <div class="flex justify-center gap-4">
                <button class="teacher-prev w-12 h-12 rounded-full border-2 border-[#2e3192] text-[#2e3192] hover:bg-[#2e3192] hover:text-white hover:scale-110 transition-all shadow-sm"><i class="fa-solid fa-arrow-left"></i></button>
                <button class="teacher-next w-12 h-12 rounded-full border-2 border-[#2e3192] text-[#2e3192] hover:bg-[#2e3192] hover:text-white hover:scale-110 transition-all shadow-sm"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>

    </div>
</section>

	<section id="portal" class="relative py-20 md:py-32 overflow-hidden bg-white border-t border-slate-100">
		<div class="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden z-0">
			<div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-blue-100/50 blur-[100px]"></div>
			<div class="absolute bottom-10 right-10 w-80 h-80 rounded-full bg-cyan-100/50 blur-[100px]"></div>
		</div>

		<div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6">
			<div class="mb-16 text-center max-w-2xl mx-auto" data-aos="fade-down">
				<h2 class="text-3xl md:text-4xl font-black text-[#2e3192] uppercase tracking-tight">
					TÍNH năng nội bộ <span class="text-blue-500">mạnh mẽ</span>
				</h2>
				<p class="mt-4 text-slate-500 text-lg font-medium italic">"Mọi công cụ bạn cần đều nằm gọn trong tầm tay"</p>
			</div>

			<div class="grid gap-8 grid-cols-1 md:grid-cols-3 items-center">
				<article class="group rounded-[2rem] bg-white p-8 shadow-xl border border-slate-100 transition-all duration-500 hover:-translate-y-3 hover:shadow-2xl relative" data-aos="fade-up" data-aos-delay="0">
					<div class="relative z-10 text-center">
						<div class="mx-auto relative mb-8 h-24 w-24">
							<div class="absolute inset-0 bg-blue-100 rounded-3xl rotate-6 transition-transform group-hover:rotate-12 group-hover:bg-blue-200"></div>
							<div class="absolute inset-0 bg-white rounded-3xl shadow-md overflow-hidden border border-slate-100">
								<img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=200" alt="Học tập" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
							</div>
							<div class="absolute -bottom-2 -right-2 bg-[#2e3192] text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
								<i class="fa-solid fa-graduation-cap text-[10px]"></i>
							</div>
						</div>
						
						<h3 class="text-xl font-extrabold text-[#2e3192]">Học tập thông minh</h3>
						<p class="mt-4 text-slate-600 leading-relaxed font-medium">Theo dõi lộ trình, nộp bài tập và tương tác trực tiếp với giáo viên qua cổng trực tuyến.</p>
					</div>
				</article>

                <article class="group rounded-[2rem] bg-[#2e3192] p-8 shadow-2xl text-center relative transform md:-translate-y-6 transition-all duration-500 hover:-translate-y-8 border-4 border-white" data-aos="fade-up" data-aos-delay="100">
					<div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-blue-400 text-white text-[10px] font-black uppercase tracking-widest px-6 py-2 rounded-full shadow-lg z-20 whitespace-nowrap">
						Dành cho quản trị
					</div>

					<div class="relative z-10">
						<div class="mx-auto relative mb-8 h-28 w-28">
							<div class="absolute inset-0 bg-white/20 backdrop-blur-md rounded-[2rem] -rotate-6 transition-transform duration-500 group-hover:-rotate-12 group-hover:scale-105"></div>
							<div class="absolute inset-0 bg-white rounded-[2rem] shadow-xl overflow-hidden border-2 border-blue-400">
								<img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=200" alt="Quản lý" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
							</div>
							<div class="absolute -bottom-2 -right-2 bg-blue-400 text-white w-10 h-10 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
								<i class="fa-solid fa-users-gear text-sm"></i>
							</div>
						</div>
						
						<h3 class="text-xl font-extrabold text-white">Quản lý toàn diện</h3>
						<p class="mt-4 text-blue-100 font-medium leading-relaxed">Điều phối lớp học, điểm danh và phê duyệt yêu cầu học vụ chỉ với một chạm.</p>
					</div>
				</article>

                <article class="group rounded-[2rem] bg-white p-8 shadow-xl border border-slate-100 transition-all duration-500 hover:-translate-y-3 hover:shadow-2xl relative" data-aos="fade-up" data-aos-delay="200">
					<div class="relative z-10 text-center">
						<div class="mx-auto relative mb-8 h-24 w-24">
							<div class="absolute inset-0 bg-blue-100 rounded-3xl rotate-6 transition-transform group-hover:rotate-12 group-hover:bg-blue-200"></div>
							<div class="absolute inset-0 bg-white rounded-3xl shadow-md overflow-hidden border border-slate-100">
								<img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&q=80&w=200" alt="Thanh toán" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
							</div>
							<div class="absolute -bottom-2 -right-2 bg-[#2e3192] text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
								<i class="fa-solid fa-file-invoice-dollar text-[10px]"></i>
							</div>
						</div>

						<h3 class="text-xl font-extrabold text-[#2e3192]">Thanh toán tiện lợi</h3>
						<p class="mt-4 text-slate-600 leading-relaxed font-medium">Minh bạch hóa học phí, lưu trữ lịch sử giao dịch và xuất hóa đơn điện tử tức thì.</p>
					</div>
				</article>

			</div>
		</div>
	</section>

	<section id="lien-he" class="relative py-20 md:py-32 overflow-hidden bg-gradient-to-b from-white via-indigo-50/60 to-blue-100/50 isolate border-t border-slate-100">
		
        <div class="absolute inset-0 z-0 opacity-[0.03]" style="background-image: radial-gradient(#2e3192 2px, transparent 2px); background-size: 32px 32px;"></div>

        <div class="absolute inset-0 pointer-events-none -z-10 overflow-hidden">
            <div class="absolute -top-32 -left-32 w-[600px] h-[600px] rounded-full bg-gradient-to-br from-[#00d4ff]/20 to-transparent blur-[120px]"></div>
            <div class="absolute -bottom-32 -right-32 w-[600px] h-[600px] rounded-full bg-gradient-to-tl from-[#2e3192]/15 to-transparent blur-[120px]"></div>
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[800px] h-[300px] bg-white/60 blur-[80px]"></div>
        </div>

		<div class="relative z-10 mx-auto w-full max-w-5xl px-4 sm:px-6">
			
			<div class="mb-14 text-center" data-aos="fade-up">
				<h2 class="text-3xl md:text-5xl font-black text-[#2e3192] uppercase tracking-tight flex items-center justify-center flex-wrap gap-3">
					ĐĂNG KÝ NHẬN 
					<span class="bg-gradient-to-r from-red-600 to-red-500 text-white px-6 py-2 rounded-full inline-flex items-center gap-2 relative shadow-[0_10px_20px_rgba(220,38,38,0.3)]">
						TƯ VẤN NGAY
						<i class="fa-solid fa-certificate text-yellow-300 absolute -top-4 -right-6 text-3xl opacity-90 animate-spin-slow hidden md:block" style="animation: spin 8s linear infinite;"></i>
					</span>
				</h2>
				<p class="mt-5 text-slate-600 font-medium text-lg italic">Đội ngũ chuyên viên sẽ liên hệ và hỗ trợ bạn trong thời gian sớm nhất</p>
			</div>

			<div class="grid md:grid-cols-3 gap-8 items-start">
				
                <div class="md:col-span-2 bg-gradient-to-br from-[#2e3192] to-[#1a1c6b] rounded-[2.5rem] p-8 md:p-10 shadow-[0_25px_50px_rgba(46,49,146,0.25)] border border-blue-400/20 relative" data-aos="fade-right">
                    
                    <div class="absolute top-0 left-0 w-full h-1/2 bg-white/5 skew-y-[-10deg] transform origin-top-left pointer-events-none rounded-t-[2.5rem]"></div>

					<form class="grid gap-5 relative z-20" action="#" method="post">
						<?= csrf_input(); ?>
						
						<div>
							<input class="w-full bg-white/95 text-[#2e3192] placeholder-slate-400 font-semibold rounded-full px-6 py-4 outline-none focus:bg-white focus:ring-4 focus:ring-[#00d4ff]/40 transition-all shadow-inner" type="text" name="full_name" placeholder="Họ Và Tên *" required>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
							<input class="w-full bg-white/95 text-[#2e3192] placeholder-slate-400 font-semibold rounded-full px-6 py-4 outline-none focus:bg-white focus:ring-4 focus:ring-[#00d4ff]/40 transition-all shadow-inner" type="tel" name="phone" placeholder="Điện thoại *" required>
							<div class="relative">
								<select name="learning_mode" class="w-full bg-white/95 text-[#2e3192] font-semibold rounded-full px-6 py-4 outline-none appearance-none focus:bg-white focus:ring-4 focus:ring-[#00d4ff]/40 transition-all shadow-inner cursor-pointer" required>
									<option value="" disabled selected hidden>Hình thức học *</option>
									<option value="offline">Học tại trung tâm</option>
									<option value="online">Học trực tuyến</option>
								</select>
								<div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
									<div class="bg-[#2e3192] text-white rounded-full w-8 h-8 flex items-center justify-center text-sm shadow-md"><i class="fa-solid fa-chevron-down"></i></div>
								</div>
							</div>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
							<input class="w-full bg-white/95 text-[#2e3192] placeholder-slate-400 font-semibold rounded-full px-6 py-4 outline-none focus:bg-white focus:ring-4 focus:ring-[#00d4ff]/40 transition-all shadow-inner" type="email" name="email" placeholder="Địa chỉ email">
							<div class="relative">
								<select name="city" class="w-full bg-white/95 text-[#2e3192] font-semibold rounded-full px-6 py-4 outline-none appearance-none focus:bg-white focus:ring-4 focus:ring-[#00d4ff]/40 transition-all shadow-inner cursor-pointer" required>
									<option value="" disabled selected hidden>Tỉnh/ Thành phố *</option>
									<option value="HCM">Hồ Chí Minh</option>
									<option value="HN">Hà Nội</option>
									<option value="BD">Bình Dương</option>
								</select>
								<div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
									<div class="bg-[#2e3192] text-white rounded-full w-8 h-8 flex items-center justify-center text-sm shadow-md"><i class="fa-solid fa-chevron-down"></i></div>
								</div>
							</div>
						</div>

						<div class="relative">
							<select name="course" class="w-full bg-white/95 text-[#2e3192] font-semibold rounded-full px-6 py-4 outline-none appearance-none focus:bg-white focus:ring-4 focus:ring-[#00d4ff]/40 transition-all shadow-inner cursor-pointer" required>
								<option value="" disabled selected hidden>Khóa học *</option>
								<option value="giao-tiep">Giao tiếp Phản xạ</option>
								<option value="ielts">Luyện thi IELTS</option>
								<option value="business">Tiếng Anh Doanh Nghiệp</option>
							</select>
							<div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
								<div class="bg-[#2e3192] text-white rounded-full w-8 h-8 flex items-center justify-center text-sm shadow-md"><i class="fa-solid fa-chevron-down"></i></div>
							</div>
						</div>

                        <button type="submit" class="mt-4 group w-full bg-gradient-to-r from-blue-600 to-[#00d4ff] hover:from-blue-700 hover:to-cyan-500 text-white font-black rounded-full py-5 px-4 flex justify-between items-center transition-all shadow-[0_10px_20px_rgba(0,212,255,0.3)] hover:shadow-[0_15px_30px_rgba(0,212,255,0.4)] hover:-translate-y-1">
							<span class="flex-1 text-center text-xl uppercase tracking-widest drop-shadow-sm">Tư vấn ngay</span>
							<div class="bg-white text-[#2e3192] rounded-full w-12 h-12 flex items-center justify-center text-2xl transition-transform duration-300 group-hover:translate-x-2 shadow-md">
								<i class="fa-solid fa-arrow-right-long transform -rotate-45"></i>
							</div>
						</button>
					</form>
				</div>

                <div class="flex flex-col gap-6" data-aos="fade-left">
					<div class="bg-white/90 backdrop-blur-md rounded-[2rem] p-8 border border-white shadow-[0_20px_40px_rgba(30,58,138,0.1)] flex flex-col gap-5 hover:-translate-y-1 transition-transform">
						<h3 class="text-[#2e3192] font-extrabold text-center text-lg mb-1 uppercase tracking-wide">Hỗ trợ trực tuyến</h3>
						<a href="#" class="group bg-slate-50 text-[#2e3192] rounded-full py-4 px-4 flex justify-between items-center font-bold transition-all hover:bg-blue-50 hover:text-blue-600 shadow-sm border border-slate-100 hover:border-blue-200">
							<span class="pl-4 text-lg">NHẮN TIN</span>
							<div class="bg-[#2e3192] text-white rounded-full w-11 h-11 flex items-center justify-center text-xl shadow-inner group-hover:bg-blue-600 transition-colors"><i class="fa-brands fa-facebook-messenger"></i></div>
						</a>
						
						<a href="tel:02873083333" class="group bg-red-50 text-red-600 rounded-full py-4 px-4 flex justify-between items-center font-bold transition-all hover:bg-red-600 hover:text-white shadow-sm border border-red-100 hover:border-transparent">
							<span class="pl-4 text-lg drop-shadow-sm">HOTLINE</span>
							<div class="bg-red-600 text-white rounded-full w-11 h-11 flex items-center justify-center text-xl shadow-inner group-hover:bg-white group-hover:text-red-600 transition-colors"><i class="fa-solid fa-phone-volume transform -rotate-12 group-hover:animate-wiggle"></i></div>
						</a>
					</div>
					
					<div class="bg-gradient-to-br from-[#2e3192] to-[#1a1c6b] rounded-[2rem] p-7 text-white text-center shadow-[0_20px_40px_rgba(46,49,146,0.2)] hover:-translate-y-1 transition-transform border border-blue-400/20">
						<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/10 mb-4 shadow-inner border border-white/5">
                            <i class="fa-solid fa-headset text-3xl text-[#00d4ff]"></i>
                        </div>
						<p class="text-sm font-medium text-blue-50 leading-relaxed">Tư vấn miễn phí 24/7 qua tất cả các kênh liên lạc.</p>
					</div>
				</div>
			</div>

			<div class="grid gap-3" data-role-panels>
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
	</section>
</main>