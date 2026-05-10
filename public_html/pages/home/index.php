<?php
declare(strict_types=1);

$homeWidgets = [
	'student_progress' => null,
	'teacher_schedules' => [],
];

$academicModel = new AcademicModel();
$courseTotal = $academicModel->countCourses();
$courseRows = $courseTotal > 0
	? $academicModel->listCoursesPage(1, min(8, $courseTotal))
	: [];

$buildCourseSlug = static function (string $value): string {
	$slug = strtolower(trim($value));
	$slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug) ?? $slug;
	$slug = preg_replace('/[\s-]+/', '-', $slug) ?? $slug;
	return trim($slug, '-');
};

$resolveCourseImage = static function (?string $value): string {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}

	if (preg_match('#^(?:https?:)?//#i', $value) === 1) {
		return $value;
	}

	return str_starts_with($value, '/') ? $value : '/' . ltrim($value, '/');
};

$homeCourses = [];
foreach ($courseRows as $row) {
	$courseName = trim((string) ($row['course_name'] ?? ''));
	if ($courseName === '') {
		continue;
	}

	$slug = $buildCourseSlug($courseName);
	$homeCourses[] = [
		'slug' => $slug,
		'title' => $courseName,
		'short_desc' => (string) ($row['description'] ?? 'Chương trình học được xây dựng theo lộ trình rõ ràng, phù hợp cho từng học viên.'),
		'price' => number_format((float) ($row['base_price'] ?? 0), 0, ',', '.') . 'đ',
		'total_sessions' => max(0, (int) ($row['total_sessions'] ?? 0)),
		'level' => 'Đang cập nhật',
		'image' => $resolveCourseImage((string) ($row['image_thumbnail'] ?? '')),
		'roadmap_count' => max(0, (int) ($row['roadmap_count'] ?? 0)),
		'class_count' => max(0, (int) ($row['class_count'] ?? 0)),
	];
}

$homeActivities = $academicModel->listActivitiesPage(1, 4);
$homeTeachers = $academicModel->feedbackLookups()['teachers'] ?? [];
$homeFeedbacks = $academicModel->listPublicFeedbacks(6);
$homeFeedbackAverage = 0.0;
if (!empty($homeFeedbacks)) {
	$homeFeedbackAverage = array_sum(array_map(static fn (array $feedback): float => (float) ($feedback['rating'] ?? 0), $homeFeedbacks)) / count($homeFeedbacks);
}

