<?php
$homeUser = auth_user();
$studentProgress = $homeWidgets['student_progress'] ?? null;
$teacherSchedules = $homeWidgets['teacher_schedules'] ?? [];
$homeSuccess = get_flash('home_success');
$homeError = get_flash('home_error');
?>

<main class="font-jakarta">
	<section class="relative bg-slate-50 py-16 md:py-24 lg:py-32 overflow-hidden">
    
    <section class="relative bg-slate-50 py-16 md:py-24 lg:py-32 overflow-hidden">
    
    <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 relative">

        <div class="hidden lg:flex absolute bottom-0 left-1/2 transform -translate-x-1/2 z-30 h-[95%] xl:h-[105%] items-end pointer-events-none drop-shadow-[0_20px_40px_rgba(0,0,0,0.4)]">
            <img src="assets/images/student2.jpg" alt="Học sinh tiêu biểu" class="h-full w-auto object-contain">
        </div>

        <div class="relative z-20 flex flex-col gap-5 xl:gap-6">

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_420px_1fr] gap-5 xl:gap-6">
                <div class="bg-[#00d4ff] p-10 md:p-12 lg:p-16 rounded-[2.5rem] shadow-xl flex items-center justify-center lg:justify-start">
                    <h1 class="text-5xl md:text-6xl lg:text-[4.5rem] font-black text-[#2e3192] leading-[1.1] text-center lg:text-left drop-shadow-sm">
                        Khát Vọng<br>Là Khởi Đầu
                    </h1>
                </div>
                
                <div class="hidden lg:block"></div>

                <div class="bg-[#2e3192] p-10 md:p-12 lg:p-16 rounded-[2.5rem] shadow-xl flex items-center justify-center lg:justify-end">
                    <h2 class="text-5xl md:text-6xl lg:text-[4.5rem] font-black text-white leading-[1.1] text-center lg:text-right uppercase drop-shadow-sm">
                        Của Mọi<br>Thành Tựu
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_420px_1fr] gap-5 xl:gap-6">
                
                <div class="flex flex-col gap-5 xl:gap-6">
                    <a href="#thi-thu" class="group relative bg-[#e62129] rounded-[2.5rem] p-8 md:p-10 overflow-hidden flex flex-col justify-end transition-all hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(230,33,41,0.4)] shadow-xl h-56 md:h-64 lg:h-72">
                        <div class="absolute top-6 left-6 lg:top-8 lg:left-8 bg-white text-[#e62129] text-xs lg:text-sm font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-md">NEW</div>
                        
                        <div class="absolute -right-6 -top-6 opacity-15 text-white transition-transform duration-700 group-hover:scale-125 group-hover:rotate-6">
                            <i class="fa-solid fa-chalkboard-user text-[12rem] md:text-[16rem]"></i>
                        </div>
                        
                        <h3 class="text-3xl md:text-4xl font-black text-white relative z-10 w-4/5 leading-[1.2]">Thi Thử Nhận Ngay Kết Quả</h3>
                    </a>

                    <div class="grid grid-cols-2 gap-5 xl:gap-6">
                        <a href="#ovi" class="group relative bg-[#ffb600] rounded-[2rem] lg:rounded-[2.5rem] p-6 lg:p-8 overflow-hidden flex flex-col justify-end transition-all hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(255,182,0,0.4)] shadow-lg h-44 md:h-52">
                            <div class="absolute -right-8 -bottom-6 opacity-15 text-white transition-transform duration-700 group-hover:scale-125">
                                <i class="fa-solid fa-users text-[8rem] md:text-[10rem]"></i>
                            </div>
                            <h4 class="text-lg md:text-xl lg:text-2xl font-bold text-white relative z-10 w-full">Đăng Nhập OVI</h4>
                        </a>
                        
                        <a href="#he" class="group relative bg-[#39b581] rounded-[2rem] lg:rounded-[2.5rem] p-6 lg:p-8 overflow-hidden flex flex-col justify-end transition-all hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(57,181,129,0.4)] shadow-lg h-44 md:h-52">
                            <i class="fa-solid fa-sparkles absolute top-6 left-6 text-2xl text-white opacity-80 animate-pulse"></i>
                            <div class="absolute -right-6 -bottom-6 opacity-15 text-white transition-transform duration-700 group-hover:scale-125 group-hover:-rotate-12">
                                <i class="fa-solid fa-suitcase-rolling text-[8rem] md:text-[10rem]"></i>
                            </div>
                            <h4 class="text-lg md:text-xl lg:text-2xl font-bold text-white relative z-10 w-full">Tiếng Anh Hè 2026</h4>
                        </a>
                    </div>
                </div>

                <div class="hidden lg:block"></div>

                <div class="flex flex-col gap-5 xl:gap-6">
                    <a href="#tai-lieu" class="group relative bg-[#e62129] rounded-[2.5rem] p-8 md:p-10 overflow-hidden flex flex-col justify-end transition-all hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(230,33,41,0.4)] shadow-xl h-56 md:h-64 lg:h-72">
                        <div class="absolute -right-12 -bottom-10 opacity-15 text-white transition-transform duration-700 group-hover:scale-110 group-hover:-rotate-6">
                            <i class="fa-solid fa-a text-[14rem] md:text-[18rem]"></i>
                        </div>
                        
                        <h3 class="text-3xl md:text-4xl font-black text-white relative z-10 w-4/5 leading-[1.2]">Tài Liệu Học Tiếng Anh</h3>
                    </a>

                    <div class="grid grid-cols-2 gap-5 xl:gap-6">
                        <a href="#doanh-nghiep" class="group relative bg-[#ffb600] rounded-[2rem] lg:rounded-[2.5rem] p-6 lg:p-8 overflow-hidden flex flex-col justify-end transition-all hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(255,182,0,0.4)] shadow-lg h-44 md:h-52">
                            <div class="absolute -right-6 -bottom-6 opacity-15 text-white transition-transform duration-700 group-hover:scale-125 group-hover:rotate-12">
                                <i class="fa-solid fa-briefcase text-[8rem] md:text-[10rem]"></i>
                            </div>
                            <h4 class="text-lg md:text-xl lg:text-2xl font-bold text-white relative z-10 w-full">Tiếng Anh Doanh Nghiệp</h4>
                        </a>
                        
                        <a href="#thanh-tich" class="group relative bg-[#ffb600] rounded-[2rem] lg:rounded-[2.5rem] p-6 lg:p-8 overflow-hidden flex flex-col justify-end transition-all hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(255,182,0,0.4)] shadow-lg h-44 md:h-52">
                            <div class="absolute -right-8 -bottom-6 opacity-15 text-white transition-transform duration-700 group-hover:scale-125 group-hover:-translate-x-4">
                                <i class="fa-solid fa-plane-departure text-[8rem] md:text-[10rem]"></i>
                            </div>
                            <h4 class="text-lg md:text-xl lg:text-2xl font-bold text-white relative z-10 w-full">Thành Tích Tiếng Anh</h4>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

	<?php if ($homeUser && (string) ($homeUser['role'] ?? '') === 'student' && is_array($studentProgress)): ?>
		<section class="py-8" aria-label="Widget học viên">
			<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
				<div class="rounded-[2rem] border border-blue-100 bg-gradient-to-br from-white to-blue-50/50 p-6 md:p-8 shadow-xl shadow-blue-900/5 relative overflow-hidden">
					<div class="absolute top-0 right-0 p-8 opacity-10"><i class="fa-solid fa-graduation-cap text-8xl text-blue-600"></i></div>
					<div class="relative z-10">
						<h2 class="text-2xl font-extrabold text-slate-800">Tiến độ học tập của bạn</h2>
						<p class="mt-1 text-slate-600">Đã hoàn thành <strong class="text-blue-700 text-lg"><?= (int) ($studentProgress['completed_lessons'] ?? 0); ?></strong> / <strong><?= (int) ($studentProgress['total_lessons'] ?? 0); ?></strong> buổi học.</p>
						
						<div class="mt-5 mb-2 h-3 w-full overflow-hidden rounded-full bg-slate-200">
							<div class="h-full rounded-full bg-gradient-to-r from-cyan-400 to-blue-600 transition-all duration-1000" style="width: <?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%;"></div>
						</div>
						
						<div class="flex items-center justify-between">
							<p class="text-sm font-bold text-slate-500">Tiến độ: <span class="text-blue-600"><?= (int) ($studentProgress['progress_percent'] ?? 0); ?>%</span></p>
							<a class="text-sm font-bold text-blue-700 hover:underline underline-offset-4" href="<?= e(page_url('profile')); ?>">Xem chi tiết &rarr;</a>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ($homeUser && (string) ($homeUser['role'] ?? '') === 'teacher'): ?>
		<section class="py-8" aria-label="Widget giáo viên">
			<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
				<div class="rounded-[2rem] border border-indigo-100 bg-gradient-to-br from-white to-indigo-50/50 p-6 md:p-8 shadow-xl shadow-indigo-900/5">
					<div class="flex items-center justify-between mb-6">
						<h2 class="text-2xl font-extrabold text-slate-800">Lịch dạy 7 ngày tới</h2>
						<a class="text-sm font-bold text-indigo-700 hover:underline" href="<?= e(page_url('profile')); ?>">Xem tất cả</a>
					</div>
					
					<?php if (empty($teacherSchedules)): ?>
						<div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-white/50 py-10 text-center">
							<i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
							<p class="text-slate-500 font-medium">Chưa có lịch dạy trong 7 ngày tới.</p>
						</div>
					<?php else: ?>
						<ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
							<?php foreach ($teacherSchedules as $schedule): ?>
								<li class="group rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-indigo-300 hover:shadow-md">
									<strong class="block text-lg text-slate-800 group-hover:text-indigo-700 transition-colors"><?= e((string) $schedule['class_name']); ?></strong>
									<div class="mt-2 flex flex-col gap-1 text-sm text-slate-500">
										<span class="flex items-center gap-2"><i class="fa-regular fa-calendar text-indigo-400"></i> <?= e((string) $schedule['study_date']); ?></span>
										<span class="flex items-center gap-2"><i class="fa-regular fa-clock text-indigo-400"></i> <?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?></span>
										<span class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-indigo-400"></i> <?= e((string) $schedule['room_name']); ?></span>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="py-10" aria-label="Uy tín và đối tác">
		<div class="mx-auto grid w-full max-w-6xl gap-4 px-4 sm:grid-cols-2 lg:grid-cols-4 sm:px-6">
			<div class="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm"><strong class="block text-4xl font-black text-blue-600">1.2K+</strong><span class="mt-2 block text-sm font-medium text-slate-500">Học viên theo học</span></div>
			<div class="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm"><strong class="block text-4xl font-black text-blue-600">95%</strong><span class="mt-2 block text-sm font-medium text-slate-500">Phụ huynh hài lòng</span></div>
			<div class="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm"><strong class="block text-4xl font-black text-blue-600">60+</strong><span class="mt-2 block text-sm font-medium text-slate-500">Giáo viên & Trợ giảng</span></div>
			<div class="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm"><strong class="block text-4xl font-black text-blue-600">20+</strong><span class="mt-2 block text-sm font-medium text-slate-500">Đối tác học thuật</span></div>
		</div>
	</section>

	<section class="py-16 md:py-24 bg-white" id="khoa-hoc">
		<div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
			
			<div class="mb-12 text-center">
				<h2 class="text-3xl md:text-5xl font-black text-blue-900 uppercase tracking-tight">
					KHOÁ HỌC DÀNH CHO <span class="inline-block mt-2 md:mt-0 rounded-full bg-red-600 px-6 py-2 text-white shadow-lg shadow-red-500/30 transform -rotate-2">MỌI MỤC TIÊU</span>
				</h2>
				<p class="mt-5 text-lg text-slate-600 max-w-3xl mx-auto font-medium">
					Dễ dàng lựa chọn khóa học tiếng Anh phù hợp cho riêng mình với chương trình học đa dạng, được thiết kế phù hợp với nhu cầu và trình độ thực tế.
				</p>
			</div>

			<div class="rounded-[3rem] bg-[#00d4ff] p-6 md:p-8 lg:p-10 shadow-2xl shadow-cyan-500/20">
				<div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
					
					<article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-xl transition-all hover:-translate-y-2 hover:shadow-2xl">
						<div class="relative bg-blue-100 h-56 overflow-hidden">
							<img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=600&h=400" alt="Giao tiếp" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
							<div class="absolute top-4 right-4 bg-[#2e3192] text-white rounded-2xl px-3 py-2 text-center shadow-lg">
								<span class="block text-[10px] uppercase font-bold opacity-90">Level</span>
								<span class="block text-2xl font-black leading-none">1-2</span>
							</div>
						</div>
						<div class="flex flex-1 flex-col p-6">
							<h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight">Giao tiếp<br>Phản xạ</h3>
							<p class="mt-3 text-sm font-medium text-slate-600 flex-1 leading-relaxed">Phát triển phản xạ nghe nói tự nhiên và tự tin giao tiếp trong các tình huống thực tế sau 8-12 tuần.</p>
							<div class="mt-5 pt-4 border-t-2 border-slate-100 flex justify-between items-end">
								<div>
									<span class="block text-xs font-bold text-slate-400 uppercase">Học phí từ</span>
									<span class="text-xl font-black text-blue-600">3.200.000đ</span>
								</div>
							</div>
						</div>
					</article>

					<article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-xl transition-all hover:-translate-y-2 hover:shadow-2xl">
						<div class="relative bg-indigo-100 h-56 overflow-hidden">
							<img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=600&h=400" alt="IELTS Foundation" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
							<div class="absolute top-4 right-4 bg-[#2e3192] text-white rounded-2xl px-3 py-2 text-center shadow-lg">
								<span class="block text-[10px] uppercase font-bold opacity-90">Target</span>
								<span class="block text-2xl font-black leading-none">4.5+</span>
							</div>
						</div>
						<div class="flex flex-1 flex-col p-6">
							<h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight">IELTS<br>Foundation</h3>
							<p class="mt-3 text-sm font-medium text-slate-600 flex-1 leading-relaxed">Củng cố nền tảng ngữ pháp, từ vựng và làm quen với format 4 kỹ năng trước khi bước vào giai đoạn luyện đề.</p>
							<div class="mt-5 pt-4 border-t-2 border-slate-100 flex justify-between items-end">
								<div>
									<span class="block text-xs font-bold text-slate-400 uppercase">Học phí từ</span>
									<span class="text-xl font-black text-blue-600">5.800.000đ</span>
								</div>
							</div>
						</div>
					</article>

					<article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-xl transition-all hover:-translate-y-2 hover:shadow-2xl">
						<div class="relative bg-cyan-100 h-56 overflow-hidden">
							<img src="https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&q=80&w=600&h=400" alt="IELTS Intensive" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
							<div class="absolute top-4 right-4 bg-[#2e3192] text-white rounded-2xl px-3 py-2 text-center shadow-lg">
								<span class="block text-[10px] uppercase font-bold opacity-90">Target</span>
								<span class="block text-2xl font-black leading-none">6.5+</span>
							</div>
						</div>
						<div class="flex flex-1 flex-col p-6">
							<h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight">IELTS<br>Intensive</h3>
							<p class="mt-3 text-sm font-medium text-slate-600 flex-1 leading-relaxed">Tối ưu chiến lược làm bài, sửa lỗi sai trực tiếp 1-1 và nâng band điểm thần tốc với lộ trình cá nhân hóa.</p>
							<div class="mt-5 pt-4 border-t-2 border-slate-100 flex justify-between items-end">
								<div>
									<span class="block text-xs font-bold text-slate-400 uppercase">Học phí từ</span>
									<span class="text-xl font-black text-blue-600">8.900.000đ</span>
								</div>
							</div>
						</div>
					</article>

					<article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-xl transition-all hover:-translate-y-2 hover:shadow-2xl">
						<div class="relative bg-purple-100 h-56 overflow-hidden">
							<img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=600&h=400" alt="Tiếng Anh Doanh Nghiệp" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
							<div class="absolute top-4 right-4 bg-[#2e3192] text-white rounded-2xl px-3 py-2 text-center shadow-lg">
								<span class="block text-[10px] uppercase font-bold opacity-90">Cho</span>
								<span class="block text-xl font-black leading-none mt-1">Người lớn</span>
							</div>
						</div>
						<div class="flex flex-1 flex-col p-6">
							<h3 class="text-xl font-extrabold text-[#2e3192] uppercase leading-tight">Business<br>English</h3>
							<p class="mt-3 text-sm font-medium text-slate-600 flex-1 leading-relaxed">Tiếng Anh ứng dụng trong môi trường công sở: Viết email, thuyết trình, đàm phán và phỏng vấn.</p>
							<div class="mt-5 pt-4 border-t-2 border-slate-100 flex justify-between items-end">
								<div>
									<span class="block text-xs font-bold text-slate-400 uppercase">Học phí từ</span>
									<span class="text-xl font-black text-blue-600">6.500.000đ</span>
								</div>
							</div>
						</div>
					</article>

				</div>
			</div>
		</div>
	</section>
	

