=== WysylajTaniej.pl — przesyłki kurierskie ===
Contributors: wysylajtaniej
Tags: wysylajtaniej, wysyłajtaniej, kurier, paczki, kurier
Requires at least: 3.0.1
Stable tag: 1.3.2
Tested up to: 6.4.4
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Opis

Wtyczka "Wysyłaj Taniej" umożliwia integrację Twojego sklepu opartego na WordPress oraz WooCommerce z platformą usług kurierskich <a href="https://www.wysylajtaniej.pl/register/">wysylajtaniej.pl</a>. Dzięki tej wtyczce możesz łatwo i wygodnie generować przesyłki kurierskie bezpośrednio z panelu WooCommerce.

Funkcje wtyczki:

<ul>
<li>Nowość. Masowe wysyłanie przesyłek bezpośrednio ze swojej listy zamówień w WooCommerce jednym kliknięciem.</li>
<li>Wybór spośród różnych firm kurierskich, takich jak DPD, Inpost, Paczkomat, DPD Pickup, Orlen Paczka.</li>
<li>Automatyczne wybieranie wagi i rozmiarów przesyłki z produktów WooCommerce lub dostosowanych ustawień.</li>
<li>Deklarowanie zawartości paczki oraz dodawanie dodatkowych usług, takich jak pobranie lub ubezpieczenie.</li>
<li>Planowanie daty i godziny dostarczenia.</li>
<li>Wybór punktu doręczenia za pomocą mapy lub listy.</li>
<li>Możliwość wysyłania zleceń do koszyka na <a href="https://www.wysylajtaniej.pl/register/">wysylajtaniej.pl</a>.</li>
<li>Ustawienia wartości domyślnych.</li>
<li>Wycena przesyłki poprzez jedno kliknięcie.</li>
<li>Dostęp do wsparcia technicznego w razie problemów z integracją.</li>
</ul>

Pamiętaj, że do korzystania z tej wtyczki konieczne jest posiadanie konta na <a href="https://www.wysylajtaniej.pl/register/">wysylajtaniej.pl</a>.

Więcej informacji oraz instrukcja konfiguracji dostępne są na <a href="https://www.wysylajtaniej.pl/narzedzia/wtyczka-do-integracji-z-firma-kurierska-woocommerce">stronie dokumentacji</a>.

## Instrukcja

Aby korzystać z wtyczki, należy posiadać aktywne konto na <a href="https://www.wysylajtaniej.pl/register/">wysylajtaniej.pl</a>. W przypadku pytań dotyczących wtyczki lub tworzenia konta, można skontaktować się pod adresem: <a href="mailto:woo@wysylajtaniej.pl">woo@wysylajtaniej.pl</a>.

## FAQ
W nowej wersji dodane zostało masowe wysyłanie z listy przesyłek oraz nastąpiła zmiana sposobu wyliczania domyślnej wagi i wymiarów. 
Od teraz uzględniane są w wadze i wymiarach wagi, wymiary produktów  oraz domyślne ustawienia wtyczki. 

<strong>Czy potrzebuję konta na <a href="https://www.wysylajtaniej.pl/register/">wysylajtaniej.pl</a>?</strong>
Tak, do korzystania z wtyczki niezbędne jest posiadanie konta na <a href="https://www.wysylajtaniej.pl/register/">wysylajtaniej.pl</a>.

<strong>Gdzie znajdę klucz API?</strong>
Klucz API można wygenerować po zalogowaniu się na <a href="https://www.wysylajtaniej.pl/">wysylajtaniej.pl</a> w zakładce "Moje dane".

<strong>Jak mogę przetestować wtyczkę bez generowania paczek?</strong>
Wtyczka umożliwia dodawanie przesyłek do koszyka na <a href="https://www.wysylajtaniej.pl/">wysylajtaniej.pl</a>, ale nie generuje ich od razu. W celu przetestowania, można dodać przesyłki do koszyka bez ich natychmiastowej generacji.

<strong>Nie wyświetlają mi się firmy kurierskie w koszyku.</strong>
Aby wyświetlić firmy kurierskie w koszyku, należy dodać odpowiednie metody wysyłki w ustawieniach WooCommerce. Następnie, w panelu Wysyłaj Taniej, należy przypisać firmę kurierską do każdej metody wysyłki.

