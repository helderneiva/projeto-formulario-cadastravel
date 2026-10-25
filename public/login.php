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
$emailAntigo = $flash['antigos']['email'] ?? '';

$titulo = 'Entrar';
$estilos = ['auth.css'];
$classeBody = 'pagina-auth';
$tituloErros = 'Não foi possível entrar:';

require dirname(__DIR__) . '/templates/header.php';
?>
    <main class="conteudo">
        <h1>Entrar</h1>

        <?php require dirname(__DIR__) . '/templates/alertas.php'; ?>

        <form class="cartao" action="autenticar.php" method="post">
            <?= Csrf::campo() ?>

            <div class="campo">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="<?= e($emailAntigo) ?>"
                    autocomplete="username"
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
                    autocomplete="current-password"
                    placeholder="Digite sua senha"
                    required
                >
            </div>

            <button class="botao botao-bloco" type="submit">Entrar</button>
        </form>

        <p class="auth-rodape">
            Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a>
        </p>
    </main>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>