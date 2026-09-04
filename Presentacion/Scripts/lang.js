document.addEventListener('DOMContentLoaded', () => {
    const btnsLang = document.querySelectorAll('.leng-switcher a');

    btnsLang.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            
            const hrefVal = btn.getAttribute('href');
            const codLang = hrefVal.includes('lang=en') ? 'en' : 'es';

            localStorage.setItem('lang', codLang);

            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('lang', codLang);

            window.location.search = urlParams.toString();
        });
    });
});