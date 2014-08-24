Lambda-Framework
================

Open source simple PHP framework
Currently readme file is available in polish only. If you don't speak polish please use google translate.

## Główny kontroler frameworka

Zadaniem kontrolera wLWAF, mówiąc najogólniej, jest zarządzanie wywoływaniem modułów. Najpierw sprawdza on dostępne moduły anastępnie sprawdza który znich jest kontrolerem aplikacji. Jeżeli istnieje więcej niż jeden kontroler aplikacji ‑ sprawdza czy użytkownik wywołał konkretną aplikację – ajeśli nie, korzysta zdomyślnego kontrolera.

Spełnia on również rolę „wstrzykiwania zależności". Rozwiązuje on rekursywnie zależności między modułami i– jeśli stosownie zadeklarowano – wstrzykuje zależne obiekty do obiektów nadrzędnych.

Zależności, czyli moduły od których zależny jest dany moduł są rozwiązywane przez kontroler rekursywnie.

Domyślny (i obecnie jedyny) główny kontroler nazywa się LMBDV1. Jego nazwa jest skrótem od Lambda Version One.

Opis metod kontrolera, szczegóły ich działania orazlisting kodu znajdują się wpierwszych trzech dodatkach do pracy.

1. 
  1. 
    1. 1.1.Plik index

Plik index.php jest tzw.„plikiem wejścia", czyli plikiem wywoływanym przez użytkownika. Jego jedynym zadaniem jest wywoływanie głównego kontrolera, przekazując do jego konstruktora informację ofolderze modułów. Ztego powodu jako jedyny plik LWAF znajduje się ponad folderem modułów.

Ścieżkę do folderu modułów ustawia się wewnątrz pliku. Domyślnie jest to folder modules.

1. 
  1. 
    1. 1.2.Plik .htaccess

Plik .htaccess Lambda Framework wykorzystuje przy tworzeniu „przyjaznych adresów" URL. Jest on stosowany tylko przy serwerach które obsługują mod\_rewrite. Zawiera dyrektywy konfiguracyjne, zamieniające przyjazne ludziom adresy na adresy zrozumiałe dla komputera. Domyślne dyrektywy można dowolnie modyfikować według własnych potrzeb, można je również dodać do pliku httpd.conf, jeśli mamy do niego dostęp. Zawartość domyślnego pliku jest następująca:



RewriteEngine on

RewriteBase /lambda

RewriteRule ^mod‑(.\*)‑(.\*)‑id(.\*).html$ index.php?m=$1&p=$2&id=$3 [L]

RewriteRule ^mod‑(.\*)‑id(.\*).html$ index.php?m=$1&&id=$2 [L]

RewriteRule ^(.\*)‑id(.\*).html$ index.php?p=$1&id=$2 [L]

RewriteRule ^mod‑(.\*)‑(.\*).html$ index.php?m=$1&p=$2 [L]

RewriteRule ^mod‑(.\*).html$ index.php?m=$1 [L]

RewriteRule ^(.\*).html$ index.php?p=$1 [L]

