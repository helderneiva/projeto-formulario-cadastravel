<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;

Auth::exigirVisitante();

$flash = Flash::ler();
$erros = $flash['erros'];
$sucesso = $flash['sucesso'];
$nomeAntigo = $flash['antigos']['nome'] ?? '';
$emailAntigo = $flash['antigos']['email'] ?? '';

$titulo = 'Cadastro';
$estilos = ['auth.css'];
$classeBody = 'pagina-auth';

require dirname(__DIR__) . '/templates/header.php';
?>
    <main class="conteudo">
        <h1>Criar conta</h1>

        <?php require dirname(__DIR__) . '/templates/alertas.php'; ?>

        <form class="cartao" action="processar.php" method="post">
            <?= Csrf::campo() ?>

            <div class="campo">
                <label for="nome">Nome completo</label>
                <input
                    type="text"
                    name="nome"
                    id="nome"
                    value="<?= e($nomeAntigo) ?>"
                    autocomplete="name"
                    maxlength="150"
                    placeholder="Digite seu nome completo"
                    required
                >
            </div>

            <div class="campo">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="<?= e($emailAntigo) ?>"
                    autocomplete="email"
                    maxlength="254"
                    placeholder="seuemail@exemplo.com"
                    required
                >
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    name="senha"
                    id="senha"
                    autocomplete="new-password"
                    minlength="12"
                    maxlength="72"
                    placeholder="Pelo menos 12 caracteres"
                    aria-describedby="senha-ajuda"
                    required
                >
                <small class="ajuda" id="senha-ajuda">
                    Use no mínimo 12 caracteres. Uma frase fácil de lembrar
                    é uma boa senha.
                </small>
            </div>

            <div class="campo">
                <label for="senha_confirmacao">Confirmar senha</label>
                <input
                    type="password"
                    name="senha_confirmacao"
                    id="senha_confirmacao"
                    autocomplete="new-password"
                    minlength="12"
                    maxlength="72"
                    placeholder="Digite a senha novamente"
                    required
                >
            </div>

            <button class="botao botao-bloco" type="submit">Cadastrar</button>
        </form>

        <p class="auth-rodape">
            Já tem conta? <a href="login.php">Entrar</a>
        </p>
    </main>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>