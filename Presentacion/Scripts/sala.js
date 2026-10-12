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

    document.querySelectorAll('[data-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (inputIdJugador && btn.hasAttribute('data-jugador')) {
                inputIdJugador.value = btn.getAttribute('data-jugador');
            }
            openModal(document.getElementById(btn.getAttribute('data-open')));
        });
    });

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(document.getElementById(btn.getAttribute('data-close')));
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(closeModal);
        }
    });
});
