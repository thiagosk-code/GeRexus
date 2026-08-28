document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('lang')) {
        const urlLang = urlParams.get('lang') === 'en' ? 'en' : 'es';
        
        localStorage.setItem('lang', urlLang);
        document.cookie = `lang=${urlLang}; path=/; max-age=31536000; SameSite=Lax`;

        urlParams.delete('lang');
        const nuevaUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', nuevaUrl);
    }

    const btnsLang = document.querySelectorAll('.leng-switcher a');

    btnsLang.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            
            const hrefVal = btn.getAttribute('href');
            const codLang = hrefVal.includes('lang=en') ? 'en' : 'es';

            localStorage.setItem('lang', codLang);
            document.cookie = `lang=${codLang}; path=/; max-age=31536000; SameSite=Lax`;

            window.location.reload();
        });
    });
});