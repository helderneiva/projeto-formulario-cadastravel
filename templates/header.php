<?php

declare(strict_types=1);

/**
 * Variáveis que a página pode definir antes de incluir este arquivo:
 *   $titulo     (string)        título da aba do navegador
 *   $estilos    (list<string>)  CSS extras, além do style.css
 *   $classeBody (string)        classe do <body>, usada pelo CSS da página
 */
$titulo = $titulo ?? 'Perfil e Feedback';
$estilos = $estilos ?? [];
$classeBody = $classeBody ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($titulo) ?> | Perfil e Feedback</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <?php foreach ($estilos as $estilo): ?>
        <link rel="stylesheet" href="assets/css/<?= e($estilo) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($classeBody) ?>">