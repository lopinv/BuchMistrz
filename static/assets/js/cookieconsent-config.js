document.addEventListener('DOMContentLoaded', function () {
    // Sprawdza, czy biblioteka CookieConsent jest dostępna.
    if (typeof CookieConsent !== 'undefined') {
        CookieConsent.run({
            current_lang: 'pl',
            autoclear_cookies: true,
            page_scripts: true,
            mode: 'opt-in',

            languages: {
                pl: {
                    consent_modal: {
                        title: 'Używamy plików cookie',
                        description: 'Nasza strona używa plików cookie, aby zapewnić Ci jak najlepsze doświadczenia. Kontynuując, wyrażasz zgodę na ich użycie.',
                        primary_btn: {
                            text: 'Akceptuj wszystkie',
                            role: 'accept_all'
                        },
                        secondary_btn: {
                            text: 'Dostosuj',
                            role: 'settings'
                        }
                    },
                    settings_modal: {
                        title: 'Ustawienia prywatności',
                        save_settings_btn: 'Zapisz ustawienia',
                        accept_all_btn: 'Akceptuj wszystkie',
                        reject_all_btn: 'Odrzuć wszystkie',
                        cookie_table_headers: [
                            { col1: 'Nazwa' },
                            { col2: 'Dostawca' },
                            { col3: 'Wygasa' },
                            { col4: 'Typ' }
                        ],
                        blocks: [
                            {
                                title: 'Niezbędne pliki cookie',
                                description: 'Te pliki cookie są niezbędne do prawidłowego działania strony, dlatego nie można ich wyłączyć.',
                                toggle: {
                                    value: 'necessary',
                                    enabled: true,
                                    readonly: true
                                }
                            },
                            {
                                title: 'Analityka i statystyki',
                                description: 'Te pliki cookie pomagają nam zrozumieć, jak użytkownicy korzystają z naszej strony, co pozwala nam ją ulepszać.',
                                toggle: {
                                    value: 'analytics',
                                    enabled: false,
                                    readonly: false
                                }
                            }
                        ]
                    }
                }
            }
        });
    }
});
