<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\TipoUsuario;
use App\Exceptions\EmailJaCadastradoException;
use App\Exceptions\LoginJaCadastradoException;
use App\Models\Usuario;
use PDO;
use PDOException;

final class UsuarioRepository
{
    private const ERRO_MYSQL_DUPLICADO = 1062;

    // Nome da chave UNIQUE do user id (veja o schema.sql).
    private const CHAVE_LOGIN = 'uq_usuarios_login';

    private const COLUNAS = 'id, nome, login, email, data_nascimento, senha_hash, tipo, foto';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Grava o usuário no banco e retorna o ID gerado.
     *
     * @throws EmailJaCadastradoException
     * @throws LoginJaCadastradoException
     */
    public function salvar(Usuario $usuario): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, login, email, data_nascimento, senha_hash, tipo)
             VALUES (:nome, :login, :email, :data_nascimento, :senha_hash, :tipo)'
        );

        try {
            $stmt->execute([
                'nome'            => $usuario->getNome(),
                'login'           => $usuario->getLogin(),
                'email'           => $usuario->getEmail(),
                'data_nascimento' => $usuario->getDataNascimento()?->format('Y-m-d'),
                'senha_hash'      => $usuario->getSenhaHash(),
                'tipo'            => $usuario->getTipo()->value,
            ]);
        } catch (PDOException $e) {
            // Uma chave UNIQUE barrou a duplicidade: descobrimos qual.
            if (($e->errorInfo[1] ?? null) === self::ERRO_MYSQL_DUPLICADO) {
                if ($this->duplicidadeDoLogin($e)) {
                    throw new LoginJaCadastradoException();
                }

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
     * Procura um usuário pelo user id. Retorna null se não existir.
     */
    public function buscarPorLogin(string $login): ?Usuario
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::COLUNAS . '
             FROM usuarios
             WHERE login = :login
             LIMIT 1'
        );
        $stmt->execute(['login' => $login]);

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
     * Grava o novo hash de senha do usuário.
     */
    public function atualizarSenha(int $id, string $senhaHash): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id'
        );
        $stmt->execute([
            'senha_hash' => $senhaHash,
            'id'         => $id,
        ]);
    }

    /**
     * Diz se o erro de chave duplicada foi causado pelo user id.
     *
     * A mensagem do MySQL/MariaDB termina com o nome da chave violada
     * ("... for key 'usuarios.uq_usuarios_login'"); olhar só o final evita
     * confundir com o valor digitado, que aparece no começo da mensagem.
     */
    private function duplicidadeDoLogin(PDOException $e): bool
    {
        $mensagem = rtrim((string) ($e->errorInfo[2] ?? ''), "' \t\r\n");

        return str_ends_with($mensagem, self::CHAVE_LOGIN);
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
            foto: $linha['foto'] !== null ? (string) $linha['foto'] : null,
            login: $linha['login'] !== null ? (string) $linha['login'] : null,
            dataNascimento: $linha['data_nascimento'] !== null
                ? (string) $linha['data_nascimento']
                : null
        );
    }
}