<section class="py-16 md:py-24 bg-white overflow-hidden" id="giao-vien">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-black text-[#2e3192] uppercase leading-tight tracking-tight">
                Hơn 3.100 Giáo viên và trợ giảng <br>
                <span class="relative inline-block text-white px-8 py-2 mt-4">
                    <span class="relative z-10">Chuẩn quốc tế</span>
                    <div class="absolute inset-0 bg-red-600 rounded-full -rotate-1 shadow-lg shadow-red-500/30"></div>
                </span>
            </h2>
        </div>

        <div class="bg-[#00d4ff] rounded-t-[3rem] p-8 md:p-12 border-x border-t border-slate-100 shadow-sm">
            <div class="grid lg:grid-cols-3 gap-8 items-center">
                <div class="lg:col-span-2">
                    <p class="text-[#2e3192] font-extrabold text-xl md:text-2xl leading-snug">
                        Đội ngũ giảng dạy chuẩn quốc tế lớn nhất Việt Nam sẵn sàng cùng Phụ huynh & Học viên chinh phục mọi mục tiêu.
                    </p>
                </div>
                <div class="flex justify-center lg:justify-end">
                    <div class="flex -space-x-4">
                        <div class="w-14 h-14 rounded-full border-4 border-[#00d4ff] bg-slate-200 overflow-hidden shadow-md"><img src="https://i.pravatar.cc/100?u=1" alt="T1"></div>
                        <div class="w-14 h-14 rounded-full border-4 border-[#00d4ff] bg-slate-200 overflow-hidden shadow-md"><img src="https://i.pravatar.cc/100?u=2" alt="T2"></div>
                        <div class="w-14 h-14 rounded-full border-4 border-[#00d4ff] bg-slate-200 overflow-hidden shadow-md"><img src="https://i.pravatar.cc/100?u=3" alt="T3"></div>
                        <div class="w-14 h-14 rounded-full border-4 border-[#00d4ff] bg-blue-900 text-white flex items-center justify-center text-xs font-bold shadow-md">+3k</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 border-x border-b border-slate-100 rounded-b-[3rem] p-6 md:p-10 shadow-2xl shadow-blue-900/5">
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
                        <article class="h-full flex flex-col rounded-[2rem] bg-white border-4 border-[#00d4ff] overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl group">
                            <div class="relative aspect-[3/4] overflow-hidden bg-slate-100">
                                <img src="<?= $t['img'] ?>" alt="<?= $t['name'] ?>" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500">
                                <div class="absolute top-4 right-4 bg-red-600 text-white rounded-xl p-2 text-center min-w-[52px] shadow-lg border-2 border-white/20">
                                    <span class="block text-xl font-black leading-none"><?= $t['exp'] ?></span>
                                    <span class="block text-[7px] font-bold uppercase leading-tight">Năm <br> kinh nghiệm</span>
                                </div>
                            </div>
                            <div class="p-5 text-center flex-1 flex flex-col justify-center">
                                <span class="text-[10px] font-black uppercase text-[#2e3192]/50 tracking-widest">Giáo viên</span>
                                <h4 class="text-sm font-black text-[#2e3192] mt-1 uppercase line-clamp-2"><?= $t['name'] ?></h4>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>

                </div>
                <div class="swiper-pagination-teacher mt-10"></div>
            </div>

            <div class="flex justify-center gap-4">
                <button class="teacher-prev w-12 h-12 rounded-full border-2 border-[#00d4ff] text-[#2e3192] hover:bg-[#2e3192] hover:text-white transition-all"><i class="fa-solid fa-arrow-left"></i></button>
                <button class="teacher-next w-12 h-12 rounded-full border-2 border-[#00d4ff] text-[#2e3192] hover:bg-[#2e3192] hover:text-white transition-all"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>

    </div>
</section>


	<section class="relative py-16 md:py-28 bg-slate-50 overflow-hidden" id="portal">
		<div class="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden">
			<div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#00d4ff]/10 blur-[80px]"></div>
			<div class="absolute bottom-10 right-10 w-80 h-80 rounded-full bg-[#2e3192]/5 blur-[80px]"></div>
		</div>

		<div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6">
			<div class="mb-16 text-center max-w-2xl mx-auto">
				<h2 class="text-3xl md:text-4xl font-black text-[#2e3192] uppercase tracking-tight">
					Tính năng nội bộ <span class="text-[#00d4ff]">mạnh mẽ</span>
				</h2>
				<p class="mt-4 text-slate-600 text-lg font-medium italic">"Mọi công cụ bạn cần đều nằm gọn trong tầm tay"</p>
			</div>

			<div class="grid gap-8 grid-cols-1 md:grid-cols-3 items-center">
				
				<article class="group rounded-[2rem] bg-white p-8 shadow-xl shadow-slate-200/50 transition-all duration-500 hover:-translate-y-3 hover:shadow-2xl hover:shadow-cyan-500/10 border border-slate-100 relative">
					<div class="relative z-10 text-center">
						<div class="mx-auto relative mb-8 h-24 w-24">
							<div class="absolute inset-0 bg-cyan-100 rounded-3xl rotate-6 transition-transform group-hover:rotate-12 group-hover:bg-[#00d4ff]"></div>
							<div class="absolute inset-0 bg-white rounded-3xl shadow-md overflow-hidden border border-slate-100">
								<img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=200" alt="Học tập" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
							</div>
							<div class="absolute -bottom-2 -right-2 bg-[#2e3192] text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
								<i class="fa-solid fa-graduation-cap text-[10px]"></i>
							</div>
						</div>
						
						<h3 class="text-xl font-extrabold text-[#2e3192]">Học tập thông minh</h3>
						<p class="mt-4 text-slate-600 leading-relaxed font-medium">Theo dõi lộ trình, nộp bài tập và tương tác trực tiếp với giáo viên qua cổng trực tuyến.</p>
					</div>
				</article>

				<article class="group rounded-[2rem] bg-gradient-to-b from-[#2e3192] to-[#1a1c6b] p-8 shadow-2xl shadow-blue-900/30 text-center relative transform md:-translate-y-6 transition-all duration-500 hover:-translate-y-8 border border-[#3f43b5]">
					<div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-[#00d4ff] text-[#2e3192] text-[10px] font-black uppercase tracking-widest px-6 py-2 rounded-full shadow-lg z-20">
						Dành cho quản trị
					</div>

					<div class="relative z-10">
						<div class="mx-auto relative mb-8 h-28 w-28">
							<div class="absolute inset-0 bg-white/10 backdrop-blur-md rounded-[2rem] -rotate-6 transition-transform group-hover:-rotate-12"></div>
							<div class="absolute inset-0 bg-white rounded-[2rem] shadow-xl overflow-hidden border-2 border-[#00d4ff]">
								<img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=200" alt="Quản lý" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
							</div>
							<div class="absolute -bottom-2 -right-2 bg-[#00d4ff] text-[#2e3192] w-10 h-10 rounded-full flex items-center justify-center shadow-lg border-2 border-white">
								<i class="fa-solid fa-users-gear text-sm"></i>
							</div>
						</div>
						
						<h3 class="text-xl font-extrabold text-white">Quản lý toàn diện</h3>
						<p class="mt-4 text-blue-100/90 leading-relaxed font-medium">Điều phối lớp học, điểm danh và phê duyệt yêu cầu học vụ chỉ với một chạm.</p>
					</div>
				</article>

				<article class="group rounded-[2rem] bg-white p-8 shadow-xl shadow-slate-200/50 transition-all duration-500 hover:-translate-y-3 hover:shadow-2xl hover:shadow-cyan-500/10 border border-slate-100 relative">
					<div class="relative z-10 text-center">
						<div class="mx-auto relative mb-8 h-24 w-24">
							<div class="absolute inset-0 bg-cyan-100 rounded-3xl rotate-6 transition-transform group-hover:rotate-12 group-hover:bg-[#00d4ff]"></div>
							<div class="absolute inset-0 bg-white rounded-3xl shadow-md overflow-hidden border border-slate-100">
								<img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&q=80&w=200" alt="Thanh toán" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
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

	<section class="relative py-16 md:py-28 overflow-hidden bg-slate-50" id="lien-he">
		
		<div class="absolute inset-0 pointer-events-none">
			<div class="absolute -top-[10%] -left-[5%] w-[40vw] h-[40vw] rounded-full bg-cyan-100/50 blur-[120px]"></div>
			<div class="absolute -bottom-[10%] -right-[5%] w-[35vw] h-[35vw] rounded-full bg-blue-100/40 blur-[100px]"></div>
			<div class="absolute inset-0 bg-gradient-to-b from-white/0 via-blue-50/30 to-white/0"></div>
		</div>

		<div class="relative z-10 mx-auto w-full max-w-5xl px-4 sm:px-6">
			
			<div class="mb-12 text-center">
				<h2 class="text-3xl md:text-5xl font-black text-[#2e3192] uppercase tracking-tight flex items-center justify-center flex-wrap gap-3">
					ĐĂNG KÝ NHẬN 
					<span class="bg-red-600 text-white px-6 py-2 rounded-full inline-flex items-center gap-2 relative shadow-lg shadow-red-500/30">
						TƯ VẤN NGAY
						<i class="fa-solid fa-certificate text-cyan-400 absolute -top-4 -right-6 text-3xl opacity-80 animate-pulse hidden md:block"></i>
					</span>
				</h2>
				<p class="mt-4 text-slate-500 font-semibold text-lg italic">Đội ngũ chuyên viên sẽ liên hệ và hỗ trợ bạn trong thời gian sớm nhất</p>
			</div>

			<div class="grid md:grid-cols-3 gap-8 items-start">
				
				<div class="md:col-span-2 bg-[#00d4ff] rounded-[2.5rem] p-6 md:p-10 shadow-[0_20px_50px_rgba(0,212,255,0.3)] border border-white/20">
					<form class="grid gap-5" action="#" method="post">
						<?= csrf_input(); ?>
						
						<div>
							<input class="w-full bg-white text-[#2e3192] placeholder-slate-400 font-semibold rounded-full px-6 py-4 outline-none focus:ring-4 focus:ring-white/60 transition-all shadow-sm" type="text" name="full_name" placeholder="Họ Và Tên *" required>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
							<input class="w-full bg-white text-[#2e3192] placeholder-slate-400 font-semibold rounded-full px-6 py-4 outline-none focus:ring-4 focus:ring-white/60 transition-all shadow-sm" type="tel" name="phone" placeholder="Điện thoại *" required>
							<div class="relative">
								<select name="learning_mode" class="w-full bg-white text-[#2e3192] font-semibold rounded-full px-6 py-4 outline-none appearance-none focus:ring-4 focus:ring-white/60 transition-all shadow-sm cursor-pointer" required>
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
							<input class="w-full bg-white text-[#2e3192] placeholder-slate-400 font-semibold rounded-full px-6 py-4 outline-none focus:ring-4 focus:ring-white/60 transition-all shadow-sm" type="email" name="email" placeholder="Địa chỉ email">
							<div class="relative">
								<select name="city" class="w-full bg-white text-[#2e3192] font-semibold rounded-full px-6 py-4 outline-none appearance-none focus:ring-4 focus:ring-white/60 transition-all shadow-sm cursor-pointer" required>
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
							<select name="course" class="w-full bg-white text-[#2e3192] font-semibold rounded-full px-6 py-4 outline-none appearance-none focus:ring-4 focus:ring-white/60 transition-all shadow-sm cursor-pointer" required>
								<option value="" disabled selected hidden>Khóa học *</option>
								<option value="giao-tiep">Giao tiếp Phản xạ</option>
								<option value="ielts">Luyện thi IELTS</option>
								<option value="business">Tiếng Anh Doanh Nghiệp</option>
							</select>
							<div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
								<div class="bg-[#2e3192] text-white rounded-full w-8 h-8 flex items-center justify-center text-sm shadow-md"><i class="fa-solid fa-chevron-down"></i></div>
							</div>
						</div>

						<button type="submit" class="mt-4 group w-full bg-[#2e3192] hover:bg-[#1a1c6b] text-white font-black rounded-full py-5 px-4 flex justify-between items-center transition-all shadow-xl hover:shadow-[0_20px_40px_rgba(46,49,146,0.3)] hover:-translate-y-1">
							<span class="flex-1 text-center text-xl uppercase tracking-widest">Tư vấn ngay</span>
							<div class="bg-white text-[#2e3192] rounded-full w-12 h-12 flex items-center justify-center text-2xl transition-transform group-hover:translate-x-2 shadow-md">
								<i class="fa-solid fa-arrow-right-long transform -rotate-45"></i>
							</div>
						</button>
					</form>
				</div>

				<div class="flex flex-col gap-6">
					<div class="bg-white/60 backdrop-blur-md rounded-[2rem] p-8 border border-white shadow-xl shadow-slate-200/50 flex flex-col gap-5">
						<h3 class="text-[#2e3192] font-bold text-center text-lg mb-2">Hỗ trợ trực tuyến</h3>
						<a href="#" class="group bg-[#2e3192] hover:bg-[#1a1c6b] text-white rounded-full py-5 px-4 flex justify-between items-center font-bold transition-all shadow-lg hover:-translate-y-1">
							<span class="pl-4 text-lg">NHẮN TIN</span>
							<div class="bg-white text-[#2e3192] rounded-full w-12 h-12 flex items-center justify-center text-2xl shadow-md"><i class="fa-brands fa-facebook-messenger"></i></div>
						</a>
						
						<a href="tel:02873083333" class="group bg-red-600 hover:bg-red-700 text-white rounded-full py-5 px-4 flex justify-between items-center font-bold transition-all shadow-lg hover:-translate-y-1">
							<span class="pl-4 text-lg">HOTLINE</span>
							<div class="bg-white text-red-600 rounded-full w-12 h-12 flex items-center justify-center text-2xl shadow-md"><i class="fa-solid fa-phone-volume transform -rotate-12"></i></div>
						</a>
					</div>
					
					<div class="bg-gradient-to-br from-[#2e3192] to-[#1a1c6b] rounded-[2rem] p-6 text-white text-center shadow-lg">
						<i class="fa-solid fa-headset text-3xl text-[#00d4ff] mb-3"></i>
						<p class="text-sm font-medium opacity-90">Tư vấn miễn phí 24/7 qua tất cả các kênh liên lạc.</p>
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
