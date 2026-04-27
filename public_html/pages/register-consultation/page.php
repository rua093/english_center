<?php
declare(strict_types=1);

// Load Header hệ thống
require_once __DIR__ . '/../partials/header.php';
?>

<div class="bg-slate-50 min-h-screen">
    <?php
    if (isset($__pageContent)) {
        echo $__pageContent;
    } else {
        require __DIR__ . '/index.php';
    }
    ?>
</div>

<?php
// Load Footer hệ thống
require_once __DIR__ . '/../partials/footer.php';
?>
