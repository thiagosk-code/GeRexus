document.addEventListener('DOMContentLoaded', () => {
    const modalRecinto = document.getElementById('modal-recinto');
    const tituloModal = document.getElementById('recinto-modal-title');
    const colocadosContainer = document.getElementById('recinto-colocados');
    const bonusRow = document.getElementById('bonus-row');
    const bonusCheck = document.getElementById('bonus-rey');
    const etiquetaRecinto = modalRecinto ? modalRecinto.getAttribute('data-recinto-label') : 'Recinto';
    let hotspotActual = null;

    const colocadosPorRecinto = {
        '1': ['../../Assets/PlatonPato.png', '../../Assets/DiogenesPerro.png'],
        '2': [],
        '3': ['../../Assets/EpicuroKoala.png'],
        '4': ['../../Assets/SocratesHyrax.png', '../../Assets/HypatiaAbeja.png', '../../Assets/AristotelesTortuga.png'],
        '5': [],
        '6': [],
        '7': []
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

    document.querySelectorAll('.recinto-hotspot').forEach(hotspot => {
        hotspot.addEventListener('click', () => {
            const idRecinto = hotspot.getAttribute('data-recinto');
            const esRey = hotspot.hasAttribute('data-rey');

            hotspotActual = hotspot;

            if (tituloModal) {
                tituloModal.textContent = etiquetaRecinto + ' ' + idRecinto;
            }

            if (bonusRow && bonusCheck) {
                bonusRow.classList.toggle('visible', esRey);
                bonusCheck.checked = esRey && hotspot.classList.contains('bonus-activo');
            }

            renderColocados(idRecinto);
            openModal(modalRecinto);
        });
    });

    if (bonusCheck) {
        bonusCheck.addEventListener('change', () => {
            if (hotspotActual && hotspotActual.hasAttribute('data-rey')) {
                hotspotActual.classList.toggle('bonus-activo', bonusCheck.checked);
            }
        });
    }

    document.querySelectorAll('[data-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal(document.getElementById(btn.getAttribute('data-open')));
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

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(closeModal);
        }
    });
});
