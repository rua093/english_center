<?php
declare(strict_types=1);

logout_user();
set_flash('success', t('auth.logout_success'));
redirect(page_url('login'));