// Fetch public student portfolios to display on homepage (limit 6)
$studentPortfolios = $academicModel->listPortfoliosPage(1, 6, '', ['is_public_web' => 1]);

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
		.swiper-pagination-feedback .swiper-pagination-bullet-active {
			background-color: #0f766e !important;
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
		.float-soft { animation: floatSoft 3.5s ease-in-out infinite; }
	</style>

	<?php
	require_once __DIR__ . '/main.php';
	?>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
	    if ('scrollRestoration' in window.history) {
	        window.history.scrollRestoration = 'manual';
	    }
	    if (!window.location.hash) {
	        window.scrollTo(0, 0);
	    }

	    // 1. Khởi tạo AOS với offset = 0 để hiện sớm hơn
	    AOS.init({ 
	        duration: 350,  
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
		        autoplay: { delay: 2500, disableOnInteraction: false },
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

		    new Swiper('.feedbackSwiper', {
		        slidesPerView: 1,
		        spaceBetween: 20,
		        loop: true,
		        autoplay: { delay: 3000, disableOnInteraction: false },
		        pagination: {
		            el: '.swiper-pagination-feedback',
		            clickable: true,
		            renderBullet: function (index, className) {
		                return '<span class="' + className + ' w-3 h-3 border-2 border-[#0f766e] rounded-full transition-all"></span>';
		            },
		        },
		        breakpoints: {
		            640: { slidesPerView: 1.1, spaceBetween: 24 },
		            1024: { slidesPerView: 2.2, spaceBetween: 28 }
		        }
		    });

			// Student portfolio swiper (autoplay like feedback)
			new Swiper('.studentPortfolioSwiper', {
			    slidesPerView: 1,
			    spaceBetween: 20,
			    loop: true,
			    autoplay: { delay: 3000, disableOnInteraction: false },
			    pagination: {
			        el: '.swiper-pagination-portfolio',
			        clickable: true,
			        renderBullet: function (index, className) {
			            return '<span class="' + className + ' w-3 h-3 border-2 border-[#0f766e] rounded-full transition-all"></span>';
			        },
			    },
			    breakpoints: {
			        640: { slidesPerView: 1.1, spaceBetween: 24 },
			        1024: { slidesPerView: 2.2, spaceBetween: 28 }
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
	// Portfolio modal handling: open modal and autoplay video when a portfolio media (video) is clicked
	document.addEventListener('DOMContentLoaded', function() {
	    const modal = document.getElementById('portfolioVideoModal');
	    const modalVideo = document.getElementById('portfolioModalVideo');
	    const modalClose = document.getElementById('portfolioModalClose');

	    function openModal(src) {
	        if (!modal || !modalVideo) return;
	        modal.classList.remove('hidden');
	        modal.classList.add('flex');
	        modalVideo.src = src;
	        modalVideo.load();
	        // attempt autoplay
	        const playPromise = modalVideo.play();
	        if (playPromise !== undefined) {
	            playPromise.catch(() => {
	                // autoplay blocked, keep controls visible
	            });
	        }
	    }

	    function closeModal() {
	        if (!modal || !modalVideo) return;
	        modal.classList.add('hidden');
	        modal.classList.remove('flex');
	        try { modalVideo.pause(); } catch (e) {}
	        modalVideo.removeAttribute('src');
	        modalVideo.load();
	    }

	    // Delegate clicks inside the student portfolio swiper
	    document.addEventListener('click', function(e) {
	        const el = e.target.closest('.portfolio-media');
	        if (!el) return;
	        const isVideo = el.dataset.isVideo === '1' || el.dataset.isVideo === 'true';
	        const src = el.dataset.media || '';
	        if (isVideo && src) {
	            openModal(src);
	        }
	    });

	    // Close handlers
	    if (modalClose) modalClose.addEventListener('click', closeModal);
	    if (modal) modal.addEventListener('click', function(e) {
	        if (e.target === modal) closeModal();
	    });
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
		animation: spin-orbit 12s linear infinite;
    }
    .orbit-reverse-spin {
		animation: spin-orbit-reverse 12s linear infinite;
    }
    
    /* Tạm dừng toàn bộ vòng quay khi di chuột vào */
    .orbit-wrapper:hover .orbit-spin,
    .orbit-wrapper:hover .orbit-reverse-spin {
        animation-play-state: paused;
    }
</style>
<style>
    /* Chỉ áp dụng cho Mobile (dưới 640px) */
    @media (max-width: 639.98px) {
        .mobile-swipe-track {
            display: flex !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch; /* Hỗ trợ vuốt mượt trên iOS */
            scroll-snap-type: x mandatory; /* Hỗ trợ khựng lại ở giữa card khi vuốt */
            scrollbar-width: none; /* Ẩn thanh cuộn Firefox */
            padding-bottom: 1rem; /* Tạo khoảng không cho bóng đổ (shadow) */
        }
        .mobile-swipe-track::-webkit-scrollbar {
            display: none; /* Ẩn thanh cuộn Chrome/Safari/Edge */
        }
        .mobile-swipe-card {
            width: 85vw !important; /* Độ rộng của 1 card trên mobile */
            flex-shrink: 0;
            scroll-snap-align: center; /* Tự động căn giữa màn hình khi vuốt xong */
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Chỉ kích hoạt JS tự chạy ở màn hình điện thoại (Dưới 640px)
    if (window.innerWidth < 640) {
        const tracks = document.querySelectorAll('.mobile-swipe-track');
        
        tracks.forEach(track => {
            let isTouching = false;
            let direction = 1; // 1: Trượt phải, -1: Trượt trái
            let animationFrameId;

            // Hàm tự động cuộn
            const autoScroll = () => {
                if (!isTouching) {
                    track.scrollLeft += direction;
                    
                    // Kiểm tra chạm hai mép biên để tự đảo chiều
                    if (track.scrollLeft >= (track.scrollWidth - track.clientWidth - 1)) {
                        direction = -1; // Chạm phải -> chạy lùi
                    } else if (track.scrollLeft <= 0) {
                        direction = 1;  // Chạm trái -> chạy tới
                    }
                }
                // Sử dụng requestAnimationFrame để hoạt ảnh siêu mượt (60fps)
                animationFrameId = requestAnimationFrame(autoScroll);
            };

            // Hàm kích hoạt lại tự động cuộn
            const startScroll = () => {
                track.style.scrollSnapType = 'none'; // Tắt khóa dính để cuộn tự động mượt mà
                cancelAnimationFrame(animationFrameId);
                animationFrameId = requestAnimationFrame(autoScroll);
            };

            // Hàm dừng khi người dùng thao tác
            const stopScroll = () => {
                cancelAnimationFrame(animationFrameId);
                track.style.scrollSnapType = 'x mandatory'; // Bật lại khóa dính giúp vuốt tay có lực hút
            };

            // ---- Lắng nghe hành vi vuốt màn hình (Mobile) ----
            track.addEventListener('touchstart', () => {
                isTouching = true;
                stopScroll();
            }, { passive: true });

            track.addEventListener('touchend', () => {
                isTouching = false;
                // Đợi 1.5 giây sau khi người dùng thả tay mới bắt đầu chạy tự động lại
                setTimeout(() => {
                    if (!isTouching) startScroll();
                }, 1500);
            });

            // Chạy lần đầu tiên khi vừa load trang
            startScroll();
        });
    }
});
</script>
