	<footer class="border-t border-slate-200 bg-white">
		<div class="mx-auto w-full max-w-6xl px-4 sm:px-6 flex flex-col gap-4 py-6 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
			<div class="grid gap-1">
				<strong>Nền tảng Trung tâm Anh ngữ</strong>
				<small>Website công khai + Cổng học viên + Bảng điều khiển quản trị</small>
			</div>
			<div class="flex flex-wrap gap-3">
				<?php if (!is_logged_in()): ?>
					<a href="/?page=login">Đăng nhập</a>
				<?php endif; ?>
				<?php if (can_access_page('student-dashboard')): ?>
					<a href="/?page=student-dashboard">Cổng học viên</a>
				<?php endif; ?>
				<?php if (can_access_page('admin-dashboard')): ?>
					<a href="/admin">Quản trị</a>
				<?php endif; ?>
			</div>
			<a href="#top">Lên đầu trang</a>
		</div>
	</footer>
	<?php $mainScriptAsset = getVersion('js', 'main.js'); ?>
	<?php if ($mainScriptAsset !== ''): ?>
		<script src="<?= e($mainScriptAsset); ?>"></script>
	<?php endif; ?>
</body>
</html>
