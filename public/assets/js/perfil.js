'use strict';

/**
 * Melhorias de usabilidade da página de perfil.
 * O formulário continua funcionando sem JavaScript; o servidor
 * sempre valida tudo novamente.
 */
document.addEventListener('DOMContentLoaded', () => {
    const TAMANHO_MAXIMO_FOTO = 2 * 1024 * 1024; // 2 MB

    // 1. Mostra o valor atual da barra de nota.
    const nota = document.getElementById('nota');
    const notaValor = document.getElementById('nota-valor');

    if (nota && notaValor) {
        const atualizarNota = () => {
            notaValor.textContent = nota.value;
        };

        nota.addEventListener('input', atualizarNota);
        atualizarNota();
    }

    // 2. Contador de caracteres do feedback.
    const mensagem = document.getElementById('mensagem');
    const contador = document.getElementById('contador');

    if (mensagem && contador) {
        const atualizarContador = () => {
            contador.textContent = String(mensagem.value.length);
        };

        mensagem.addEventListener('input', atualizarContador);
        atualizarContador();
    }

    // 3. Prévia da foto escolhida e aviso de tamanho excedido.
    const foto = document.getElementById('foto');
    const previa = document.getElementById('previa-foto');

    if (foto && previa) {
        foto.addEventListener('change', () => {
            if (previa.src.startsWith('blob:')) {
                URL.revokeObjectURL(previa.src);
            }

            const arquivo = foto.files && foto.files[0];

            if (!arquivo) {
                foto.setCustomValidity('');
                previa.hidden = true;
                return;
            }

            if (arquivo.size > TAMANHO_MAXIMO_FOTO) {
                foto.setCustomValidity('A imagem deve ter no máximo 2 MB.');
                foto.reportValidity();
                previa.hidden = true;
                return;
            }

            foto.setCustomValidity('');
            previa.src = URL.createObjectURL(arquivo);
            previa.hidden = false;
        });
    }
});