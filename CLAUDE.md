# CLAUDE.md

## Opis projektu

Platforma do przeglądania i polubień zdjęć (mini-Instagram) — zadanie rekrutacyjne.
Dwie niezależne aplikacje z osobnymi bazami danych, orkiestrowane przez Docker Compose.

## Architektura

- **Symfony App** (PHP 8.1+, Symfony 6.4) — port 8000: frontend webowy + logika biznesowa (użytkownicy, sesje, polubienia)
- **Phoenix API** (Elixir 1.15+, Phoenix 1.7) — port 4000: REST API serwujące dane o zdjęciach (metadane aparatu, URL-e)
- **2x PostgreSQL 15** — osobne bazy: `symfony_app` (port 5432), `phoenix_api` (port 5433)

Komunikacja: Symfony → Phoenix API przez HTTP (`access-token` header). Token przechowywany w obu bazach.

### Architektura Symfony — Hexagonal (Ports & Adapters)

```
Controller → Application/Service → Domain/Model (logika biznesowa)
                                        ↓
                                  Domain/Port (interfejs)
                                        ↓
                            Infrastructure/Repository (adapter)
                                        ↓
                            Infrastructure/Mapper ↔ Entity ORM
                                        ↓
                                    PostgreSQL
```

- **Domain/Model** — czyste modele domenowe (`final class`, bez ORM, z metodami biznesowymi)
- **Domain/Port** — interfejsy repozytoriów i klientów zewnętrznych
- **Application** — serwisy orkiestrujące (`AuthService`, `LikeService`, `PhotoService`, `UserService`, `ImportPhotoService`)
- **Infrastructure/Doctrine/Entity** — encje ORM Doctrine (atrybuty `#[ORM\*]`)
- **Infrastructure/Doctrine/Repository** — implementacje portów (prepared statements, query builder)
- **Infrastructure/Doctrine/Mapper** — konwersja ORM ↔ Domain (`toDomain()`, `toEntity()`)
- **Infrastructure/Http** — `PhoenixClient` (wywołania HTTP do Phoenix API)
- **Controller** — cienkie kontrolery, delegują do serwisów

## Komendy

### Uruchomienie
```bash
docker-compose up -d
docker-compose exec symfony composer dump-autoload
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec symfony php bin/console app:seed
docker-compose exec phoenix mix ecto.migrate
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

### Testy
```bash
# PHP (PHPUnit 10.5)
docker-compose exec symfony php vendor/bin/phpunit

# Elixir (ExUnit)
docker-compose exec -e MIX_ENV=test -e DB_HOST=phoenix-db phoenix mix test
```

### Reset bazy danych
```bash
# Symfony
docker-compose exec symfony php bin/console doctrine:schema:drop --force --full-database
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec symfony php bin/console app:seed

# Phoenix
docker-compose exec phoenix mix ecto.reset
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

### Logowanie (tokeny generowane losowo przy seedzie)
```bash
# Sprawdź tokeny w bazie:
docker-compose exec symfony-db psql -U postgres -d symfony_app -c "SELECT u.username, at.token FROM auth_tokens at JOIN users u ON at.user_id = u.id;"
# Zaloguj się: http://localhost:8000/auth/{username}/{token}
```

### Test API ręcznie
```bash
curl -H "access-token: {token}" http://localhost:4000/api/photos
```

## Struktura katalogów

```
phoenix-api/                    # Elixir/Phoenix — REST API zdjęć
  lib/phoenix_api/              # Schematy Ecto: User (accounts), Photo (media)
    rate_limiter.ex             # GenServer (OTP) — sliding window rate limiting
  lib/phoenix_api_web/          # Router, kontrolery, plugi
    plugs/authenticate.ex       # Weryfikacja access-token
    plugs/rate_limit.ex         # Egzekwowanie limitów (429)
  priv/repo/                    # Migracje + seeds
  test/
    phoenix_api/
      rate_limiter_test.exs     # Testy jednostkowe GenServera (5 testów)
    phoenix_api_web/
      plugs/rate_limit_test.exs # Testy integracyjne rate limitingu (3 testy)
      controllers/
        photo_controller_test.exs  # Testy kontrolera (6 testów)

symfony-app/                    # PHP/Symfony — aplikacja webowa
  src/
    Domain/
      Model/                    # Czyste modele: User, Photo, Like, AuthToken, PhotoFilter
      Port/                     # Interfejsy: *RepositoryInterface, PhoenixClientInterface
    Application/                # Serwisy: Auth, Like, Photo, User, ImportPhoto
    Infrastructure/
      Doctrine/
        Entity/                 # Encje ORM: User, Photo, Like, AuthToken
        Repository/             # Implementacje portów (Doctrine query builder)
        Mapper/                 # User/Photo/Like/AuthTokenMapper (ORM ↔ Domain)
      Http/
        PhoenixClient.php       # Klient HTTP do Phoenix API
    Controller/                 # Auth, Home, Photo, Profile
    Command/                    # SeedDatabaseCommand (app:seed)
  templates/                    # Twig (base, home/index, profile/index)
  migrations/                   # Doctrine migrations
  config/                       # services.yaml, doctrine.yaml, routes.yaml
  tests/
    Application/
      ImportPhotoServiceTest.php  # Testy jednostkowe ImportPhotoService (4 testy)
```

