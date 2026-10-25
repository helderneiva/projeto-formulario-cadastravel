<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Mensagens "de uma só vez": guardadas na sessão por uma página e
 * exibidas (e apagadas) pela página seguinte.
 */
final class Flash
{
    private const CHAVE = 'flash';

    public static function sucesso(string $mensagem): void
    {
        self::gravar('sucesso', $mensagem);
    }

    /**
     * @param list<string> $erros
     */
    public static function erros(array $erros): void
    {
        self::gravar('erros', $erros);
    }

    /**
     * Dados digitados, para repreencher o formulário após um erro.
     * Nunca guarde senhas aqui.
     *
     * @param array<string, string> $dados
     */
    public static function antigos(array $dados): void
    {
        self::gravar('antigos', $dados);
    }

    /**
     * Lê as mensagens e já as apaga.
     *
     * @return array{erros: list<string>, sucesso: ?string, antigos: array<string, string>}
     */
    public static function ler(): array
    {
        $flash = $_SESSION[self::CHAVE] ?? [];
        unset($_SESSION[self::CHAVE]);

        if (!is_array($flash)) {
            $flash = [];
        }

        $erros = is_array($flash['erros'] ?? null)
            ? array_values(array_filter($flash['erros'], 'is_string'))
            : [];

        $sucesso = is_string($flash['sucesso'] ?? null)
            ? $flash['sucesso']
            : null;

        $antigos = is_array($flash['antigos'] ?? null)
            ? array_filter($flash['antigos'], 'is_string')
            : [];

        return [
            'erros'   => $erros,
            'sucesso' => $sucesso,
            'antigos' => $antigos,
        ];
    }

    private static function gravar(string $campo, mixed $valor): void
    {
        if (
            !isset($_SESSION[self::CHAVE])
            || !is_array($_SESSION[self::CHAVE])
        ) {
            $_SESSION[self::CHAVE] = [];
        }

        $_SESSION[self::CHAVE][$campo] = $valor;
    }
}