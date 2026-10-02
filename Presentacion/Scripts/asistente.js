document.addEventListener('DOMContentLoaded', () => {
    const modalRecinto = document.getElementById('modal-recinto');
    const tituloModal = document.getElementById('recinto-modal-title');
    const colocadosContainer = document.getElementById('recinto-colocados');

    const colocadosPorRecinto = {
        '1': ['../../Assets/PlatonPato.png', '../../Assets/DiogenesPerro.png'],
        '2': [],
        '3': ['../../Assets/EpicuroKoala.png'],
        '4': ['../../Assets/SocratesHyrax.png', '../../Assets/HypatiaAbeja.png', '../../Assets/AristotelesTortuga.png'],
        '5': [],
        '6': []
    };

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

    function renderColocados(idRecinto) {
        if (!colocadosContainer) return;

        colocadosContainer.innerHTML = '';
        const rutas = colocadosPorRecinto[idRecinto] || [];

        rutas.forEach(ruta => {
            const wrapper = document.createElement('span');
            wrapper.classList.add('adepto-select-btn');

            const img = document.createElement('img');
            img.src = ruta;
            img.alt = 'Adepto colocado';
            img.classList.add('adepto-icon');

            wrapper.appendChild(img);
            colocadosContainer.appendChild(wrapper);
        });
    }

    document.querySelectorAll('.recinto-box').forEach(box => {
        box.addEventListener('click', () => {
            const idRecinto = box.getAttribute('data-recinto');

            if (tituloModal) {
                tituloModal.textContent = 'Recinto ' + idRecinto;
            }

            renderColocados(idRecinto);
            openModal(modalRecinto);
        });
    });

    document.querySelectorAll('[data-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-close');
            closeModal(document.getElementById(targetId));
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
    });
});
