<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Config\Database;
use App\Models\Feedback;
use App\Repositories\FeedbackRepository;
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

$mensagem = $_POST['mensagem'] ?? null;
$notaBruta = $_POST['nota'] ?? null;

if (!is_string($mensagem)) {
    $erros[] = 'Escreva o seu feedback.';
}

// O input range envia texto ("7"); aqui vira número inteiro.
$nota = is_string($notaBruta)
    ? filter_var($notaBruta, FILTER_VALIDATE_INT)
    : false;

if ($nota === false) {
    $erros[] = 'Informe uma nota válida entre 0 e 10.';
}

if ($erros === []) {
    try {
        $feedback = Feedback::criar($usuarioId, $mensagem, $nota);

        $repositorio = new FeedbackRepository(Database::conectar());
        $repositorio->salvar($feedback);

        Flash::sucesso('Obrigado! Seu feedback foi enviado.');
        redirecionar('perfil.php');

    } catch (\InvalidArgumentException $e) {
        $erros[] = $e->getMessage();

    } catch (\PDOException $e) {
        error_log('Erro ao salvar o feedback: ' . $e->getMessage());
        $erros[] = 'Não foi possível enviar o feedback agora. '
            . 'Tente novamente mais tarde.';
    }
}

// Houve erro: devolve o que foi digitado para o usuário não perder o texto.
Flash::erros($erros);
Flash::antigos([
    'mensagem' => is_string($mensagem) ? $mensagem : '',
    'nota'     => is_string($notaBruta) ? $notaBruta : '5',
]);
redirecionar('perfil.php');