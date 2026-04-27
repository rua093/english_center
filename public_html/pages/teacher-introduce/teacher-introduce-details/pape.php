<?php
declare(strict_types=1);

require_once __DIR__ . '/../partials/header.php';
if (isset($__pageContent)) {
    echo $__pageContent;
} else {
    // Nếu bạn load trang danh sách thì include danh_sach_gv.php
    // Nếu load chi tiết thì include chi_tiet_gv.php
    // Ở đây tôi để mặc định theo route của bạn.
}
require_once __DIR__ . '/../partials/footer.php';