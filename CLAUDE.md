# CLAUDE.md

## Opis projektu

Platforma do przeglądania i polubień zdjęć (mini-Instagram) — zadanie rekrutacyjne.
Dwie niezależne aplikacje z osobnymi bazami danych, orkiestrowane przez Docker Compose.

## Architektura

- **Symfony App** (PHP 8.1+, Symfony 6.4) — port 8000: frontend webowy + logika biznesowa (użytkownicy, sesje, polubienia)
- **Phoenix API** (Elixir 1.15+, Phoenix 1.7) — port 4000: REST API serwujące dane o zdjęciach (metadane aparatu, URL-e)
- **2x PostgreSQL 15** — osobne bazy: `symfony_app` (port 5432), `phoenix_api` (port 5433)

Komunikacja: Symfony → Phoenix API przez HTTP (`access-token` header). Token przechowywany w obu bazach.

## Komendy

### Uruchomienie
```bash
docker-compose up -d
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec symfony php bin/console app:seed
docker-compose exec phoenix mix ecto.migrate
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

### Testy
```bash
# PHP (PHPUnit 10.5)
docker-compose exec symfony php bin/phpunit

# Elixir (ExUnit)
docker-compose exec phoenix mix test
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

### Test API ręcznie
```bash
curl -H "access-token: test_token_user1_abc123" http://localhost:4000/api/photos
```

## Struktura katalogów

```
phoenix-api/          # Elixir/Phoenix — REST API zdjęć
  lib/phoenix_api/    # Schematy Ecto: User (accounts), Photo (media)
  lib/phoenix_api_web/  # Router, kontrolery, plugi (Authenticate)
  priv/repo/          # Migracje + seeds
  test/               # ExUnit

symfony-app/          # PHP/Symfony — aplikacja webowa
  src/Controller/     # Auth, Home, Photo, Profile
  src/Entity/         # User, Photo, AuthToken (Doctrine ORM)
  src/Likes/          # Like entity + LikeService + LikeRepository (interfejs)
  src/Command/        # SeedDatabaseCommand (app:seed)
  templates/          # Twig (base, home/index, profile/index)
  migrations/         # Doctrine migrations
  config/             # services.yaml, routes.yaml, packages/
  tests/              # PHPUnit
```

## Konwencje kodu

### PHP / Symfony
- `declare(strict_types=1)` w każdym pliku
- Atrybuty PHP 8.1+: `#[Route]`, `#[ORM\Entity]`, `#[ORM\Column]`
- PSR-4 autoloading: namespace `App\` → `src/`
- Fluent settery (zwracają `self`)
- Kontrolery dziedziczą `AbstractController`
- Repozytoria bazują na `ServiceEntityRepository`
- Warstwa serwisowa z interfejsami (`LikeRepositoryInterface`)
- Sesje PHP do autentykacji (bez Symfony Security firewall)
- CSS inline w szablonach Twig (brak asset pipeline)

### Elixir / Phoenix
- Standardowa struktura Phoenix 1.7 JSON API
- Custom Plug do autentykacji (`Plugs.Authenticate`) — lookup tokenu w DB
- Schematy Ecto z funkcjami `changeset/2`
- ExUnit z SQL Sandbox (async-safe)

## Znane celowe problemy (zadanie rekrutacyjne)

1. **SQL Injection** w `AuthController.php` — surowa interpolacja stringów w zapytaniach SQL (`"SELECT * FROM auth_tokens WHERE token = '$token'"`)
2. **Brak UNIQUE INDEX** na tabeli `likes` — pozwala na duplikaty polubień (komentarz w migracji potwierdza celowość)
3. **Stub klienta Phoenix** — `services_test.yaml` referencuje `App\Infrastructure\Http\PhoenixClient` i `App\Domain\Port\PhoenixClientInterface`, które jeszcze nie istnieją w `src/`

## Zmienne środowiskowe

| Zmienna | Opis | Domyślna |
|---------|------|----------|
| `DATABASE_URL` (Symfony) | Connection string do PostgreSQL Symfony | `postgres://postgres:postgres@symfony-db:5432/symfony_app` |
| `DATABASE_URL` (Phoenix) | Connection string do PostgreSQL Phoenix | `ecto://postgres:postgres@phoenix-db/phoenix_api` |
| `PHOENIX_BASE_URL` | URL Phoenix API widziany z kontenera Symfony | `http://phoenix:4000` |
| `APP_ENV` | Środowisko Symfony | `dev` |
| `APP_SECRET` | Symfony secret | `thisisasecret222` |
| `SECRET_KEY_BASE` | Phoenix secret | `thisisasecret111` |
