<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Config\Database;
use App\Repositories\FeedbackRepository;
use App\Repositories\UsuarioRepository;
use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;

$usuarioId = Auth::exigirLogin();

$flash = Flash::ler();
$erros = $flash['erros'];
$sucesso = $flash['sucesso'];

// Dados devolvidos pelo formulário de feedback após um erro.
$mensagemAntiga = $flash['antigos']['mensagem'] ?? '';
$notaAntiga = filter_var(
    $flash['antigos']['nota'] ?? 5,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 0, 'max_range' => 10, 'default' => 5]]
);

// Mantém o bloco "Alterar senha" aberto quando houve erro nele.
$abrirSenha = ($flash['antigos']['abrir_senha'] ?? '') === '1';

$usuario = null;
$feedbacks = [];

try {
    $pdo = Database::conectar();

    $usuario = (new UsuarioRepository($pdo))->buscarPorId($usuarioId);

    // A conta foi apagada enquanto a sessão estava aberta.
    if ($usuario === null) {
        Auth::sair();
        Flash::erros(['Sua conta não foi encontrada. Faça login novamente.']);
        redirecionar('login.php');
    }

    $feedbacks = (new FeedbackRepository($pdo))->listarPorUsuario($usuarioId);
} catch (\PDOException | \ValueError | \InvalidArgumentException $e) {
    error_log('Erro ao carregar o perfil: ' . $e->getMessage());
    $erros[] = 'Não foi possível carregar seu perfil agora. '
        . 'Tente novamente mais tarde.';
}

$titulo = 'Meu Perfil';
$estilos = ['perfil.css'];
$classeBody = 'pagina-perfil';
$scripts = ['perfil.js', 'mostrar-senha.js'];

