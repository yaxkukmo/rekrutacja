Plan dzialania:


1. Refactoring - zamiana na port/adapter 
- utworzenie katalogow: Application (serwisy), Domain (model domenowy, interfejsy), Infrastructure (implementacje interfejsow, modele ORM, mapery orm <-> model domenowy)
- przeniesienie istniejacych plikow
- utworzenie brakujacych plikow (interfejsow, klas modeli)
- przeniesienie kodu z god class do utworzonych/przeniesionych plikow
- uzyc DI
- naprawic controllery (maja uzywac serwisow)
- usunac niepotrzebne pliki
2. Zadanie: Import zdjec do SymfonyApp z PhoenixApi
- dodanie pola z tokenem do bazy domyslnie NULL
- aktualizacja modeli
- dodanie zapisu tokenu w repozytorium
- import zdjec z API
- zapisanie kolekcji zdjec dla currentUser
- formularz
3. Zadanie: Filtrowanie zdjec
- utworzyc formularz
- w kontrolerze odczytac parametry ustawione w formularzu
- w serwisie zaimplementowac wyszukiwanie po filtrze w zaleznosci od istnienia pol filtra
- stworzyc obiekt domenowy ktory bedzie filtrem
- w porcie dodac metode wyszukiwania po filtrze a w repo ja zaimplementowac
4. Zadanie: modyfikacja API 100% AI

 Plan implementacji rate-limitingu przez OTP                                                                                                                                                         
                                                                                                                                                                                                      
  Dwa limity do zaimplementowania:                                                                                                                                                                    
                                                                                                                                                                                                      
  - Per-user: 5 importów / 10 minut                                                                                                                                                                   
  - Globalny: 1000 importów / godzinę
                                                                                                                                                                                                      
  ---                                                                                                                                                                                                 
  1. RateLimiter — GenServer (OTP)

  Jeden proces trzymający stan w pamięci jako mapę:
  %{
    {:user, user_id} => [{timestamp}, ...],  # per-user okno
    :global => [{timestamp}, ...}             # globalny licznik
  }

  Algorytm sliding window — przy każdym żądaniu usuwasz timestampy starsze niż okno (10 min / 1h) i sprawdzasz czy nie przekroczono limitu.

  2. Supervision tree

  Dodajesz RateLimiter jako dziecko w Application.ex — process żyje przez cały czas życia aplikacji, restartuje się automatycznie przy crashu.

  3. Plug RateLimit

  Nowy plug wywoływany po Authenticate (masz już current_user w conn.assigns). Plug pyta RateLimiter czy request jest dozwolony — jeśli nie, zwraca 429 Too Many Requests.

  4. Router

  Dodajesz RateLimit do pipeline lub bezpośrednio w PhotoController.



Uwagi

- wykonano explain na wiekszosci zapytan i dodano index dla photo na pola userId imageId - przy malej ilosci zdjec nie jest uzywany ale bedzie gdsy przybedzie zdjec.
- potencjalnie mozna zostawic symfonyApp jako API a dodac nuxt + vue jako SPA

- Zadania wykonano przy pomocy claude code. 
Szybko zostalem pozbawiony zludzen, ze DDD (probuje tego a aplikacjach aby sie nauczyc) to dobry pomysl na ta aplikacje. Propozycja heksagonalnel architektury byla nowoscia dla mnie wiec przystalem na propozycje aby tego uzyc. Dodatkowo zaproponowalem jednak aby dodac czyste modele domenowe. Moim zdaniem warstwy kontroller i domena sa proste i kazdy sie w tym polapie.
W pozostalych zadanich z symfonyApp korzystalem juz mniej z AI skoro wszystko stalo sie proste i zrozumiale po zastosowaniu portow i adapterow.
 Ostatnie zadanie to 100% AI - nie znam elixira, ale pol roku temu pracowalem nad importerem danych w eliksirze (tez za pomoca clauda).
Niestety juz przy pierwszym prompcie (dokonaj analizy tej aplikacji) dostalem info o znalezionych bledach. Co prawda i tak bym trafil na sql" . $costam ."sql ale juz bylo za pozno. Brakujacy indeks tez bym znalazl przez expleina (tez uzywam go przez AI). Tak czy siak nie ma sensu oszukiwac, znalezienie bledow to zasluga AI (patrz: CLAUDE.md) 
Dodatkowym bledem? ktory znalazlem byl zwracany status informujacy czy uzytkownik istnieje (lepiej 401)
Brak TDD w opisie byla wyrazna sugestia aby pisac najpierw testy ale nie robilem tak do tej pory ani nie bralem udzialu w projekcie gdzie ktos by uzywal TDD (byly proby ale szybko porzucone) wiec tez nie chcialem sprawiac wrazenia, ze "tak, uzywam, wiem, znam".



