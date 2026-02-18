Plan dzialania:

Znalezione bledy:
- sql injection w AuthController
- zwracany status informujacy czy uzytkownik istnieje (lepiej 401)
- brak indeksu na likes

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
- 
