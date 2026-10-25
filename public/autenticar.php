<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Config\Database;
use App\Repositories\UsuarioRepository;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;

// Aceita somente requisições enviadas pelo formulário.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('login.php');
}

$erros = [];

if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
    $erros[] = 'A sessão do formulário expirou ou é inválida. Tente novamente.';
}

// Pode ser o user id ou o e-mail.
$identificador = $_POST['identificador'] ?? null;
$senha = $_POST['senha'] ?? null;

if (
    !is_string($identificador)
    || !is_string($senha)
    || trim($identificador) === ''
    || $senha === ''
) {
    $erros[] = 'Informe o user id (ou e-mail) e a senha.';
}

if ($erros === []) {
    try {
        $identificador = trim($identificador);
        $repositorio = new UsuarioRepository(Database::conectar());

        // O user id não pode conter "@"; portanto, quem tem "@" é e-mail.
        $usuario = str_contains($identificador, '@')
            ? $repositorio->buscarPorEmail($identificador)
            : $repositorio->buscarPorLogin(strtolower($identificador));

        if ($usuario !== null && $usuario->verificarSenha($senha)) {
            Auth::logar((int) $usuario->getId());
            redirecionar('perfil.php');
        }

        if ($usuario === null) {
            // Usuário inexistente: gasta um tempo parecido com o de uma
            // verificação real, para que a demora não revele quais
            // user ids e e-mails existem no sistema.
            password_hash($senha, PASSWORD_DEFAULT);
        }

        // Mesma mensagem para usuário inexistente e senha errada.
        $erros[] = 'User ID, e-mail ou senha incorretos.';

    } catch (\PDOException | \ValueError | \InvalidArgumentException $e) {
        // Detalhes técnicos vão para o log, nunca para o usuário.
        error_log('Erro ao autenticar: ' . $e->getMessage());
        $erros[] = 'Não foi possível entrar agora. '
            . 'Tente novamente mais tarde.';
    }
}

// Falhou: devolve ao formulário com a mensagem e o identificador digitado
// (a senha nunca é devolvida).
Flash::erros($erros);
Flash::antigos([
    'identificador' => is_string($identificador) ? $identificador : '',
]);
redirecionar('login.php');