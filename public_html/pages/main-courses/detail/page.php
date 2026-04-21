<?php
declare(strict_types=1);

require_once __DIR__ . '/../../partials/header.php';

if (isset($__pageContent)) {
    echo $__pageContent;
} else {
    // Mặc định load nội dung chi tiết khoá học
    require __DIR__ . '/index.php';
}

require_once __DIR__ . '/../../partials/footer.php';