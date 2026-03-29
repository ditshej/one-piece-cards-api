## 1. Package installieren & konfigurieren

- [x] 1.1 `composer require dedoc/scramble` installieren
- [x] 1.2 Scramble-Config publishen: `php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider"`
- [x] 1.3 `config/scramble.php` anpassen: Titel ("One Piece Cards API"), Version, API-Pfad (`api/v1`)

## 2. Verifikation

- [x] 2.1 `GET /docs/api.json` liefert gültiges OpenAPI 3.1 JSON mit allen 4 Endpoints
- [x] 2.2 `GET /docs/api` liefert Stoplight Elements UI
- [x] 2.3 Cards-Endpoint enthält Query-Parameter (color, category, cost, pack, search) in der Spec
- [x] 2.4 Response-Schemas für Pack und Card sind korrekt (alle Felder aus den Eloquent Resources)

## 3. Tests

- [x] 3.1 Feature-Test: `GET /docs/api.json` gibt 200 zurück
- [x] 3.2 Feature-Test: `GET /docs/api` gibt 200 zurück
