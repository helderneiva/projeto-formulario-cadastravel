<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoUsuario;
use DateTimeImmutable;
use InvalidArgumentException;

final class Usuario
{
    public const IDADE_MINIMA = 16;
    public const IDADE_MAXIMA = 120;

    private const EMAIL_MAX_BYTES = 254;
    private const NOME_MAX_CARACTERES = 150;
    // User id: 3 a 30 caracteres; letras minúsculas sem acento, números,
    // ponto ou sublinhado; começa com letra ou número. Como "@" não é
    // permitido, um user id nunca é confundido com um e-mail.
    private const LOGIN_PADRAO = '/^[a-z0-9][a-z0-9_.]{2,29}\z/';
    private const SENHA_MIN_CARACTERES = 12;
    private const SENHA_MAX_BYTES = 72; // limite do bcrypt

    private string $nome;

    private function __construct(
        private readonly ?int $id,
        private readonly string $email,
        string $nome,
        private string $senhaHash,
        private TipoUsuario $tipo,
        private readonly ?string $foto = null,
        private readonly ?string $login = null,
        private readonly ?DateTimeImmutable $dataNascimento = null
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
     *
     * O user id e a data de nascimento são obrigatórios aqui; só os
     * usuários antigos (reconstituídos do banco) podem não tê-los.
     */
    public static function criarNovo(
        string $email,
        string $nome,
        string $login,
        string $dataNascimento,
        string $senhaTextoPuro,
        TipoUsuario $tipo
    ): self {
        $loginValido = self::validarLogin($login);
        $nascimentoValido = self::validarDataNascimento($dataNascimento);
        self::validarSenha($senhaTextoPuro);

        return new self(
            id: null,
            email: trim($email),
            nome: $nome,
            senhaHash: self::gerarHash($senhaTextoPuro),
            tipo: $tipo,
            login: $loginValido,
            dataNascimento: $nascimentoValido
        );
    }

    /**
     * Recria um usuário que já existe no banco.
     *
     * Não valida a senha porque aqui só temos o hash (a senha em texto
     * puro nunca é guardada). Login e data de nascimento podem ser null
     * em contas criadas antes desses campos existirem.
     */
    public static function reconstituir(
        int $id,
        string $email,
        string $nome,
        string $senhaHash,
        TipoUsuario $tipo,
        ?string $foto = null,
        ?string $login = null,
        ?string $dataNascimento = null
    ): self {
        $nascimento = null;

        if ($dataNascimento !== null) {
            try {
                $nascimento = new DateTimeImmutable($dataNascimento);
            } catch (\Exception) {
                throw new InvalidArgumentException(
                    'A data de nascimento guardada é inválida.'
                );
            }
        }

        return new self(
            id: $id,
            email: $email,
            nome: $nome,
            senhaHash: $senhaHash,
            tipo: $tipo,
            foto: $foto,
            login: $login,
            dataNascimento: $nascimento
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

    /**
     * User id escolhido no cadastro, ou null em contas antigas.
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function getDataNascimento(): ?DateTimeImmutable
    {
        return $this->dataNascimento;
    }

    /**
     * Idade em anos completos, calculada a partir da data de nascimento.
     * Retorna null se a data não foi informada.
     */
    public function getIdade(?DateTimeImmutable $hoje = null): ?int
    {
        if ($this->dataNascimento === null) {
            return null;
        }

        $hoje ??= new DateTimeImmutable('today');

        return $this->dataNascimento->diff($hoje)->y;
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

    /**
     * Normaliza (minúsculas, sem espaços nas pontas) e valida o user id.
     */
    private static function validarLogin(string $login): string
    {
        $login = strtolower(trim($login));

        if (preg_match(self::LOGIN_PADRAO, $login) !== 1) {
            throw new InvalidArgumentException(
                'O user id deve ter de 3 a 30 caracteres, usando apenas '
                . 'letras sem acento, números, ponto ou sublinhado, e '
                . 'começar com letra ou número.'
            );
        }

        return $login;
    }

    /**
     * Valida a data de nascimento e a idade mínima. Aceita DD/MM/AAAA
     * (digitada pelo usuário) e AAAA-MM-DD (formato interno do navegador).
     */
    private static function validarDataNascimento(string $data): DateTimeImmutable
    {
        $data = trim($data);

        // DD/MM/AAAA -> AAAA-MM-DD
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})\z#', $data, $partes) === 1) {
            $data = $partes[3] . '-' . $partes[2] . '-' . $partes[1];
        }

        $nascimento = DateTimeImmutable::createFromFormat('!Y-m-d', $data);

        // Reformatar e comparar recusa datas que o PHP "corrigiria"
        // sozinho, como 31/02.
        if ($nascimento === false || $nascimento->format('Y-m-d') !== $data) {
            throw new InvalidArgumentException(
                'Informe uma data de nascimento válida.'
            );
        }

        $hoje = new DateTimeImmutable('today');

        if ($nascimento > $hoje) {
            throw new InvalidArgumentException(
                'A data de nascimento não pode estar no futuro.'
            );
        }

        $idade = $nascimento->diff($hoje)->y;

        if ($idade < self::IDADE_MINIMA) {
            throw new InvalidArgumentException(
                'É necessário ter pelo menos 16 anos para se cadastrar.'
            );
        }

        if ($idade > self::IDADE_MAXIMA) {
            throw new InvalidArgumentException(
                'Informe uma data de nascimento válida.'
            );
        }

        return $nascimento;
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