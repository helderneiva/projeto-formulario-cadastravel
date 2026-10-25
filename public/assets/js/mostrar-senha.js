'use strict';

/**
 * "Mostrar senha": a caixa de seleção alterna todos os campos de senha
 * marcados com data-campo-senha dentro do mesmo formulário.
 *
 * A opção vem escondida no HTML (atributo hidden) e só aparece aqui,
 * para não mostrar um controle sem função quando o JavaScript está
 * desligado. Os formulários continuam funcionando sem ele.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-mostrar-senha]').forEach((caixa) => {
        const formulario = caixa.closest('form');

        if (!formulario) {
            return;
        }

        const campos = formulario.querySelectorAll('[data-campo-senha]');

        if (campos.length === 0) {
            return;
        }

        const opcao = caixa.closest('[data-mostrar-senha-opcao]');

        if (opcao) {
            opcao.hidden = false;
        }

        const aplicar = () => {
            const tipo = caixa.checked ? 'text' : 'password';

            campos.forEach((campo) => {
                campo.type = tipo;
            });
        };

        caixa.addEventListener('change', aplicar);

        // Sincroniza o estado (o navegador pode restaurar a caixa marcada
        // ao voltar para a página).
        aplicar();
    });
});