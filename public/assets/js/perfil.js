'use strict';

/**
 * Melhorias de usabilidade da página de perfil.
 * O formulário continua funcionando sem JavaScript; o servidor
 * sempre valida tudo novamente.
 */
document.addEventListener('DOMContentLoaded', () => {
    const TAMANHO_MAXIMO_FOTO = 2 * 1024 * 1024; // 2 MB

    // 1. Barra de nota: mostra o valor, uma carinha e a cor
    //    (vermelho perto do 0, verde perto do 10).
    const EMOJIS = ['😭', '😢', '😞', '😔', '😕', '😐', '🙂', '😊', '😄', '😁', '🤩'];
    const nota = document.getElementById('nota');
    const notaValor = document.getElementById('nota-valor');
    const notaEmoji = document.getElementById('nota-emoji');

    if (nota && notaValor) {
        const campoNota = nota.closest('.campo');

        const atualizarNota = (animar) => {
            const valor = Number(nota.value);

            notaValor.textContent = nota.value;

            if (campoNota) {
                // Matiz 0 = vermelho; 130 = verde.
                campoNota.style.setProperty('--nota-cor', 'hsl(' + valor * 13 + ', 85%, 58%)');
                campoNota.style.setProperty('--nota-frac', String(valor / 10));
            }

            if (notaEmoji) {
                notaEmoji.textContent = EMOJIS[valor] || '';

                if (animar) {
                    notaEmoji.classList.remove('pop');
                    void notaEmoji.offsetWidth; // reinicia a animação
                    notaEmoji.classList.add('pop');
                }
            }
        };

        nota.addEventListener('input', () => atualizarNota(true));
        atualizarNota(false);
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