<?php
declare(strict_types=1);

require_admin_or_staff();
if (!has_permission('admin.dashboard.view')) {
http_response_code(403);
echo '403 Forbidden';
exit;
}

redirect(page_url('dashboard-admin'));
