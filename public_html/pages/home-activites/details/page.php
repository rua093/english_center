<?php
declare(strict_types=1);

require_once __DIR__ .  '/../../partials/header.php';
?>
<div class="bg-slate-50 min-h-screen font-jakarta">
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