<?php

declare(strict_types=1);

/**
 * Variável opcional:
 *   $scripts (list<string>)  arquivos JS de public/assets/js/
 */
$scripts = $scripts ?? [];
?>
    <?php foreach ($scripts as $script): ?>
        <script src="assets/js/<?= e($script) ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>