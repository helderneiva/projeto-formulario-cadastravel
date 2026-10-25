<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Config\Database;
use App\Enums\TipoUsuario;
use App\Exceptions\EmailJaCadastradoException;
use App\Models\Usuario;
use App\Repositories\UsuarioRepository;
use App\Support\Csrf;
use App\Support\Flash;

// Aceita somente requisições enviadas pelo formulário.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('cadastro.php');
}

$erros = [];

if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
    $erros[] = 'A sessão do formulário expirou ou é inválida. Tente novamente.';
}

$nome = $_POST['nome'] ?? null;
$email = $_POST['email'] ?? null;
$senha = $_POST['senha'] ?? null;
$confirmacao = $_POST['senha_confirmacao'] ?? null;

if (
    !is_string($nome)
    || !is_string($email)
    || !is_string($senha)
    || !is_string($confirmacao)
) {
    $erros[] = 'Preencha todos os campos corretamente.';
} elseif ($senha !== $confirmacao) {
    $erros[] = 'A senha e a confirmação não são iguais.';
}

if ($erros === []) {
    try {
        $usuario = Usuario::criarNovo(
            email: $email,
            nome: $nome,
            senhaTextoPuro: $senha,
            tipo: TipoUsuario::COMUM
        );

        $repositorio = new UsuarioRepository(Database::conectar());
        $repositorio->salvar($usuario);

        // Cadastro concluído: o token é descartado e um novo nasce na
        // próxima visita.
        Csrf::renovar();
        Flash::sucesso('Cadastro realizado com sucesso! Faça login para continuar.');
        redirecionar('login.php');

    } catch (EmailJaCadastradoException | \InvalidArgumentException $e) {
        // As mensagens destas exceções foram escritas para o usuário.
        $erros[] = $e->getMessage();

    } catch (\ValueError $e) {
        $erros[] = 'Um dos dados informados não é válido.';

    } catch (\PDOException $e) {
        // Detalhes técnicos vão para o log, nunca para o usuário.
        error_log('Erro de banco de dados no cadastro: ' . $e->getMessage());
        $erros[] = 'Não foi possível concluir o cadastro agora. '
            . 'Tente novamente mais tarde.';
    }
}

// Houve erro: devolve ao formulário com as mensagens e os dados digitados
// (a senha nunca é devolvida).
Flash::erros($erros);
Flash::antigos([
    'nome'  => is_string($nome) ? $nome : '',
    'email' => is_string($email) ? $email : '',
]);
redirecionar('cadastro.php');