<?php
declare(strict_types=1);

// require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../partials/tailwind_cdn.php';
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
require_once __DIR__ . '/../../partials/footer.php';
?>
