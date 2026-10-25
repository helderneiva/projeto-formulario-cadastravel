<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Config\Database;
use App\Repositories\UsuarioRepository;
use App\Services\UploadFoto;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;

$usuarioId = Auth::exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('perfil.php');
}

$erros = [];

if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
    $erros[] = 'A sessão do formulário expirou ou é inválida. Tente novamente.';
}

$arquivo = $_FILES['foto'] ?? null;

if (!is_array($arquivo)) {
    $erros[] = 'Selecione uma imagem para enviar.';
}

if ($erros === []) {
    $uploader = new UploadFoto(__DIR__ . '/uploads/avatars');
    $novaFoto = null;

    try {
        $novaFoto = $uploader->salvar($arquivo);

        $repositorio = new UsuarioRepository(Database::conectar());
        $fotoAntiga = $repositorio->buscarPorId($usuarioId)?->getFoto();

        $repositorio->atualizarFoto($usuarioId, $novaFoto);

        // Só apaga a foto antiga depois de o banco aceitar a nova.
        $uploader->remover($fotoAntiga);

        Flash::sucesso('Foto de perfil atualizada com sucesso!');
        redirecionar('perfil.php');

    } catch (\InvalidArgumentException $e) {
        // Problema com o arquivo enviado: a mensagem é para o usuário.
        $erros[] = $e->getMessage();

    } catch (\PDOException | \RuntimeException $e) {
        // Falha do servidor: desfaz o arquivo salvo e registra no log.
        $uploader->remover($novaFoto);
        error_log('Erro ao salvar a foto: ' . $e->getMessage());
        $erros[] = 'Não foi possível salvar a foto agora. '
            . 'Tente novamente mais tarde.';
    }
}

Flash::erros($erros);
redirecionar('perfil.php');