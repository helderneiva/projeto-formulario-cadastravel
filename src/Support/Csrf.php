<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Proteção contra CSRF: garante que o formulário foi enviado a partir
 * do nosso site e não de uma página de terceiros.
 */
final class Csrf
{
    private const CHAVE = 'csrf_token';

    /**
     * Retorna o token da sessão, criando um se necessário.
     */
    public static function token(): string
    {
        $token = $_SESSION[self::CHAVE] ?? null;

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::CHAVE] = $token;
        }

        return $token;
    }

    /**
     * HTML do campo escondido que deve ficar dentro de todo formulário POST.
     */
    public static function campo(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . e(self::token()) . '">';
    }

    /**
     * Confere se o token recebido do formulário é o da sessão.
     */
    public static function validar(mixed $tokenRecebido): bool
    {
        $tokenSessao = $_SESSION[self::CHAVE] ?? null;

        return is_string($tokenRecebido)
            && is_string($tokenSessao)
            && hash_equals($tokenSessao, $tokenRecebido);
    }

    /**
     * Descarta o token atual; um novo é criado na próxima visita.
     */
    public static function renovar(): void
    {
        unset($_SESSION[self::CHAVE]);
    }
}