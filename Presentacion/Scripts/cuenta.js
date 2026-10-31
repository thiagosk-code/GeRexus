document.addEventListener('DOMContentLoaded', () => {
    const headerAvatarImg = document.getElementById('header-avatar-img');
    const savedAvatar = localStorage.getItem('userAvatar');

    if (savedAvatar) {
        if (headerAvatarImg) {
            headerAvatarImg.style.backgroundImage = `url('${savedAvatar}')`;
        }
        updateActiveAvatarButtons(savedAvatar);
    } else if (headerAvatarImg) {
        headerAvatarImg.style.backgroundImage = "url('../../Assets/DiogenesPerro.png')";
    }

    const btnOpenAvatars = document.getElementById('btn-open-avatars');
    const modalAvatars = document.getElementById('modal-avatars');
    const btnOpenLogout = document.getElementById('btn-open-logout');
    const modalLogout = document.getElementById('modal-logout');
    const btnOpenDelete = document.getElementById('btn-open-delete');
    const modalDelete = document.getElementById('modal-delete');
    const modalMoves = document.getElementById('modal-moves');

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

    if (btnOpenAvatars) {
        btnOpenAvatars.addEventListener('click', () => openModal(modalAvatars));
    }

    if (btnOpenLogout) {
        btnOpenLogout.addEventListener('click', () => openModal(modalLogout));
    }

    if (btnOpenDelete) {
        btnOpenDelete.addEventListener('click', () => openModal(modalDelete));
    }

    const movesContent = document.getElementById('moves-content');
    const gamesList = document.getElementById('games-list');
    const btnMore = document.getElementById('btn-more-games');
    let solicitudJugadas = 0;

    async function pedirJson(url) {
        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
            throw new Error('http');
        }
        return res.json();
    }

    function mostrarMensaje(contenedor, texto) {
        contenedor.innerHTML = '';
        const p = document.createElement('p');
        p.textContent = texto;
        contenedor.appendChild(p);
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-moves');
        if (!btn || !movesContent) return;

        const solicitud = ++solicitudJugadas;
        mostrarMensaje(movesContent, movesContent.dataset.cargando);
        openModal(modalMoves);

        try {
            const data = await pedirJson('jugadas_partida.php?id=' + encodeURIComponent(btn.dataset.partida));
            if (solicitud !== solicitudJugadas) return;

            if (data.vacio) {
                mostrarMensaje(movesContent, movesContent.dataset.vacio);
            } else {
                movesContent.innerHTML = data.html;
            }
        } catch (err) {
            if (solicitud !== solicitudJugadas) return;
            mostrarMensaje(movesContent, movesContent.dataset.error);
        }
    });

    if (btnMore && gamesList) {
        btnMore.addEventListener('click', async () => {
            btnMore.disabled = true;

            try {
                const data = await pedirJson('historial_partidas.php?offset=' + encodeURIComponent(btnMore.dataset.offset));
                gamesList.insertAdjacentHTML('beforeend', data.html);
                btnMore.dataset.offset = data.siguiente;

                if (data.hayMas) {
                    btnMore.disabled = false;
                } else {
                    btnMore.remove();
                }
            } catch (err) {
                btnMore.textContent = btnMore.dataset.error;
                setTimeout(() => {
                    btnMore.textContent = btnMore.dataset.label;
                    btnMore.disabled = false;
                }, 2500);
            }
        });
    }

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

    document.querySelectorAll('.avatar-opt:not(.btn-add-avatar)').forEach(btn => {
        btn.addEventListener('click', () => {
            const newSrc = btn.getAttribute('data-src');
            if (!newSrc) return;

            localStorage.setItem('userAvatar', newSrc);
            if (headerAvatarImg) {
                headerAvatarImg.style.backgroundImage = `url('${newSrc}')`;
            }

            updateActiveAvatarButtons(newSrc);

            if (modalAvatars && modalAvatars.classList.contains('active')) {
                closeModal(modalAvatars);
            }
        });
    });

    function updateActiveAvatarButtons(selectedSrc) {
        document.querySelectorAll('.avatar-opt:not(.btn-add-avatar)').forEach(b => {
            if (b.getAttribute('data-src') === selectedSrc) {
                b.classList.add('active');
            } else {
                b.classList.remove('active');
            }
        });
    }
});