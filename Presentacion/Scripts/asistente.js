document.addEventListener('DOMContentLoaded', () => {
    const modalRecinto = document.getElementById('modal-recinto');
    const tituloModal = document.getElementById('recinto-modal-title');
    const colocadosContainer = document.getElementById('recinto-colocados');
    const inputRecintoId = document.getElementById('input-recinto-id');
    const inputEspecie = document.getElementById('input-especie');
    const inputCorriente = document.getElementById('input-corriente');
    const formAgregarAdepto = document.getElementById('form-agregar-adepto');
    const bonusRow = document.getElementById('bonus-row');
    const bonusCheck = document.getElementById('bonus-rey');
    const csrfTokenVal = document.querySelector('input[name="csrf_token"]')?.value || '';
    let hotspotActual = null;

    // Utiliza las traducciones inyectadas desde PHP o valores por defecto
    const nombresRecintos = window.recintosNombres || {
        '1': 'El Congreso',
        '2': 'Profesorado',
        '3': 'La Pradera del Amor',
        '4': 'Rey Filósofo',
        '5': 'El Ágora',
        '6': 'Director',
        '7': 'El Pasillo'
    };

    const imagenesAdeptos = {
        'cinica': '../../Assets/DiogenesPerro.png',
        'socratica': '../../Assets/SocratesHyrax.png',
        'neoplatonica': '../../Assets/HypatiaAbeja.png',
        'peripatetica': '../../Assets/AristotelesTortuga.png',
        'platonica': '../../Assets/PlatonPato.png',
        'epicurea': '../../Assets/EpicuroKoala.png'
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

    function renderColocados(idRecinto, esRey) {
        if (!colocadosContainer) return;

        colocadosContainer.innerHTML = '';
        const key = (idRecinto === '7') ? 'pasillo' : parseInt(idRecinto);
        const adeptosEnRecinto = window.escuela[key] || [];

        // Gestionar visibilidad del checkbox del Rey Filósofo:
        // Solo se muestra si es el recinto del rey Y ya tiene al menos un adepto colocado.
        if (bonusRow) {
            if (esRey && adeptosEnRecinto.length > 0) {
                bonusRow.style.display = 'flex';
                if (bonusCheck) {
                    bonusCheck.checked = !!adeptosEnRecinto[0].bonusRey;
                }
            } else {
                bonusRow.style.display = 'none';
                if (bonusCheck) bonusCheck.checked = false;
            }
        }

        adeptosEnRecinto.forEach((adepto, index) => {
            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';
            wrapper.style.cursor = 'pointer';

            const btnItem = document.createElement('button');
            btnItem.type = 'button';
            btnItem.classList.add('adepto-select-btn');

            const img = document.createElement('img');
            img.src = imagenesAdeptos[adepto.corriente] || '../../Assets/Socrates.png';
            img.alt = adepto.corriente || 'Adepto colocado';
            img.classList.add('adepto-icon');

            btnItem.appendChild(img);

            // Cruz de borrado individual en la esquina superior derecha
            const deleteBadge = document.createElement('span');
            deleteBadge.innerHTML = '&times;';
            deleteBadge.style.position = 'absolute';
            deleteBadge.style.top = '-0.3rem';
            deleteBadge.style.right = '-0.3rem';
            deleteBadge.style.background = 'rgba(197, 63, 84, 0.9)';
            deleteBadge.style.color = '#fff';
            deleteBadge.style.borderRadius = '50%';
            deleteBadge.style.width = '1.3rem';
            deleteBadge.style.height = '1.3rem';
            deleteBadge.style.display = 'flex';
            deleteBadge.style.alignItems = 'center';
            deleteBadge.style.justifyContent = 'center';
            deleteBadge.style.fontSize = '0.95rem';
            deleteBadge.style.fontWeight = 'bold';
            deleteBadge.style.zIndex = '10';

            const eliminarAdeptoHandler = (e) => {
                e.stopPropagation();
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'asistente.php';

                const inputs = [
                    { name: 'accion', value: 'eliminar_adepto' },
                    { name: 'recinto', value: idRecinto },
                    { name: 'indice', value: index },
                    { name: 'csrf_token', value: csrfTokenVal }
                ];

                inputs.forEach(data => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = data.name;
                    inp.value = data.value;
                    form.appendChild(inp);
                });

                document.body.appendChild(form);
                form.submit();
            };

            wrapper.appendChild(btnItem);
            wrapper.appendChild(deleteBadge);

            wrapper.addEventListener('click', eliminarAdeptoHandler);
            colocadosContainer.appendChild(wrapper);
        });
    }

    document.querySelectorAll('.recinto-hotspot').forEach(hotspot => {
        hotspot.addEventListener('click', () => {
            const idRecinto = hotspot.getAttribute('data-recinto');
            const esRey = hotspot.hasAttribute('data-rey');

            hotspotActual = hotspot;

            if (tituloModal) {
                tituloModal.textContent = nombresRecintos[idRecinto] || ('Recinto ' + idRecinto);
            }

            if (inputRecintoId) {
                inputRecintoId.value = idRecinto;
            }

            renderColocados(idRecinto, esRey);
            openModal(modalRecinto);
        });
    });

    // Captura correcta de los botones de selección para evitar el envío erróneo por defecto
    document.querySelectorAll('.adepto-select-btn[data-especie]').forEach(btn => {
        btn.addEventListener('click', () => {
            const especie = btn.getAttribute('data-especie');
            const corriente = btn.getAttribute('data-corriente');

            if (inputEspecie) inputEspecie.value = especie;
            if (inputCorriente) inputCorriente.value = corriente;

            if (formAgregarAdepto) {
                formAgregarAdepto.submit();
            }
        });
    });

    // Actualizar el bonus del Rey Filósofo de manera dinámica al cambiar el checkbox
    if (bonusCheck) {
        bonusCheck.addEventListener('change', () => {
            if (inputRecintoId && inputRecintoId.value === '4') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'asistente.php';

                const inputs = [
                    { name: 'accion', value: 'actualizar_bonus_rey' },
                    { name: 'csrf_token', value: csrfTokenVal }
                ];

                if (bonusCheck.checked) {
                    inputs.push({ name: 'bonus_rey', value: '1' });
                }

                inputs.forEach(data => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = data.name;
                    inp.value = data.value;
                    form.appendChild(inp);
                });

                document.body.appendChild(form);
                form.submit();
            } else if (hotspotActual && hotspotActual.hasAttribute('data-rey')) {
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