## Zrzuty ekranu

1. Wprowadzanie klucza API.
2. Domyślne dane nadawcy.
3. Wysyłanie paczki.
4. Wybór punktu doręczenia z mapy.

## Historia zmian

= 1.0.0 =
* Pierwsza wersja wtyczki dla InPost Kurier.

= 1.0.1 =
* Dodano obsługę DPD Pickup, Paczkomatów oraz Paczki w Ruchu.

= 1.0.2 =
* Testy kompatybilności z WooCommerce 4.6.1 oraz drobne poprawki.

= 1.0.2 =
* Testy kompatybilności z WooCommerce 4.6.1 oraz drobne poprawki.

= 1.0.3 =
* Dodano poprawki, w tym automatyczne uzupełnianie danych z ustawień wtyczki.

= 1.0.4 =
* Sprawdzono kompatybilność z nowymi wersjami WordPress i WooCommerce.

= 1.0.5 =
* Dodano możliwość wyboru kilku metod wysyłki do jednej firmy kurierskiej oraz inne usprawnienia.

= 1.0.6 =
* Rozbudowano obsługę stref wysyłki oraz dostosowano do WooCommerce 4.8.0 i WordPress 5.6.

= 1.0.7 =
* Poprawki dla PHP 8.0 oraz kompatybilność z WooCommerce 5.2.2 i WordPress 5.7.1.

= 1.0.8 =
* Domyślne zaznaczanie pobrania i ubezpieczenia oraz inne zmiany.

= 1.0.9 =
* Aktualizacja dla WooCommerce 6 i WordPress 5.8.2.

= 1.1.0 =
* Aktualizacja dla PHP 8.0 i WordPress 5.9.

= 1.1.1 =
* Poprawki kompatybilności z WordPress 5.9.1.

= 1.1.2 =
* Poprawki dla WordPress 6 oraz obsługa Paczki w Ruchu.

= 1.1.3 =
* Dodano zmienną do opisu paczki.

= 1.1.4 =
* Testy kompatybilności z nowymi wersjami PHP, WooCommerce i WordPress.

= 1.1.5 =
* Dodano możliwość masowego wysyłania przesyłek z listy zamówień.

= 1.1.6 =
* Testy kompatybilności z nowymi wersjami PHP, WooCommerce i WordPress oraz inne poprawki.

= 1.1.7 =
* Testy kompatybilności i drobne poprawki.

= 1.1.8 =
* Poprawki i testy z WooCommerce i WordPress.

= 1.1.9 =
* Aktualizacja testów kompatybilności z WooCommerce i WordPress oraz dodano link do pomocy technicznej.

= 1.2.0 =
* Aktualizacja testów kompatybilności i poprawki.

= 1.2.1 =
* Testy kompatybilności i poprawki.

= 1.2.2 =
* Poprawka usuwająca błąd wyświetlania w panelu administratora.

= 1.2.3 =
* Testy kompatybilności z WordPress 6.0.1.

= 1.2.4 =
* Testy kompatybilności z WordPress 6.3 i WooCommerce 8.0.0 oraz poprawki.

= 1.2.5 =
* Testy kompatybilności z WordPress 6.3.1 i WooCommerce 8.0.1, dodanie funkcji  statusus wysyłki.

= 1.2.6 =
* Dodanie funkcji masowego wysyłania z listy zamówień.

= 1.2.7 =
* Poprawki wysyłania masowego. Zmiana sposobu wyliczania domyślnej wagi i wymiarów. 
* Uwględnianie w wadze i wymiarów zamówienia oraz domyślnych ustawień.

= 1.2.8 =
* Poprawki wysyłania masowego w przypadku punktów odbioru. 

= 1.2.9 =
* Poprawki drobne. Aktualizacja do WP 6.3.2

= 1.3.0 =
* Usunięcie błądu Brak wartości DESCRIPTION

= 1.3.1 =
* Dostosowanie do nowych wersji php oraz WP i WC

= 1.3.2 =
* Poprawka w wysyłce seryjnej. Ubezpieczenie przesyłki pojawi się, jeżeli jest pobranie lub podaliśmy wartość w ustawieniach wtyczki.
