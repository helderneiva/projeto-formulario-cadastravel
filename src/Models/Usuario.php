<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoUsuario;
use InvalidArgumentException;

final class Usuario
{
    private const EMAIL_MAX_BYTES = 254;
    private const NOME_MAX_CARACTERES = 150;
    private const SENHA_MIN_CARACTERES = 12;
    private const SENHA_MAX_BYTES = 72; // limite do bcrypt

    private string $nome;

    private function __construct(
        private readonly ?int $id,
        private readonly string $email,
        string $nome,
        private string $senhaHash,
        private TipoUsuario $tipo,
        private readonly ?string $foto = null
    ) {
        if ($id !== null && $id < 1) {
            throw new InvalidArgumentException(
                'O ID deve ser um inteiro positivo.'
            );
        }

        // Validação do e-mail centralizada aqui: nenhum objeto inválido existe.
        if (
            $email === ''
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            throw new InvalidArgumentException(
                'O e-mail informado é inválido.'
            );
        }

        if (strlen($email) > self::EMAIL_MAX_BYTES) {
            throw new InvalidArgumentException(
                'O e-mail não pode ultrapassar 254 bytes.'
            );
        }

        if ($senhaHash === '') {
            throw new InvalidArgumentException(
                'O hash da senha não pode ser vazio.'
            );
        }

        $this->definirNome($nome);
    }

    /**
     * Cria um usuário novo a partir dos dados recebidos.
     */
    public static function criarNovo(
        string $email,
        string $nome,
        string $senhaTextoPuro,
        TipoUsuario $tipo
    ): self {
        self::validarSenha($senhaTextoPuro);

        return new self(
            id: null,
            email: trim($email),
            nome: $nome,
            senhaHash: self::gerarHash($senhaTextoPuro),
            tipo: $tipo
        );
    }

    /**
     * Recria um usuário que já existe no banco.
     *
     * Não valida a senha porque aqui só temos o hash (a senha em texto
     * puro nunca é guardada).
     */
    public static function reconstituir(
        int $id,
        string $email,
        string $nome,
        string $senhaHash,
        TipoUsuario $tipo,
        ?string $foto = null
    ): self {
        return new self(
            id: $id,
            email: $email,
            nome: $nome,
            senhaHash: $senhaHash,
            tipo: $tipo,
            foto: $foto
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    /**
     * Nome do arquivo da foto de perfil, ou null se não houver foto.
     */
    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function definirNome(string $nome): void
    {
        $nome = trim($nome);

        if ($nome === '') {
            throw new InvalidArgumentException(
                'O nome não pode ser vazio.'
            );
        }

        // Exige UTF-8 válido e recusa caracteres de controle
        // (quebras de linha, tabulação, etc.).
        if (
            !mb_check_encoding($nome, 'UTF-8')
            || preg_match('/\p{Cc}/u', $nome) === 1
        ) {
            throw new InvalidArgumentException(
                'O nome contém caracteres inválidos.'
            );
        }

        if (mb_strlen($nome, 'UTF-8') > self::NOME_MAX_CARACTERES) {
            throw new InvalidArgumentException(
                'O nome não pode ultrapassar 150 caracteres.'
            );
        }

        $this->nome = $nome;
    }

    public function alterarSenha(string $senhaTextoPuro): void
    {
        self::validarSenha($senhaTextoPuro);

        $this->senhaHash = self::gerarHash($senhaTextoPuro);
    }

    public function verificarSenha(string $senhaDigitada): bool
    {
        return password_verify($senhaDigitada, $this->senhaHash);
    }

    public function getSenhaHash(): string
    {
        return $this->senhaHash;
    }

    public function isAdmin(): bool
    {
        return $this->tipo === TipoUsuario::ADMIN;
    }

    public function getTipo(): TipoUsuario
    {
        return $this->tipo;
    }

    private static function gerarHash(string $senhaTextoPuro): string
    {
        // Desde o PHP 8.0, password_hash() lança ValueError em vez de
        // retornar false, então não é necessário checar "=== false".
        return password_hash($senhaTextoPuro, PASSWORD_DEFAULT);
    }

    private static function validarSenha(string $senha): void
    {
        if (!mb_check_encoding($senha, 'UTF-8')) {
            throw new InvalidArgumentException(
                'A senha contém caracteres inválidos.'
            );
        }

        // Mínimo contado em caracteres: é o que o usuário enxerga.
        if (mb_strlen($senha, 'UTF-8') < self::SENHA_MIN_CARACTERES) {
            throw new InvalidArgumentException(
                'A senha deve ter pelo menos 12 caracteres.'
            );
        }

        // Máximo contado em bytes: o bcrypt ignora o que passar de 72 bytes,
        // então recusamos em vez de aceitar uma senha cortada em silêncio.
        if (strlen($senha) > self::SENHA_MAX_BYTES) {
            throw new InvalidArgumentException(
                'A senha é longa demais (máximo de 72 bytes; '
                . 'acentos e emojis ocupam mais de 1 byte cada).'
            );
        }
    }
}