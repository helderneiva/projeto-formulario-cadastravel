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
$scripts = ['perfil.js'];

require dirname(__DIR__) . '/templates/header.php';
?>
    <main class="conteudo">
        <header class="topo-perfil">
            <h1>Meu Perfil</h1>

            <form action="sair.php" method="post">
                <?= Csrf::campo() ?>
                <button class="botao botao-secundario" type="submit">Sair</button>
            </form>
        </header>

        <?php require dirname(__DIR__) . '/templates/alertas.php'; ?>

        <?php if ($usuario !== null): ?>
            <section class="cartao perfil-resumo" aria-labelledby="titulo-dados">
                <?php if ($usuario->getFoto() !== null): ?>
                    <img
                        class="avatar"
                        src="uploads/avatars/<?= e($usuario->getFoto()) ?>"
                        alt="Foto de perfil de <?= e($usuario->getNome()) ?>"
                        width="112"
                        height="112"
                    >
                <?php else: ?>
                    <div class="avatar avatar-inicial" aria-hidden="true">
                        <?= e(mb_strtoupper(mb_substr($usuario->getNome(), 0, 1, 'UTF-8'), 'UTF-8')) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <h2 id="titulo-dados">Meus dados</h2>
                    <dl class="perfil-dados">
                        <dt>Nome</dt>
                        <dd><?= e($usuario->getNome()) ?></dd>

                        <dt>E-mail</dt>
                        <dd><?= e($usuario->getEmail()) ?></dd>

                        <dt>Tipo de conta</dt>
                        <dd><?= $usuario->isAdmin() ? 'Administrador' : 'Comum' ?></dd>
                    </dl>
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

                    <button class="botao" type="submit">Salvar foto</button>
                </form>
            </section>

            <section class="cartao" aria-labelledby="titulo-feedback">
                <h2 id="titulo-feedback">Deixe seu feedback</h2>

                <form action="salvar_feedback.php" method="post">
                    <?= Csrf::campo() ?>

                    <div class="campo">
                        <label for="mensagem">O que posso melhorar?</label>
                        <textarea
                            name="mensagem"
                            id="mensagem"
                            rows="5"
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

                    <div class="campo">
                        <label for="nota">
                            Nota para o projeto:
                            <output id="nota-valor" for="nota"><?= $notaAntiga ?></output>
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
        <?php endif; ?>
    </main>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>