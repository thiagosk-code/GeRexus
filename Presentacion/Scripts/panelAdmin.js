document.addEventListener('DOMContentLoaded', function() {
    const rowsPerPage = 5;
    let currentPage = 1;
    const tableBody = document.getElementById('tabla-body');

    if (tableBody) {
        const allRows = Array.from(tableBody.querySelectorAll('tr'));
        let visibleRows = [...allRows];

        const buscarInput = document.getElementById('buscar-input');
        const pagNumbers = document.getElementById('pag-numbers');
        const btnPrev = document.getElementById('pag-prev');
        const btnNext = document.getElementById('pag-next');

        function renderTable() {
            const totalPages = Math.ceil(visibleRows.length / rowsPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            allRows.forEach(row => row.style.display = 'none');

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            visibleRows.slice(start, end).forEach(row => row.style.display = '');

            if (pagNumbers) {
                pagNumbers.innerHTML = '';
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.classList.add('pag-num');
                    if (i === currentPage) btn.classList.add('active');
                    btn.textContent = i;
                    btn.addEventListener('click', () => {
                        currentPage = i;
                        renderTable();
                    });
                    pagNumbers.appendChild(btn);
                }
            }
        }

        if (buscarInput) {
            buscarInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                visibleRows = allRows.filter(row => {
                    const id = row.getAttribute('data-id') || '';
                    const nom = row.getAttribute('data-nombre') || '';
                    const email = row.getAttribute('data-email') || '';
                    return id.includes(query) || nom.includes(query) || email.includes(query);
                });
                currentPage = 1;
                renderTable();
            });
        }

        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });
        }

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                const totalPages = Math.ceil(visibleRows.length / rowsPerPage) || 1;
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });
        }

        renderTable();

        const modIdInput = document.getElementById('mod-id');
        if (modIdInput) {
            modIdInput.addEventListener('input', function() {
                const valId = this.value.trim();
                if (valId === '') {
                    document.getElementById('mod-nombre').value = '';
                    document.getElementById('mod-email').value = '';
                    document.getElementById('mod-dracmas').value = '';
                    return;
                }

                const targetRow = allRows.find(row => row.getAttribute('data-id') === valId);
                if (targetRow) {
                    const cells = targetRow.querySelectorAll('td');
                    document.getElementById('mod-nombre').value = cells[1].textContent.trim();
                    document.getElementById('mod-email').value = cells[2].textContent.trim();
                    document.getElementById('mod-dracmas').value = cells[3].textContent.trim();
                }
            });
        }
    }

    document.addEventListener('click', function(e) {
        const btnEdit = e.target.closest('.btn-edit');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id') || '';
            const nombre = btnEdit.getAttribute('data-nombre') || '';
            const email = btnEdit.getAttribute('data-email') || '';
            const dracmas = btnEdit.getAttribute('data-dracmas') || '';

            const inputId = document.getElementById('mod-id');
            const inputNombre = document.getElementById('mod-nombre');
            const inputEmail = document.getElementById('mod-email');
            const inputDracmas = document.getElementById('mod-dracmas');

            if (inputId) inputId.value = id;
            if (inputNombre) inputNombre.value = nombre;
            if (inputEmail) inputEmail.value = email;
            if (inputDracmas) inputDracmas.value = dracmas;

            const cardMod = document.getElementById('card-modificar');
            if (cardMod) {
                cardMod.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });

    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('form-eliminar')) {
            const confirmacion = confirm('¿Seguro que deseas dar de baja a este usuario?');
            if (confirmacion === false) {
                e.preventDefault();
            }
        }
    });

    const tabs = [
        { btn: document.getElementById('btn-tab-gu'), panel: document.getElementById('panel-gu') },
        { btn: document.getElementById('btn-tab-ga'), panel: document.getElementById('panel-ga') },
        { btn: document.getElementById('btn-tab-gc'), panel: document.getElementById('panel-gc') }
    ];

    tabs.forEach(t => {
        if (t.btn && t.panel) {
            t.btn.addEventListener('click', () => {
                tabs.forEach(item => {
                    if (item.btn) item.btn.classList.remove('active');
                    if (item.panel) item.panel.classList.remove('active');
                });
                t.btn.classList.add('active');
                t.panel.classList.add('active');
            });
        }
    });
});
