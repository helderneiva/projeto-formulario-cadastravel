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
$identificadorAntigo = $flash['antigos']['identificador'] ?? '';

$titulo = 'Entrar';
$estilos = ['auth.css'];
$classeBody = 'pagina-auth';
$scripts = ['mostrar-senha.js'];
$tituloErros = 'Não foi possível entrar:';

require dirname(__DIR__) . '/templates/header.php';
?>
    <main class="conteudo">
        <h1>Entrar</h1>

        <?php require dirname(__DIR__) . '/templates/alertas.php'; ?>

        <form class="cartao" action="autenticar.php" method="post">
            <?= Csrf::campo() ?>

            <div class="campo">
                <label for="identificador">User ID ou e-mail</label>
                <input
                    type="text"
                    name="identificador"
                    id="identificador"
                    value="<?= e($identificadorAntigo) ?>"
                    autocomplete="username"
                    autocapitalize="none"
                    spellcheck="false"
                    maxlength="254"
                    placeholder="Seu user id ou e-mail"
                    required
                >
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    name="senha"
                    id="senha"
                    data-campo-senha
                    autocomplete="current-password"
                    placeholder="Digite sua senha"
                    required
                >
            </div>

            <div class="campo-opcao" data-mostrar-senha-opcao hidden>
                <input type="checkbox" id="mostrar-senha" data-mostrar-senha>
                <label for="mostrar-senha">Mostrar senha</label>
            </div>

            <button class="botao botao-bloco" type="submit">Entrar</button>
        </form>

        <p class="auth-rodape">
            Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a>
        </p>
    </main>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>