<?php

declare(strict_types=1);

namespace App\Services;

use finfo;
use InvalidArgumentException;
use RuntimeException;

/**
 * Valida e guarda a foto de perfil enviada pelo usuário.
 *
 * - InvalidArgumentException: problema com o arquivo enviado (mostrar ao usuário).
 * - RuntimeException: falha do servidor (registrar no log).
 */
final class UploadFoto
{
    private const TAMANHO_MAXIMO = 2 * 1024 * 1024; // 2 MB
    private const LADO_MAXIMO = 4000;               // pixels

    /** Tipo real do arquivo => extensão com que será salvo. */
    private const TIPOS_PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(private readonly string $diretorio)
    {
    }

    /**
     * Valida e salva a imagem. Retorna o nome do arquivo gerado.
     *
     * @param array<string, mixed> $arquivo Um item de $_FILES.
     */
    public function salvar(array $arquivo): string
    {
        $this->validarErroDeEnvio($arquivo['error'] ?? null);

        $temporario = $arquivo['tmp_name'] ?? null;

        if (!is_string($temporario) || !is_uploaded_file($temporario)) {
            throw new InvalidArgumentException('Envio de arquivo inválido.');
        }

        $tamanho = filesize($temporario);

        if ($tamanho === false || $tamanho > self::TAMANHO_MAXIMO) {
            throw new InvalidArgumentException(
                'A imagem deve ter no máximo 2 MB.'
            );
        }

        // O tipo é descoberto pelo CONTEÚDO do arquivo, não pelo nome
        // nem pelo tipo informado pelo navegador (ambos podem ser falsos).
        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($temporario);

        if (!is_string($tipo) || !isset(self::TIPOS_PERMITIDOS[$tipo])) {
            throw new InvalidArgumentException(
                'Formato não permitido. Envie uma imagem JPG, PNG ou WebP.'
            );
        }

        $dimensoes = getimagesize($temporario);

        if ($dimensoes === false) {
            throw new InvalidArgumentException(
                'O arquivo enviado não é uma imagem válida.'
            );
        }

        [$largura, $altura] = $dimensoes;

        if ($largura > self::LADO_MAXIMO || $altura > self::LADO_MAXIMO) {
            throw new InvalidArgumentException(
                'A imagem é grande demais (máximo de 4000 x 4000 pixels).'
            );
        }

        if (
            !is_dir($this->diretorio)
            && !mkdir($this->diretorio, 0755, true)
            && !is_dir($this->diretorio)
        ) {
            throw new RuntimeException(
                'Não foi possível criar a pasta de fotos.'
            );
        }

        // Nome aleatório: o nome original do arquivo nunca é usado.
        $nome = bin2hex(random_bytes(16))
            . '.' . self::TIPOS_PERMITIDOS[$tipo];

        $destino = $this->diretorio . DIRECTORY_SEPARATOR . $nome;

        if (!move_uploaded_file($temporario, $destino)) {
            throw new RuntimeException(
                'Não foi possível salvar a foto no servidor.'
            );
        }

        chmod($destino, 0644);

        return $nome;
    }

    /**
     * Apaga uma foto antiga. Ignora nomes fora do padrão que geramos,
     * o que impede apagar arquivos de outras pastas.
     */
    public function remover(?string $nome): void
    {
        if ($nome === null) {
            return;
        }

        if (preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $nome) !== 1) {
            return;
        }

        $caminho = $this->diretorio . DIRECTORY_SEPARATOR . $nome;

        if (is_file($caminho)) {
            unlink($caminho);
        }
    }

    private function validarErroDeEnvio(mixed $codigo): void
    {
        if (!is_int($codigo)) {
            throw new InvalidArgumentException('Envio de arquivo inválido.');
        }

        match ($codigo) {
            UPLOAD_ERR_OK => null,
            UPLOAD_ERR_NO_FILE => throw new InvalidArgumentException(
                'Selecione uma imagem para enviar.'
            ),
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => throw new InvalidArgumentException(
                'A imagem deve ter no máximo 2 MB.'
            ),
            default => throw new InvalidArgumentException(
                'Não foi possível receber o arquivo. Tente novamente.'
            ),
        };
    }
}