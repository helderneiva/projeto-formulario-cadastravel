<?php

declare(strict_types=1);

/**
 * Exibe as mensagens de sucesso e de erro.
 *
 * Este arquivo é incluído (require) pelas páginas, que definem as
 * variáveis abaixo antes da inclusão:
 *
 * @var string|null $sucesso      mensagem de sucesso, ou null
 * @var list<string> $erros       lista de mensagens de erro
 * @var string|null $tituloErros  título opcional do bloco de erros
 */
$tituloErros = $tituloErros ?? 'Verifique os seguintes problemas:';
?>
<?php if ($sucesso !== null): ?>
    <div class="alerta alerta-sucesso" role="status">
        <?= e($sucesso) ?>
    </div>
<?php endif; ?>

<?php if ($erros !== []): ?>
    <div class="alerta alerta-erro" role="alert">
        <p><?= e($tituloErros) ?></p>
        <ul>
            <?php foreach ($erros as $erro): ?>
                <li><?= e($erro) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>