W pierwszym wierszu sprawdzane jest, czy mod\_rewrite jest dostępny. Wdrugim wierszu zostaje on włączony. Najważniejszy zpunktu widzenia programisty jest trzeci wiersz, gdzie podajemy ścieżkę pod którą zainstalowany jest LWAF (w powyższym przykładzie jest to folder „lambda").

Kolejne wiersze to reguły, według których będzie następowało przepisywanie adresów. Poniżej opis parametrów URL wykorzystywanych przez różne elementy frameworka:

| Parametr | Wykorzystywany przez | Opis |
| --- | --- | --- |
| m | Kontroler LMBDV1 | Nazwa modułu wywoływanego przez użytkownika |
| p | Aplikację (poprzez Router) | Nazwa strony wywoływanej przez użytkownika |
| id | Aplikację (poprzez Router) | Dodatkowe parametry do strony wywoływane przez użytkownika |



1. 
  1. 
    1. 1.3.Tryby pracy kontrolera

Kontroler LMBDV1 posiada trzy tryby pracy:

1. 
Discovery (wykrywanie) – Kontroler za każdym razem będzie wykrywał moduły wkatalogu modułów. Wtym trybie wszystkie foldery wewnątrz folderu modułów są przeszukiwane pod kątem istnienia plików module.ini. Wartość domyślna, zalecana wśrodowisku deweloperskim;   


2. 
Performance (wydajność) – Kontroler używa statycznych definicji modułów, zapisanych wpliku modules\_defined.php. Wartość zalecana wśrodowisku produkcyjnym;  


3. Automatic (automatyczne) – Kontroler aktualizuje plik modules\_defined.php cyklicznie, przez większość czasu pozostając wtrybie performance.

1. 
  1. 
    1. 1.4.Pliki konfiguracyjne

W LWAF występują trzy główne typy plików konfiguracyjnych:

1. Pliki module.ini
2. Pliki config.php
3. Pliki install.ini

Z plików **module.ini** korzysta główny kontroler frameworka przy wyszukiwaniu modułów wtrybie „discovery". Poniżej dostępne ustawienia:

| Nazwa | Opis |
| --- | --- |
| version | Wersja modułu (cyfry ikropka/kropki) |
| enabled | Czy moduł jest włączony (1 – włączony, 0 – wyłączony) |
| autoload | Czy obiekt modułu ma być automatycznie tworzony przez główny kontroler iwłączany do jego puli obiektów (1 – tak, 0 – nie) |
| appcontroller | Czy moduł jest kontrolerem aplikacji (1 – tak, 0 – nie). Obiekt kontrolera aplikacji zawsze jest automatycznie tworzony przez główny kontroler, bez względu na wartość autoload. |
| classname | Nazwa klasy modułu. Plik ją zawierający musi mieć identyczną nazwę. |
| inject | Czy obiekt modułu ma być wstrzykiwany do modułów od niego zależnych (1 – tak, 0 – nie) |
| config | Nazwa pliku konfiguracyjnego, który powinien zostać wczytany przed wywołaniem modułu (bez rozszerzenia .php) |
| dependency [nazwa\_modułu] | Tablica zależności od modułu. Jako klucz podajemy nazwę modułu, ajako wartość wymaganą wersję. |
| lmbd\_compatible | Wymagana przez moduł wersja Lambda Framework |

Z plików **install.ini** korzysta aplikacja zarządzająca frameworkiem Appverse. Poniżej dostępne ustawienia:

| Nazwa | Opis |
| --- | --- |
| name | Nazwa modułu |
| type | Kategoria modułu. Standardowe kategorie: Application (Lambda MVC), Application (custom), DB drivers, Front‑end, Icon packs, Themes, Utilities |
| description | Opis modułu |
| icon | Ścieżka do ikony modułu |
| author | Autor modułu |
| installer | Ścieżka do instalatora modułu |
| package [] | Jeżeli moduł jest kontrolerem aplikacji izawiera kilka modułów, wskazanie ich jako package [] = nazwa\_modułu |

Standardowo pliki **config.php** są rozpoznawane przez framework jako pliki przechowujące wewnętrzne ustawienia konfiguracyjne. Ustawienia są przechowywane wformie wywołań metody Config::write(). Informacje wnich przekazywane są widoczne wyłącznie wewnątrz frameworka. Aplikacja zarządzająca frameworkiem przy usuwaniu/aktualizacji modułów domyślnie pozostawia je bez zmian.

Po pobraniu przez moduł pliku config.php, zapisane wnim wartości konfiguracyjne stają się widoczne globalnie dla wszystkich modułów.

W przypadku informacji które nie powinny być widoczne dla wszystkich modułów stosuje się lokalne pliki konfiguracji. Przykładem takiego pliku jest plik drivers\_enabled.php, wktórym przechowywane są informacje na temat sterowników orazparametrów połączeń do baz danych. Dostęp do tego pliku ma tylko klasa Model, która zarządza połączeniami zbazą danych.

W tabeli poniżej opis przedstawiłem wartości konfiguracyjnych dla głównego kontrolera:

| Nazwa | Opis | Domyślnie |
| --- | --- | --- |
| lmbd.default.module | Domyślny moduł („none" wprzypadku braku) | none |
| lmbd.default.module.force | Wymuszanie wywołania domyślnego modułu (1 – tak albo 0 –nie) | 0 |
| lmbd.controller.workmode | Tryb pracy kontrolera (szczegóły wpodpunkcie otrybach pracy kontrolera) | discovery |
| lmbd.controller.scan.interval | Czas cyklu aktualizacji pliku modules\_defined wtrybie automatycznym wyrażony wsekundach (szczegóły wpodpunkcie otrybach pracy kontrolera) | 3600 |

1. 
  1. 
    1. 1.5.Pseudokonstruktor init

Pseudokontruktor init() różni się tym od domyślnego konstruktora \_\_construct tym, że jest wywoływany przez kontroler frameworka dopiero po rozwiązaniu zależności modułu. Nie przyjmuje on żadnych argumentów.

Aby moduł mógł zniego korzystać, jego klasa musi zaimplementować interfejs iConsturct orazposiadać metodę init().

1. 
  1. 2.Aspekty implementacji składowych widoku frameworka

Widok wLambda Framework składa się zmodułu Template iView orazopcjonalnie Frontend ‑ jeśli programista zdecyduje się na używanie frameworka front‑end.

1. 
  1. 
    1. 2.1.Moduł View

Moduł View jest główną składową widoku frameworka. Zapisuje on wszystkie wartości przekazane poprzez metodę display() wtablicy. Następnie, poprzez destruktor lub manualne wywołanie metody startView() wczytuje motyw (plik html, tzw.„layout") ipo dodaniu do niego zawartości tablicy na podstawie stref wyświetla go.

Strefy są tym, co wyróżnia moduł view isprawia że wLWAF nie używa się domyślnej funkcji „echo" lub „print". Strefa jest obszarem motywu, wktórym ma być wyświetlana dana treść.

Standardowo LWAF posiada 8 stref:
