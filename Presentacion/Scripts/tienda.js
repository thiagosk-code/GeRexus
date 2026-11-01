(() => {
    const planes = document.querySelectorAll('.plan-card');
    const feedback = document.getElementById('tienda-feedback');

    if (!feedback) return;

    planes.forEach((plan) => {
        plan.addEventListener('click', () => {
            feedback.hidden = false;
            feedback.focus({ preventScroll: true });
        });
    });
})();
