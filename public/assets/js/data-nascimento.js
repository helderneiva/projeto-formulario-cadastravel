'use strict';

/**
 * Data de nascimento: o campo de texto aceita digitação (dd/mm/aaaa, com
 * máscara automática) e o botão ao lado abre o calendário do navegador.
 * Os dois ficam sincronizados.
 *
 * Sem JavaScript, o campo de texto continua funcionando: basta digitar
 * dd/mm/aaaa. O servidor sempre valida a data novamente.
 */
document.addEventListener('DOMContentLoaded', () => {
    const texto = document.getElementById('data_nascimento');
    const calendario = document.getElementById('calendario-nascimento');
    const botao = document.getElementById('abrir-calendario');

    if (!texto) {
        return;
    }

    // "20052000" -> "20/05/2000" (aceita digitação parcial).
    const aplicarMascara = (valor) => {
        const digitos = valor.replace(/\D/g, '').slice(0, 8);

        if (digitos.length > 4) {
            return digitos.slice(0, 2) + '/' + digitos.slice(2, 4) + '/' + digitos.slice(4);
        }

        if (digitos.length > 2) {
            return digitos.slice(0, 2) + '/' + digitos.slice(2);
        }

        return digitos;
    };

    // "20/05/2000" -> "2000-05-20" (formato do input type="date").
    const paraIso = (valor) => {
        const partes = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(valor);

        return partes ? partes[3] + '-' + partes[2] + '-' + partes[1] : '';
    };

    // "2000-05-20" -> "20/05/2000".
    const paraBr = (valor) => {
        const partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor);

        return partes ? partes[3] + '/' + partes[2] + '/' + partes[1] : '';
    };

    const sincronizarCalendario = () => {
        if (calendario) {
            calendario.value = paraIso(texto.value);
        }
    };

    texto.addEventListener('input', () => {
        const mascarado = aplicarMascara(texto.value);

        if (mascarado !== texto.value) {
            texto.value = mascarado;
        }

        sincronizarCalendario();
    });

    if (!calendario || !botao) {
        return;
    }

    // Escolheu no calendário: preenche o campo de texto.
    calendario.addEventListener('change', () => {
        texto.value = paraBr(calendario.value);
    });

    botao.addEventListener('click', () => {
        sincronizarCalendario();

        try {
            if (typeof calendario.showPicker === 'function') {
                calendario.showPicker();
            } else {
                calendario.click();
            }
        } catch (erro) {
            calendario.focus();
        }
    });

    // O botão só aparece quando o JavaScript está ativo.
    botao.hidden = false;
    sincronizarCalendario();
});