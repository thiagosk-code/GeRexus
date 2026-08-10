document.addEventListener('DOMContentLoaded', () => {
    const temaToggleBtn = document.querySelector('.tema-toggle');
    if (!temaToggleBtn) return;

    temaToggleBtn.addEventListener('click', () => {
        temaToggleBtn.classList.add('pop-effect');
        document.body.classList.toggle('light-mode');

        const icon = temaToggleBtn.querySelector('.material-symbols-outlined');
        if (icon) {
            const isLight = document.body.classList.contains('light-mode');
            icon.textContent = isLight ? 'light_mode' : 'dark_mode';
        }

        setTimeout(() => {
            temaToggleBtn.classList.remove('pop-effect');
        }, 300);
    });
});