document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-adepto');
    if (!modal) return;

    const titulo = document.getElementById('modal-adepto-titulo');
    const imgActual = document.getElementById('modal-adepto-img');
    const slots = Array.from(document.querySelectorAll('.adepto-slot'));
    const inputs = Array.from(document.querySelectorAll('.adepto-slot-wrap input[name="adeptos[]"]'));
    const opciones = Array.from(modal.querySelectorAll('.adepto-select-btn[data-id]'));
    const etiqueta = modal.getAttribute('data-etiqueta');
    let slotActual = 0;

    function datosAdepto(id) {
        const opcion = opciones.find(o => o.getAttribute('data-id') === String(id));
        if (!opcion) return null;
        return {
            id: opcion.getAttribute('data-id'),
            src: opcion.getAttribute('data-src'),
            nombre: opcion.getAttribute('data-nombre')
        };
    }

    function pintarSlot(posicion, id) {
        const datos = datosAdepto(id);
        if (!datos) return;

        inputs[posicion].value = datos.id;
        const img = slots[posicion].querySelector('img');
        img.src = datos.src;
        img.alt = datos.nombre;
    }

    function refrescarModal() {
        titulo.textContent = etiqueta + ' ' + (slotActual + 1);

        const actual = datosAdepto(inputs[slotActual].value);
        if (actual) {
            imgActual.src = actual.src;
            imgActual.alt = actual.nombre;
        }

        const usados = inputs.map(input => input.value);
        opciones.forEach(opcion => {
            const id = opcion.getAttribute('data-id');
            opcion.classList.toggle('en-uso', usados.includes(id) && inputs[slotActual].value !== id);
        });
    }

    function cerrar() {
        modal.classList.remove('active');
    }

    slots.forEach((slot, posicion) => {
        slot.addEventListener('click', () => {
            slotActual = posicion;
            refrescarModal();
            modal.classList.add('active');
        });
    });

    opciones.forEach(opcion => {
        opcion.addEventListener('click', () => {
            const elegido = opcion.getAttribute('data-id');
            const previo = inputs[slotActual].value;
            const otroSlot = inputs.findIndex((input, posicion) => posicion !== slotActual && input.value === elegido);

            if (otroSlot !== -1) {
                pintarSlot(otroSlot, previo);
            }

            pintarSlot(slotActual, elegido);
            refrescarModal();
        });
    });

    document.querySelectorAll('[data-close="modal-adepto"]').forEach(btn => btn.addEventListener('click', cerrar));

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            cerrar();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrar();
        }
    });
});
