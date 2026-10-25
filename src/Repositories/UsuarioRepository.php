<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\TipoUsuario;
use App\Exceptions\EmailJaCadastradoException;
use App\Models\Usuario;
use PDO;
use PDOException;

final class UsuarioRepository
{
    private const ERRO_MYSQL_DUPLICADO = 1062;

    private const COLUNAS = 'id, nome, email, senha_hash, tipo, foto';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Grava o usuário no banco e retorna o ID gerado.
     *
     * @throws EmailJaCadastradoException
     */
    public function salvar(Usuario $usuario): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, tipo)
             VALUES (:nome, :email, :senha_hash, :tipo)'
        );

        try {
            $stmt->execute([
                'nome'       => $usuario->getNome(),
                'email'      => $usuario->getEmail(),
                'senha_hash' => $usuario->getSenhaHash(),
                'tipo'       => $usuario->getTipo()->value,
            ]);
        } catch (PDOException $e) {
            // A chave UNIQUE do e-mail barrou a duplicidade.
            if (($e->errorInfo[1] ?? null) === self::ERRO_MYSQL_DUPLICADO) {
                throw new EmailJaCadastradoException();
            }

            throw $e;
        }

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Procura um usuário pelo e-mail. Retorna null se não existir.
     */
    public function buscarPorEmail(string $email): ?Usuario
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::COLUNAS . '
             FROM usuarios
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        $linha = $stmt->fetch();

        return is_array($linha) ? $this->montarUsuario($linha) : null;
    }

    /**
     * Procura um usuário pelo ID. Retorna null se não existir.
     */
    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::COLUNAS . '
             FROM usuarios
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $linha = $stmt->fetch();

        return is_array($linha) ? $this->montarUsuario($linha) : null;
    }

    /**
     * Atualiza o nome do arquivo da foto de perfil (null remove a foto).
     */
    public function atualizarFoto(int $id, ?string $foto): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET foto = :foto WHERE id = :id'
        );
        $stmt->execute([
            'foto' => $foto,
            'id'   => $id,
        ]);
    }

    /**
     * Converte uma linha do banco em objeto Usuario.
     *
     * @param array<string, mixed> $linha
     */
    private function montarUsuario(array $linha): Usuario
    {
        return Usuario::reconstituir(
            id: (int) $linha['id'],
            email: (string) $linha['email'],
            nome: (string) $linha['nome'],
            senhaHash: (string) $linha['senha_hash'],
            tipo: TipoUsuario::from((string) $linha['tipo']),
            foto: $linha['foto'] !== null ? (string) $linha['foto'] : null
        );
    }
}