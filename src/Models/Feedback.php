<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use InvalidArgumentException;

final class Feedback
{
    private const MENSAGEM_MIN_CARACTERES = 10;
    private const MENSAGEM_MAX_CARACTERES = 1000;
    private const NOTA_MIN = 0;
    private const NOTA_MAX = 10;

    private function __construct(
        private readonly ?int $id,
        private readonly int $usuarioId,
        private readonly string $mensagem,
        private readonly int $nota,
        private readonly ?DateTimeImmutable $criadoEm
    ) {
    }

    /**
     * Cria um feedback novo, validando mensagem e nota.
     */
    public static function criar(
        int $usuarioId,
        string $mensagem,
        int $nota
    ): self {
        if ($usuarioId < 1) {
            throw new InvalidArgumentException('Usuário inválido.');
        }

        return new self(
            id: null,
            usuarioId: $usuarioId,
            mensagem: self::validarMensagem($mensagem),
            nota: self::validarNota($nota),
            criadoEm: null
        );
    }

    /**
     * Recria um feedback que já existe no banco.
     */
    public static function reconstituir(
        int $id,
        int $usuarioId,
        string $mensagem,
        int $nota,
        string $criadoEm
    ): self {
        return new self(
            id: $id,
            usuarioId: $usuarioId,
            mensagem: $mensagem,
            nota: $nota,
            criadoEm: new DateTimeImmutable($criadoEm)
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuarioId;
    }

    public function getMensagem(): string
    {
        return $this->mensagem;
    }

    public function getNota(): int
    {
        return $this->nota;
    }

    public function getCriadoEm(): ?DateTimeImmutable
    {
        return $this->criadoEm;
    }

    private static function validarMensagem(string $mensagem): string
    {
        $mensagem = trim($mensagem);

        if (!mb_check_encoding($mensagem, 'UTF-8')) {
            throw new InvalidArgumentException(
                'O feedback contém caracteres inválidos.'
            );
        }

        // Recusa caracteres de controle, exceto quebra de linha e tabulação.
        if (preg_match('/[^\P{Cc}\n\r\t]/u', $mensagem) === 1) {
            throw new InvalidArgumentException(
                'O feedback contém caracteres inválidos.'
            );
        }

        $tamanho = mb_strlen($mensagem, 'UTF-8');

        if ($tamanho < self::MENSAGEM_MIN_CARACTERES) {
            throw new InvalidArgumentException(
                'O feedback deve ter pelo menos 10 caracteres.'
            );
        }

        if ($tamanho > self::MENSAGEM_MAX_CARACTERES) {
            throw new InvalidArgumentException(
                'O feedback não pode ultrapassar 1000 caracteres.'
            );
        }

        return $mensagem;
    }

    private static function validarNota(int $nota): int
    {
        if ($nota < self::NOTA_MIN || $nota > self::NOTA_MAX) {
            throw new InvalidArgumentException(
                'A nota deve estar entre 0 e 10.'
            );
        }

        return $nota;
    }
}