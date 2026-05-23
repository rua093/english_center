<?php
declare(strict_types=1);

require_once __DIR__ . '/../partials/admin_header.php';
?>
<main class="min-w-0" data-admin-main-content="1">
    <?= $__pageContent ?? ''; ?>
</main>
<?php
require_once __DIR__ . '/../partials/admin_footer.php';
