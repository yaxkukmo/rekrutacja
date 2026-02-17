Plan dzialania:

1. Refactoring - zamiana na port/adapter 
- utworzenie katalogow: Application (serwisy), Domain (model domenowy, interfejsy), Infrastructure (implementacje interfejsow, modele ORM, mapery orm <-> model domenowy)
- przeniesienie istniejacych plikow
- utworzenie brakujacych plikow (interfejsow, klas modeli)
- przeniesienie kodu z god class do utworzonych/przeniesionych plikow
- uzyc DI
- naprawic controllery (maja uzywac serwisow)
- usunac niepotrzebne pliki
2. Zadania
