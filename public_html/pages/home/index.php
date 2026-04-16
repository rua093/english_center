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
