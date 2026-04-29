<?php
declare(strict_types=1);

set_flash('error', 'Chức năng quản lý tài khoản ngân hàng đã được gỡ khỏi hệ thống.');

redirect(page_url('bank-manage'));
