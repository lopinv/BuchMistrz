# BuchMistrz - Konfiguracja WooCommerce API

## Instrukcja konfiguracji API

### 1. Generowanie kluczy API w WooCommerce

1. Zaloguj się do panelu administratora WordPress
2. Przejdź do **WooCommerce > Ustawienia > Zaawansowane > REST API**
3. Kliknij **Dodaj klucz**
4. Uzupełnij formularz:
   - **Opis**: np. "BuchMistrz Sklep"
   - **Użytkownik**: Wybierz użytkownika z uprawnieniami administratora
   - **Uprawnienia**: Wybierz "Odczyt"
5. Kliknij **Generuj klucz API**
6. Skopiuj **Consumer Key** i **Consumer Secret**

### 2. Konfiguracja w pliku script.js

Otwórz plik `script.js` i znajdź sekcję `API_CONFIG` na początku pliku:

```javascript
const API_CONFIG = {
    baseUrl: 'https://twoja-domena.com/wp-json/wc/v3', // Zastąp swoją domeną
    consumerKey: 'ck_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', // Wklej Consumer Key
    consumerSecret: 'cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' // Wklej Consumer Secret
};
```

Zastąp:
- `twoja-domena.com` - swoją domeną sklepu
- `ck_XXXXXXX...` - Consumer Key z WooCommerce
- `cs_XXXXXXX...` - Consumer Secret z WooCommerce

### 3. Wymagania CORS (Cross-Origin Resource Sharing)

Jeśli strona będzie hostowana na innej domenie niż sklep WooCommerce, może być potrzebne skonfigurowanie CORS.

Dodaj do pliku `.htaccess` w katalogu głównym WordPress:

```apache
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS, DELETE, PUT"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
```

Lub dodaj do pliku `functions.php` aktywnego motywu:

```php
function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
}
add_action('init','add_cors_http_header');
```

### 4. Tryb demonstracyjny

Jeśli nie skonfigurujesz API (pozostawisz domyślne wartości), strona będzie działać w trybie demonstracyjnym z przykładowymi produktami.

### 5. Funkcje strony

#### Zaimplementowane funkcje:
- ✅ Wyświetlanie produktów z WooCommerce API
- ✅ Filtrowanie po kategoriach
- ✅ Sortowanie (nazwa, cena, data)
- ✅ Wyszukiwanie produktów
- ✅ Paginacja
- ✅ Responsive design
- ✅ Przeceny i promocje
- ✅ Status magazynowy
- ✅ Lista ulubionych (localStorage)
- ✅ Przekierowanie do oryginalnej strony produktu
- ✅ Powiadomienia o akcjach użytkownika

#### Język i lokalizacja:
- 🇵🇱 Pełne tłumaczenie na język polski
- 🎨 Kolory nawiązujące do flagi Polski (czerwony-biały)
- 💰 Formatowanie cen w złotówkach (zł)
- 📱 Responsywny design dostosowany do polskich standardów

### 6. Dostosowanie wyglądu

Wszystkie style CSS znajdują się w pliku `index.html` w sekcji `<style>`. Możesz dostosować:

- Kolory (zmienne CSS w `:root`)
- Czcionki
- Rozmiary i odstępy
- Animacje

### 7. Rozwiązywanie problemów

#### Problem: Produkty się nie ładują
- Sprawdź czy klucze API są poprawne
- Sprawdź czy domena jest prawidłowa
- Sprawdź konsolę przeglądarki (F12) w poszukiwaniu błędów

#### Problem: CORS Error
- Skonfiguruj CORS zgodnie z punktem 3
- Sprawdź czy sklep WooCommerce ma włączone REST API

#### Problem: Nieprawidłowe wyświetlanie
- Sprawdź czy wszystkie pliki CSS i JS są prawidłowo załadowane
- Sprawdź czy CDN dla Font Awesome i Google Fonts są dostępne

### 8. Dodatkowe możliwości

Strona jest przygotowana na rozszerzenia:
- Koszyk zakupowy
- System logowania
- Porównywanie produktów
- Recenzje i oceny
- Powiadomienia push

---

## Wsparcie techniczne

W przypadku problemów z konfiguracją, skontaktuj się z administratorem systemu lub deweloperem.

Strona została stworzona z myślą o polskim rynku i standardach e-commerce.