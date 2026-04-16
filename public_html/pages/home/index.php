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

	<style>
    @keyframes bell-shake {
        0%, 100% { transform: rotate(0deg) translateY(0); }
        10% { transform: rotate(-8deg) translateY(-1px); }
        20% { transform: rotate(8deg) translateY(-1px); }
        30% { transform: rotate(-6deg) translateY(0); }
        40% { transform: rotate(6deg) translateY(0); }
        50% { transform: rotate(-4deg) translateY(-1px); }
        60% { transform: rotate(4deg) translateY(0); }
        70% { transform: rotate(-2deg) translateY(0); }
        80% { transform: rotate(2deg) translateY(0); }
        90% { transform: rotate(0deg) translateY(-1px); }
    }

    .contact-bell {
        animation: bell-shake 2.8s ease-in-out infinite;
        transform-origin: center bottom;
    }

    .contact-bell:nth-child(2) {
        animation-delay: 0.15s;
    }

    .contact-bell:nth-child(3) {
        animation-delay: 0.3s;
    }

    .contact-bell:nth-child(4) {
        animation-delay: 0.45s;
    }

    .contact-bell:nth-child(5) {
        animation-delay: 0.6s;
    }

    .contact-bell:hover {
        animation-play-state: paused;
    }

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
        animation: spin-orbit 20s linear infinite;
    }
    .orbit-reverse-spin {
        animation: spin-orbit-reverse 20s linear infinite;
    }
    
    /* Tạm dừng toàn bộ vòng quay khi di chuột vào */
    .orbit-wrapper:hover .orbit-spin,
    .orbit-wrapper:hover .orbit-reverse-spin {
        animation-play-state: paused;
    }
</style>
?>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
	<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
	<style>
		.swiper-pagination-teacher .swiper-pagination-bullet-active {
			background-color: #2e3192 !important;
			width: 1.5rem !important;
			border-radius: 99px !important;
		}
	</style>
	<?php
require_once __DIR__ . '/main.php';
?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
	    if (typeof Swiper === 'undefined') {
	        return;
	    }

	    new Swiper('.teacherSwiper', {
	        slidesPerView: 1.2,
	        spaceBetween: 16,
	        centeredSlides: false,
	        loop: true,
	        autoplay: { delay: 3500, disableOnInteraction: false },
	        pagination: {
	            el: '.swiper-pagination-teacher',
	            clickable: true,
	            renderBullet: function (index, className) {
	                return '<span class="' + className + ' w-3 h-3 border-2 border-[#2e3192] rounded-full transition-all"></span>';
	            },
	        },
	        breakpoints: {
	            640: { slidesPerView: 2.2, spaceBetween: 20 },
	            1024: { slidesPerView: 3, spaceBetween: 30 }
	        }
	    });
	});
	</script>
