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

    document.querySelectorAll('.btn-moves').forEach(btn => {
        btn.addEventListener('click', () => openModal(modalMoves));
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