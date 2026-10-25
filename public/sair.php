<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;

// O logout só vale por formulário (POST) com token CSRF: assim, um link ou
// imagem em outro site não consegue deslogar o usuário.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('login.php');
}

if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
    Flash::erros(['A sessão do formulário expirou ou é inválida. Tente novamente.']);
    redirecionar('perfil.php');
}

Auth::sair();
Flash::sucesso('Você saiu da sua conta.');
redirecionar('login.php');