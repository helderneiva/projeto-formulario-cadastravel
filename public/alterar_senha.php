<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Config\Database;
use App\Repositories\UsuarioRepository;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;

$usuarioId = Auth::exigirLogin();

// Aceita somente requisições enviadas pelo formulário.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('perfil.php');
}

$erros = [];

if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
    $erros[] = 'A sessão do formulário expirou ou é inválida. Tente novamente.';
}

$senhaAtual = $_POST['senha_atual'] ?? null;
$novaSenha = $_POST['nova_senha'] ?? null;
$confirmacao = $_POST['nova_senha_confirmacao'] ?? null;

if (
    !is_string($senhaAtual)
    || !is_string($novaSenha)
    || !is_string($confirmacao)
) {
    $erros[] = 'Preencha todos os campos corretamente.';
} elseif ($senhaAtual === '') {
    $erros[] = 'Informe a sua senha atual.';
} elseif ($novaSenha !== $confirmacao) {
    $erros[] = 'A nova senha e a confirmação não são iguais.';
} elseif ($novaSenha === $senhaAtual) {
    $erros[] = 'A nova senha deve ser diferente da senha atual.';
}

if ($erros === []) {
    try {
        $repositorio = new UsuarioRepository(Database::conectar());
        $usuario = $repositorio->buscarPorId($usuarioId);

        // A conta foi apagada enquanto a sessão estava aberta.
        if ($usuario === null) {
            Auth::sair();
            Flash::erros(['Sua conta não foi encontrada. Faça login novamente.']);
            redirecionar('login.php');
        }

        // Exigir a senha atual impede que alguém com acesso a uma
        // sessão esquecida aberta troque a senha da conta.
        if (!$usuario->verificarSenha($senhaAtual)) {
            $erros[] = 'A senha atual está incorreta.';
        } else {
            // Valida a nova senha (tamanho mínimo e máximo) e gera o hash.
            $usuario->alterarSenha($novaSenha);
            $repositorio->atualizarSenha($usuarioId, $usuario->getSenhaHash());

            // Novo identificador de sessão e novo token CSRF após a troca.
            Auth::logar($usuarioId);
            Flash::sucesso('Senha alterada com sucesso!');
            redirecionar('perfil.php');
        }

    } catch (\InvalidArgumentException $e) {
        // As mensagens das regras da senha foram escritas para o usuário.
        $erros[] = $e->getMessage();

    } catch (\PDOException | \ValueError $e) {
        // Detalhes técnicos vão para o log, nunca para o usuário.
        error_log('Erro ao alterar a senha: ' . $e->getMessage());
        $erros[] = 'Não foi possível alterar a senha agora. '
            . 'Tente novamente mais tarde.';
    }
}

// As senhas digitadas nunca são devolvidas ao formulário. O aviso abaixo
// só pede para o perfil reabrir o bloco "Alterar senha" e mostrar o erro.
Flash::erros($erros);
Flash::antigos(['abrir_senha' => '1']);
redirecionar('perfil.php');