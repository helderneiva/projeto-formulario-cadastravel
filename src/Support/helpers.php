<?php

declare(strict_types=1);

/**
 * Escapa texto para exibição segura em HTML (proteção contra XSS).
 */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redireciona para outra página. O código 303 evita o reenvio do
 * formulário quando o usuário aperta F5.
 */
function redirecionar(string $destino): never
{
    header('Location: ' . $destino, true, 303);
    exit;
}