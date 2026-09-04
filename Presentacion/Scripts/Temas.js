document.addEventListener('DOMContentLoaded', () => {
    const temaToggleBtn = document.querySelector('.tema-toggle');
    const icon = temaToggleBtn ? temaToggleBtn.querySelector('.material-symbols-outlined') : null;

    const temaGuardado = localStorage.getItem('tema');

    if (temaGuardado === 'light') {
        document.body.classList.add('light-mode');
        if (icon) icon.textContent = 'light_mode';
    } else if (temaGuardado === 'dark') {
        document.body.classList.remove('light-mode');
        if (icon) icon.textContent = 'dark_mode';
    }

    if (!temaToggleBtn) return;

    temaToggleBtn.addEventListener('click', () => {
        temaToggleBtn.classList.add('pop-effect');
        const esClaro = document.body.classList.toggle('light-mode');

        if (esClaro) {
            localStorage.setItem('tema', 'light');
            if (icon) icon.textContent = 'light_mode';
        } else {
            localStorage.setItem('tema', 'dark');
            if (icon) icon.textContent = 'dark_mode';
        }

        setTimeout(() => {
            temaToggleBtn.classList.remove('pop-effect');
        }, 300);
    });
});