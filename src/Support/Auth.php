<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Controle de quem está logado.
 */
final class Auth
{
    private const CHAVE = 'usuario_id';

    /**
     * ID do usuário logado, ou null se ninguém estiver logado.
     */
    public static function usuarioId(): ?int
    {
        $id = $_SESSION[self::CHAVE] ?? null;

        return is_int($id) ? $id : null;
    }

    /**
     * Marca o usuário como logado.
     */
    public static function logar(int $usuarioId): void
    {
        // Novo identificador de sessão: impede que alguém que conhecia
        // o identificador antigo continue com acesso.
        session_regenerate_id(true);
        $_SESSION[self::CHAVE] = $usuarioId;
        Csrf::renovar();
    }

    /**
     * Encerra o login: apaga os dados da sessão e troca o identificador.
     */
    public static function sair(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    /**
     * Garante que há alguém logado; senão, manda para o login.
     */
    public static function exigirLogin(): int
    {
        $id = self::usuarioId();

        if ($id === null) {
            Flash::erros(['Faça login para acessar esta página.']);
            redirecionar('login.php');
        }

        return $id;
    }

    /**
     * Para páginas de login e cadastro: quem já entrou vai para o perfil.
     */
    public static function exigirVisitante(): void
    {
        if (self::usuarioId() !== null) {
            redirecionar('perfil.php');
        }
    }
}