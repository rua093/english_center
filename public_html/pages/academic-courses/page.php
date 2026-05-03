<?php
declare(strict_types=1);

if (is_admin_area()) {
    require_once __DIR__ . '/../partials/admin_header.php';
} else {
    require_once __DIR__ . '/../partials/header.php';
}

if (isset($__pageContent)) {
    echo $__pageContent;
} else {
    require __DIR__ . '/index.php';
}

if (is_admin_area()) {
    require_once __DIR__ . '/../partials/admin_footer.php';
} else {
    require_once __DIR__ . '/../partials/footer.php';
}
