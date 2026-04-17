<?php
declare(strict_types=1);

$homeWidgets = [
	'student_progress' => null,
	'teacher_schedules' => [],
];
if (is_logged_in()) {
	$user = auth_user();
	if ($user) {
		$homeWidgets = (new UserModel())->homeWidgetData((int) $user['id'], (string) $user['role']);
	}
}
?>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
	<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
	<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

	<style>
		:root {
			/* Educational Color Palette */
			--primary: #1e3a8a;      
			--primary-light: #eff6ff;
			--primary-mid: #3b82f6;  
			--accent: #f59e0b;       
			--accent-hover: #d97706;
			--text-main: #334155;    
			--text-muted: #64748b;   
			--white: #ffffff;
			--bg-light: #f8fafc;     
		}

		/* Custom scrollbar mượt mà */
		::-webkit-scrollbar { width: 8px; }
		::-webkit-scrollbar-track { background: var(--bg-light); }
		::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }

		/* Swiper custom */
		.swiper-pagination-teacher .swiper-pagination-bullet-active {
			background-color: var(--primary) !important;
			width: 2rem !important;
			border-radius: 99px !important;
		}

		/* Clean Utilities */
		.edu-shadow {
			box-shadow: 0 10px 40px -10px rgba(30, 58, 138, 0.1);
			transition: all 0.3s ease;
		}
		.edu-shadow:hover {
			box-shadow: 0 20px 40px -10px rgba(30, 58, 138, 0.15);
			transform: translateY(-4px);
		}

		.edu-card {
			background: var(--white);
			border-radius: 1.5rem;
			border: 1px solid rgba(226, 232, 240, 0.8);
		}

		/* Soft floating animation for images */
		@keyframes floatSoft {
			0%, 100% { transform: translateY(0px); }
			50% { transform: translateY(-10px); }
		}
		.float-soft { animation: floatSoft 6s ease-in-out infinite; }
	</style>

	<?php
	require_once __DIR__ . '/main.php';
	?>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
	    // 1. Khởi tạo AOS với offset = 0 để hiện sớm hơn
	    AOS.init({ 
	        duration: 600,  
	        once: true,     
	        offset: 0       // Kích hoạt ngay khi mép phần tử chạm đáy màn hình
	    });

	    // 2. Khởi tạo Swiper
	    if (typeof Swiper !== 'undefined') {
		    new Swiper('.teacherSwiper', {
		        slidesPerView: 1.2,
		        spaceBetween: 24,
		        centeredSlides: false,
		        loop: true,
		        autoplay: { delay: 4000, disableOnInteraction: false },
		        pagination: {
		            el: '.swiper-pagination-teacher',
		            clickable: true,
		            renderBullet: function (index, className) {
		                return '<span class="' + className + ' w-3 h-3 border-2 border-[#1e3a8a] rounded-full transition-all"></span>';
		            },
		        },
		        breakpoints: {
		            640: { slidesPerView: 2.2, spaceBetween: 30 },
		            1024: { slidesPerView: 4, spaceBetween: 32 }
		        }
		    });
		}
	});

	// 3. FIX LỖI DELAY DO LOAD ẢNH CHẬM (CỰC KỲ QUAN TRỌNG)
	window.addEventListener('load', function() {
	    // Tính toán lại toàn bộ tọa độ AOS sau khi HTML, CSS và toàn bộ hình ảnh đã load xong 100%
	    AOS.refresh();
	});
	</script>