document.addEventListener('DOMContentLoaded', () => {
    const inputIdJugador = document.getElementById('echar-id-jugador');

    function openModal(modal) {
        if (modal) {
            modal.classList.add('active');
        }
    }

    function closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
        }
    }

    document.addEventListener('click', (e) => {
        const btnAbrir = e.target.closest('[data-open]');
        if (btnAbrir) {
            if (inputIdJugador && btnAbrir.hasAttribute('data-jugador')) {
                inputIdJugador.value = btnAbrir.getAttribute('data-jugador');
            }
            openModal(document.getElementById(btnAbrir.getAttribute('data-open')));
            return;
        }

        const btnCerrar = e.target.closest('[data-close]');
        if (btnCerrar) {
            closeModal(document.getElementById(btnCerrar.getAttribute('data-close')));
            return;
        }

        if (e.target.classList.contains('modal-overlay')) {
            closeModal(e.target);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(closeModal);
        }
    });

    const sala = document.getElementById('sala-main');
    const lista = document.getElementById('jugadores-lista');
    const contador = document.getElementById('sala-contador');

    if (!sala || !lista) return;

    const codigo = sala.getAttribute('data-codigo');
    const urlEstado = sala.getAttribute('data-estado-url');
    const destinos = {
        salio: 'digital.php',
        cancelada: 'digital.php?aviso=cancelada',
        expulsado: 'digital.php?aviso=expulsado'
    };
    const INTERVALO = 2000;

    let version = parseInt(sala.getAttribute('data-version'), 10) || 0;
    let temporizador = null;
    let consultando = false;
    let fallos = 0;

    async function consultar() {
        if (consultando) return;
        consultando = true;
        clearTimeout(temporizador);

        let espera = INTERVALO;

        try {
            const url = urlEstado + '?codigo=' + encodeURIComponent(codigo) + '&version=' + version;
            const res = await fetch(url, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                throw new Error('http');
            }

            const data = await res.json();
            fallos = 0;

            if (data.accion === 'iniciada') {
                window.location.href = 'partida.php?id=' + encodeURIComponent(data.idPartida);
                return;
            }

            if (destinos[data.accion]) {
                window.location.href = destinos[data.accion];
                return;
            }

            if (data.cambio) {
                lista.innerHTML = data.html;
                version = data.version;
                if (contador) {
                    contador.textContent = data.cantidad + '/' + data.max;
                }
            }
        } catch (err) {
            fallos++;
            espera = Math.min(15000, INTERVALO * (fallos + 1));
        }

        consultando = false;
        temporizador = setTimeout(consultar, espera);
    }

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            consultar();
        }
    });

    temporizador = setTimeout(consultar, INTERVALO);
});