## Konwencje kodu

### PHP / Symfony
- `declare(strict_types=1)` w każdym pliku
- PSR-12: 4 spacje, klamry w nowej linii
- PSR-4 autoloading: namespace `App\` → `src/`
- Atrybuty PHP 8.1+: `#[Route]`, `#[ORM\Entity]`, `#[ORM\Column]`
- Modele domenowe: `final class`, immutable (brak setterów), `\DateTimeImmutable`
- Encje ORM: fluent settery (zwracają `self`), atrybuty Doctrine
- Kontrolery: dziedziczą `AbstractController`, wstrzykują serwisy przez konstruktor
- Repozytoria: `ServiceEntityRepository`, implementują porty z `Domain/Port`
- Mappery: statyczne metody `toDomain()` / `toEntity()`
- DI: interfejsy bindowane do implementacji w `services.yaml`
- Sesje PHP do autentykacji (bez Symfony Security firewall)
- CSS inline w szablonach Twig (brak asset pipeline)

### Elixir / Phoenix
- Standardowa struktura Phoenix 1.7 JSON API
- Custom Plug do autentykacji (`Plugs.Authenticate`) — lookup tokenu w DB
- Schematy Ecto z funkcjami `changeset/2`
- ExUnit z SQL Sandbox (async-safe)
- Testy uruchamiane z: `MIX_ENV=test DB_HOST=phoenix-db mix test`

## Funkcjonalność importu zdjęć z Phoenix API

Import zdjęć z Phoenix API do Symfony (główne zadanie rekrutacyjne):

1. Użytkownik zapisuje Phoenix API token na stronie profilu (`POST /profile/token`)
2. Użytkownik klika "Import photos" (`POST /profile/import`)
3. `ImportPhotoService` pobiera zdjęcia z Phoenix API, filtruje duplikaty i zapisuje nowe
4. Migracja `Version20260218140000` dodaje kolumnę `phoenix_api_token` do tabeli `users`

### Przepływ danych
```
ProfileController → ImportPhotoService → PhoenixClient (HTTP GET /api/photos)
                                       → PhotoRepository.existsByImageUrl() (filtr duplikatów)
                                       → PhotoRepository.saveAll() (zapis nowych)
```

### Testy jednostkowe (PHPUnit)
```
tests/Application/ImportPhotoServiceTest.php:
- testItThrowsExceptionWhenUserHasNoToken
- testItCallsPhoenixApiAndSavePhotos
- testItOmitsDuplicates
- testItThrowsExceptionWhenPhoenixApiReturnsError
```

## Filtrowanie zdjęć

Strona główna (`GET /`) obsługuje filtrowanie zdjęć po query params:
- `location`, `camera`, `description`, `username` — częściowe dopasowanie (LIKE `%...%`)
- `takenFrom`, `takenTo` — zakres dat (`>=`, `<=`)

Przepływ: formularz GET → `HomeController` → `PhotoFilter` (value object) → `PhotoService` → `PhotoRepository.findByFilter()`

Filtry działają jako AND — wszystkie aktywne kryteria muszą być spełnione jednocześnie.

## Rate limiting w Phoenix API (OTP)

Endpoint `GET /api/photos` podlega dwóm limitom:
- **Per-user**: 5 requestów / 10 minut
- **Globalny**: 1000 requestów / godzinę

### Implementacja
- `RateLimiter` — GenServer trzymający stan (sliding window) w pamięci jako listy timestampów
- Zarejestrowany w supervision tree (`Application.ex`) — restartuje się automatycznie
- `Plugs.RateLimit` — wywoływany po `Authenticate`, zwraca `429 Too Many Requests` przy przekroczeniu
- Kolejność plugów w `PhotoController`: `Authenticate` → `RateLimit` → akcja

### Odpowiedzi przy przekroczeniu limitu
```json
{"error": "Rate limit exceeded: maximum 5 imports per 10 minutes per user"}
{"error": "Rate limit exceeded: maximum 1000 imports per hour"}
```

## Naprawione problemy (z oryginalnego zadania)

1. ~~**SQL Injection**~~ — `AuthController` deleguje do `AuthService` → `AuthTokenRepository` z prepared statements
2. ~~**Brak UNIQUE INDEX**~~ — migracja `Version20260218120000` dodaje unique index `(user_id, photo_id)` na `likes`
3. ~~**Stub klienta Phoenix**~~ — zaimplementowany `PhoenixClient` w `Infrastructure/Http/`

## Zmienne środowiskowe

| Zmienna | Opis | Domyślna |
|---------|------|----------|
| `DATABASE_URL` (Symfony) | Connection string do PostgreSQL Symfony | `postgres://postgres:postgres@symfony-db:5432/symfony_app` |
| `DATABASE_URL` (Phoenix) | Connection string do PostgreSQL Phoenix | `ecto://postgres:postgres@phoenix-db/phoenix_api` |
| `PHOENIX_BASE_URL` | URL Phoenix API widziany z kontenera Symfony | `http://phoenix:4000` |
| `APP_ENV` | Środowisko Symfony | `dev` |
| `APP_SECRET` | Symfony secret | `thisisasecret222` |
| `SECRET_KEY_BASE` | Phoenix secret | `thisisasecret111` |
