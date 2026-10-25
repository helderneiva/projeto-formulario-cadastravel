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

$email = $_POST['email'] ?? null;
$senha = $_POST['senha'] ?? null;

if (
    !is_string($email)
    || !is_string($senha)
    || trim($email) === ''
    || $senha === ''
) {
    $erros[] = 'Informe o e-mail e a senha.';
}

if ($erros === []) {
    try {
        $repositorio = new UsuarioRepository(Database::conectar());
        $usuario = $repositorio->buscarPorEmail(trim($email));

        if ($usuario !== null && $usuario->verificarSenha($senha)) {
            Auth::logar((int) $usuario->getId());
            redirecionar('perfil.php');
        }

        if ($usuario === null) {
            // E-mail inexistente: gasta um tempo parecido com o de uma
            // verificação real, para que a demora não revele quais
            // e-mails existem no sistema.
            password_hash($senha, PASSWORD_DEFAULT);
        }

        // Mesma mensagem para e-mail inexistente e senha errada.
        $erros[] = 'E-mail ou senha incorretos.';

    } catch (\PDOException | \ValueError | \InvalidArgumentException $e) {
        // Detalhes técnicos vão para o log, nunca para o usuário.
        error_log('Erro ao autenticar: ' . $e->getMessage());
        $erros[] = 'Não foi possível entrar agora. '
            . 'Tente novamente mais tarde.';
    }
}

// Falhou: devolve ao formulário com a mensagem e o e-mail digitado
// (a senha nunca é devolvida).
Flash::erros($erros);
Flash::antigos([
    'email' => is_string($email) ? $email : '',
]);
redirecionar('login.php');