require dirname(__DIR__) . '/templates/header.php';
?>
    <header class="topo-perfil">
        <h1>Meu Perfil</h1>

        <form action="sair.php" method="post">
            <?= Csrf::campo() ?>
            <button class="botao botao-sair" type="submit">Sair</button>
        </form>
    </header>

    <main class="conteudo">
        <?php require dirname(__DIR__) . '/templates/alertas.php'; ?>

        <?php if ($usuario !== null): ?>
            <div class="perfil-colunas">
                <div class="perfil-coluna">
                    <section class="cartao perfil-resumo" aria-labelledby="titulo-dados">
                        <div class="perfil-foto">
                            <div class="avatar-moldura">
                                <?php if ($usuario->getFoto() !== null): ?>
                                    <img
                                        class="avatar"
                                        src="uploads/avatars/<?= e($usuario->getFoto()) ?>"
                                        alt="Foto de perfil de <?= e($usuario->getNome()) ?>"
                                        width="152"
                                        height="152"
                                    >
                                <?php else: ?>
                                    <div class="avatar avatar-inicial" aria-hidden="true">
                                        <?= e(mb_strtoupper(mb_substr($usuario->getNome(), 0, 1, 'UTF-8'), 'UTF-8')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form
                                class="perfil-foto-form"
                                action="salvar_foto.php"
                                method="post"
                                enctype="multipart/form-data"
                            >
                                <?= Csrf::campo() ?>

                                <div class="campo">
                                    <label for="foto">Alterar foto de perfil</label>
                                    <input
                                        type="file"
                                        name="foto"
                                        id="foto"
                                        accept="image/jpeg,image/png,image/webp"
                                        aria-describedby="foto-ajuda"
                                        required
                                    >
                                    <small class="ajuda" id="foto-ajuda">
                                        JPG, PNG ou WebP, com até 2 MB.
                                    </small>
                                    <img
                                        id="previa-foto"
                                        class="avatar avatar-previa"
                                        alt="Prévia da nova foto"
                                        width="112"
                                        height="112"
                                        hidden
                                    >
                                </div>

                                <button class="botao botao-bloco" type="submit">Salvar foto</button>
                            </form>
                        </div>

                        <div class="perfil-info">
                            <h2 id="titulo-dados">Meus dados</h2>
                            <?php $idade = $usuario->getIdade(); ?>
                            <dl class="perfil-dados">
                                <div class="dado dado-largo dado-nome">
                                    <dt>Nome</dt>
                                    <dd><?= e($usuario->getNome()) ?></dd>
                                </div>
                                <div class="dado">
                                    <dt>User ID</dt>
                                    <dd><?= e($usuario->getLogin() ?? 'Não definido') ?></dd>
                                </div>
                                <div class="dado">
                                    <dt>Idade</dt>
                                    <dd><?= $idade !== null ? $idade . ' anos' : 'Não informada' ?></dd>
                                </div>
                                <div class="dado dado-largo">
                                    <dt>E-mail</dt>
                                    <dd><?= e($usuario->getEmail()) ?></dd>
                                </div>
                                <div class="dado dado-largo dado-tipo">
                                    <dt>Tipo de conta</dt>
                                    <dd><?= $usuario->isAdmin() ? 'Administrador' : 'Comum' ?></dd>
                                </div>
                            </dl>
                        </div>
                    </section>

                <section class="cartao" aria-labelledby="titulo-senha">
                    <details class="recolhivel"<?= $abrirSenha ? ' open' : '' ?>>
                        <summary id="titulo-senha">Alterar senha</summary>

                        <form action="alterar_senha.php" method="post">
                            <?= Csrf::campo() ?>

                            <div class="campo">
                                <label for="senha_atual">Senha atual</label>
                                <input
                                    type="password"
                                    name="senha_atual"
                                    id="senha_atual"
                                    data-campo-senha
                                    autocomplete="current-password"
                                    placeholder="Digite sua senha atual"
                                    required
                                >
                            </div>

                            <div class="campo">
                                <label for="nova_senha">Nova senha</label>
                                <input
                                    type="password"
                                    name="nova_senha"
                                    id="nova_senha"
                                    data-campo-senha
                                    autocomplete="new-password"
                                    minlength="12"
                                    maxlength="72"
                                    placeholder="Pelo menos 12 caracteres"
                                    aria-describedby="nova-senha-ajuda"
                                    required
                                >
                                <small class="ajuda" id="nova-senha-ajuda">
                                    Use no mínimo 12 caracteres.
                                </small>
                            </div>

                            <div class="campo">
                                <label for="nova_senha_confirmacao">Confirmar nova senha</label>
                                <input
                                    type="password"
                                    name="nova_senha_confirmacao"
                                    id="nova_senha_confirmacao"
                                    data-campo-senha
                                    autocomplete="new-password"
                                    minlength="12"
                                    maxlength="72"
                                    placeholder="Digite a nova senha novamente"
                                    required
                                >
                            </div>

                            <div class="campo-opcao" data-mostrar-senha-opcao hidden>
                                <input type="checkbox" id="mostrar-senha" data-mostrar-senha>
                                <label for="mostrar-senha">Mostrar senhas</label>
                            </div>

                            <button class="botao" type="submit">Alterar senha</button>
                        </form>
                    </details>
                </section>
                </div>

                <div class="perfil-coluna">
                <section class="cartao" aria-labelledby="titulo-feedback">
                    <h2 id="titulo-feedback">Deixe seu feedback</h2>

                    <form action="salvar_feedback.php" method="post">
                        <?= Csrf::campo() ?>

                        <div class="campo">
                            <label for="mensagem">O que posso melhorar?</label>
                            <textarea
                                name="mensagem"
                                id="mensagem"
                                rows="8"
                                minlength="10"
                                maxlength="1000"
                                placeholder="Escreva sua sugestão ou crítica (mínimo de 10 caracteres)"
                                aria-describedby="mensagem-ajuda"
                                required
                            ><?= e($mensagemAntiga) ?></textarea>
                            <small class="ajuda" id="mensagem-ajuda">
                                <span id="contador">0</span>/1000 caracteres
                            </small>
                        </div>

                        <div class="campo campo-nota">
                            <label for="nota">
                                Nota para o projeto:
                                <output id="nota-valor" for="nota"><?= $notaAntiga ?></output>
                                <span id="nota-emoji" class="nota-emoji" aria-hidden="true"></span>
                            </label>
                            <input
                                type="range"
                                name="nota"
                                id="nota"
                                min="0"
                                max="10"
                                step="1"
                                value="<?= $notaAntiga ?>"
                            >
                            <div class="nota-escala" aria-hidden="true">
                                <span>0</span>
                                <span>10</span>
                            </div>
                        </div>

                        <button class="botao" type="submit">Enviar feedback</button>
                    </form>
                </section>

                <section class="cartao" aria-labelledby="titulo-historico">
                    <h2 id="titulo-historico">Meus feedbacks enviados</h2>

                    <?php if ($feedbacks === []): ?>
                        <p class="feedback-vazio">
                            Você ainda não enviou nenhum feedback.
                        </p>
                    <?php else: ?>
                        <ul class="lista-feedbacks">
                            <?php foreach ($feedbacks as $feedback): ?>
                                <li class="feedback-item">
                                    <div class="feedback-cabecalho">
                                        <span class="feedback-nota">
                                            Nota: <?= $feedback->getNota() ?>/10
                                        </span>
                                        <time class="feedback-data">
                                            <?= e($feedback->getCriadoEm()?->format('d/m/Y H:i')) ?>
                                        </time>
                                    </div>
                                    <p><?= nl2br(e($feedback->getMensagem())) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
                </div>
            </div>
        <?php endif; ?>
    </main>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>