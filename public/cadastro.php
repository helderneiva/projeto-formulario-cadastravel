<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Models\Usuario;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;

Auth::exigirVisitante();

$flash = Flash::ler();
$erros = $flash['erros'];
$sucesso = $flash['sucesso'];
$nomeAntigo = $flash['antigos']['nome'] ?? '';
$loginAntigo = $flash['antigos']['login'] ?? '';
$emailAntigo = $flash['antigos']['email'] ?? '';
$nascimentoAntigo = $flash['antigos']['data_nascimento'] ?? '';

// Limites do campo de data (o servidor valida tudo de novo).
$hoje = new DateTimeImmutable('today');
$nascimentoMaximo = $hoje->modify('-' . Usuario::IDADE_MINIMA . ' years')->format('Y-m-d');
$nascimentoMinimo = $hoje->modify('-' . Usuario::IDADE_MAXIMA . ' years')->format('Y-m-d');

$titulo = 'Cadastro';
$estilos = ['auth.css'];
$classeBody = 'pagina-auth';
$scripts = ['mostrar-senha.js', 'data-nascimento.js'];

require dirname(__DIR__) . '/templates/header.php';
?>
    <main class="conteudo">
        <h1>Criar conta</h1>

        <?php require dirname(__DIR__) . '/templates/alertas.php'; ?>

        <form class="cartao" action="processar.php" method="post" novalidate>
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
                <label for="login">User ID</label>
                <input
                    type="text"
                    name="login"
                    id="login"
                    value="<?= e($loginAntigo) ?>"
                    autocomplete="username"
                    autocapitalize="none"
                    spellcheck="false"
                    minlength="3"
                    maxlength="30"
                    placeholder="Escolha seu user id"
                    aria-describedby="login-ajuda"
                    required
                >
                <small class="ajuda" id="login-ajuda">
                    De 3 a 30 caracteres: letras sem acento, números,
                    ponto ou sublinhado. Você entrará com ele.
                </small>
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
                <label for="data_nascimento">Data de nascimento</label>
                <div class="campo-data">
                    <input
                        type="text"
                        name="data_nascimento"
                        id="data_nascimento"
                        value="<?= e($nascimentoAntigo) ?>"
                        inputmode="numeric"
                        autocomplete="bday"
                        maxlength="10"
                        placeholder="dd/mm/aaaa"
                        aria-describedby="nascimento-ajuda"
                        required
                    >
                    <button
                        class="botao-calendario"
                        type="button"
                        id="abrir-calendario"
                        aria-label="Abrir o calendário"
                        hidden
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path
                                fill="currentColor"
                                d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm-2 8h14v10H5V10z"
                            />
                        </svg>
                    </button>
                    <input
                        type="date"
                        id="calendario-nascimento"
                        class="calendario-oculto"
                        min="<?= e($nascimentoMinimo) ?>"
                        max="<?= e($nascimentoMaximo) ?>"
                        tabindex="-1"
                        aria-hidden="true"
                    >
                </div>
                <small class="ajuda" id="nascimento-ajuda">
                    Digite no formato dd/mm/aaaa ou use o calendário.
                    É necessário ter pelo menos <?= Usuario::IDADE_MINIMA ?> anos.
                </small>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    name="senha"
                    id="senha"
                    data-campo-senha
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

            <div class="campo-opcao" data-mostrar-senha-opcao hidden>
                <input type="checkbox" id="mostrar-senha" data-mostrar-senha>
                <label for="mostrar-senha">Mostrar senha</label>
            </div>

            <div class="campo">
                <label for="senha_confirmacao">Confirmar senha</label>
                <input
                    type="password"
                    name="senha_confirmacao"
                    id="senha_confirmacao"
                    data-campo-senha
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