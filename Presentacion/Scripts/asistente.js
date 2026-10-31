document.addEventListener('DOMContentLoaded', () => {
    const data = window.asistenteData || {};
    const escuela = data.escuela || {};
    const nombresRecintos = data.nombresRecintos || {};
    const csrfToken = data.csrfToken || '';
    const modalRecinto = document.getElementById('modal-recinto');
    const tituloModal = document.getElementById('recinto-modal-title');
    const colocadosContainer = document.getElementById('recinto-colocados');
    const inputRecintoId = document.getElementById('input-recinto-id');
    const inputAdepto = document.getElementById('input-adepto');
    const formAgregarAdepto = document.getElementById('form-agregar-adepto');
    const bonusRow = document.getElementById('bonus-row');
    const bonusCheck = document.getElementById('bonus-rey');

    const imagenesAdeptos = {
        cinica: '../../Assets/DiogenesPerro.png',
        socratica: '../../Assets/SocratesHyrax.png',
        neoplatonica: '../../Assets/HypatiaAbeja.png',
        peripatetica: '../../Assets/AristotelesTortuga.png',
        platonica: '../../Assets/PlatonPato.png',
        epicurea: '../../Assets/EpicuroKoala.png'
    };

    function openModal(modal) {
        if (modal) modal.classList.add('active');
    }

    function closeModal(modal) {
        if (modal) modal.classList.remove('active');
    }

    function submitAction(fields) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'asistente.php';

        Object.entries({ ...fields, csrf_token: csrfToken }).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = String(value);
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function rutaAdepto(adepto) {
        const archivo = typeof adepto.archivo === 'string' ? adepto.archivo : '';
        return archivo !== '' ? `../../Assets/${archivo}` : (imagenesAdeptos[adepto.corriente] || '../../Assets/Socrates.png');
    }

    function renderTablero() {
        document.querySelectorAll('.recinto-hotspot').forEach((hotspot) => {
            const idRecinto = hotspot.dataset.recinto || '';
            const key = idRecinto === '7' ? 'pasillo' : idRecinto;
            const adeptos = Array.isArray(escuela[key]) ? escuela[key] : [];
            const contenedor = hotspot.querySelector('.recinto-fichas');
            if (!contenedor) return;

            contenedor.replaceChildren();

            adeptos.forEach((adepto) => {
                const img = document.createElement('img');
                img.src = rutaAdepto(adepto);
                img.alt = '';
                img.loading = 'lazy';
                contenedor.appendChild(img);
            });
        });
    }

    function renderColocados(idRecinto, esRey) {
        if (!colocadosContainer) return;

        colocadosContainer.replaceChildren();
        const key = idRecinto === '7' ? 'pasillo' : idRecinto;
        const adeptos = Array.isArray(escuela[key]) ? escuela[key] : [];

        if (bonusRow && bonusCheck) {
            const mostrarBonus = esRey && adeptos.length > 0;
            bonusRow.classList.toggle('visible', mostrarBonus);
            bonusCheck.checked = mostrarBonus && Boolean(adeptos[0].bonusRey);
        }

        adeptos.forEach((adepto, indice) => {
            const wrapper = document.createElement('span');
            wrapper.className = 'adepto-select-btn';
            wrapper.style.position = 'relative';

            const img = document.createElement('img');
            img.src = rutaAdepto(adepto);
            img.alt = adepto.corriente || 'Adepto colocado';
            img.className = 'adepto-icon';

            const eliminar = document.createElement('button');
            eliminar.type = 'button';
            eliminar.textContent = '×';
            eliminar.setAttribute('aria-label', data.eliminarLabel || 'Eliminar adepto');
            eliminar.style.position = 'absolute';
            eliminar.style.top = '-0.45rem';
            eliminar.style.right = '-0.45rem';
            eliminar.style.width = '1.35rem';
            eliminar.style.height = '1.35rem';
            eliminar.style.padding = '0';
            eliminar.style.border = '0';
            eliminar.style.borderRadius = '50%';
            eliminar.style.background = 'rgba(197, 63, 84, 0.95)';
            eliminar.style.color = '#fff';
            eliminar.style.cursor = 'pointer';
            eliminar.addEventListener('click', () => {
                submitAction({ accion: 'eliminar_adepto', recinto: idRecinto, indice });
            });

            wrapper.append(img, eliminar);
            colocadosContainer.appendChild(wrapper);
        });
    }

    renderTablero();

    document.querySelectorAll('.recinto-hotspot').forEach((hotspot) => {
        if (hotspot.dataset.recinto === '4' && Array.isArray(escuela['4']) && escuela['4'][0]?.bonusRey) {
            hotspot.classList.add('bonus-activo');
        }

        hotspot.addEventListener('click', () => {
            const idRecinto = hotspot.dataset.recinto || '';
            const esRey = hotspot.hasAttribute('data-rey');

            if (tituloModal) tituloModal.textContent = nombresRecintos[idRecinto] || `Recinto ${idRecinto}`;
            if (inputRecintoId) inputRecintoId.value = idRecinto;

            renderColocados(idRecinto, esRey);
            openModal(modalRecinto);
        });
    });

    document.querySelectorAll('.adepto-select-btn[data-adepto]').forEach((boton) => {
        boton.addEventListener('click', () => {
            if (!inputAdepto || !formAgregarAdepto) return;
            inputAdepto.value = boton.dataset.adepto || '';
            formAgregarAdepto.submit();
        });
    });

    if (bonusCheck) {
        bonusCheck.addEventListener('change', () => {
            const fields = { accion: 'actualizar_bonus_rey' };
            if (bonusCheck.checked) fields.bonus_rey = '1';
            submitAction(fields);
        });
    }

    document.querySelectorAll('[data-open]').forEach((boton) => {
        boton.addEventListener('click', () => openModal(document.getElementById(boton.dataset.open)));
    });

    document.querySelectorAll('[data-close]').forEach((boton) => {
        boton.addEventListener('click', () => closeModal(document.getElementById(boton.dataset.close)));
    });

    document.querySelectorAll('.modal-overlay').forEach((overlay) => {
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) closeModal(overlay);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(closeModal);
        }
    });
});
