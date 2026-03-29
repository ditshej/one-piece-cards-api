## 1. Package installieren & konfigurieren

- [ ] 1.1 `composer require dedoc/scramble` installieren
- [ ] 1.2 Scramble-Config publishen: `php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider"`
- [ ] 1.3 `config/scramble.php` anpassen: Titel ("One Piece Cards API"), Version, API-Pfad (`api/v1`)

## 2. Verifikation

- [ ] 2.1 `GET /docs/api.json` liefert gültiges OpenAPI 3.1 JSON mit allen 4 Endpoints
- [ ] 2.2 `GET /docs/api` liefert Stoplight Elements UI
- [ ] 2.3 Cards-Endpoint enthält Query-Parameter (color, category, cost, pack, search) in der Spec
- [ ] 2.4 Response-Schemas für Pack und Card sind korrekt (alle Felder aus den Eloquent Resources)

## 3. Tests

- [ ] 3.1 Feature-Test: `GET /docs/api.json` gibt 200 zurück
- [ ] 3.2 Feature-Test: `GET /docs/api` gibt 200 zurück
