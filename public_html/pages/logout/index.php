<?php
declare(strict_types=1);

logout_user();
set_flash('success', 'Bạn đã đăng xuất thành công.');
redirect('/?page=login');
