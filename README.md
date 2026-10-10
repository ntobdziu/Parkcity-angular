# ParkCity – Angular + Laravel API

Isti sistem javnog parkiranja (ista tema, iste uloge i ista struktura baze), ali ovog puta
**frontend je Angular**, a Laravel je samo **REST API** koji vraća JSON.

U navbaru su samo **dve funkcionalnosti**:

| Funkcionalnost | Ko je koristi |
|---|---|
| **Parking karte** – kupovina (uživo cena), lista, produženje, otkazivanje, detalji | građanin (kupuje), operater / kontrolor / admin (pregled svih karata) |
| **Kontrola i kazne** – provera vozila po tablici, predlog statusa, evidentiranje kontrole, automatska kazna, lista i naplata kazni | kontrolor (kontroliše i naplaćuje), admin (pregled + naplata), građanin (vidi svoje kazne) |

```
parkcity-angular/
├── backend/    Laravel 11 REST API (Sanctum token prijava)   -> http://localhost:8000
└── frontend/   Angular 19 (standalone komponente, Bootstrap) -> http://localhost:4200
```

---

## 1. Šta treba da je instalirano (Windows)

- **XAMPP** (MySQL mora biti pokrenut) – isto kao kod prvog projekta
- **PHP 8.2+** i **Composer** (već imaš)
- **Node.js 20 ili 22 (LTS)** – https://nodejs.org (proveri: `node -v`)

## 2. Pokretanje backenda (Laravel API)

1. U phpMyAdmin-u (http://localhost/phpmyadmin) napravi **novu bazu** `parkcity_angular`
   (kolacija `utf8mb4_unicode_ci`). Stara baza `parkcity` ostaje netaknuta.
2. Otvori CMD u folderu `backend` i pokreni redom:

```
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

3. Proveri u pregledaču http://localhost:8000 – treba da vidiš `{"app":"ParkCity API","status":"ok",...}`.

Ako imaš lozinku na MySQL-u ili drugačije ime baze, izmeni `DB_*` linije u `backend\.env`.
Prozor sa `php artisan serve` ostaje otvoren dok koristiš aplikaciju.

## 3. Pokretanje frontenda (Angular)

U **drugom** CMD prozoru, u folderu `frontend`:

```
npm install
npm start
```

Sačekaj da piše `Local: http://localhost:4200/` i otvori tu adresu.
(`npm install` traje par minuta, radi se samo prvi put.)

> Prečica: dupli klik na `start-backend.bat` i `start-frontend.bat` (posle prvog podešavanja iz koraka 2 i 3).

## 4. Nalozi za prijavu (lozinka za sve: `password`)

| Uloga | Email |
|---|---|
| Građanin | `gradjanin@parkcity.rs` (Marko), `ana@parkcity.rs`, `petar@parkcity.rs` |
| Kontrolor | `kontrolor@parkcity.rs` |
| Operater | `operater@parkcity.rs` |
| Admin | `admin@parkcity.rs` |

Na stranici za prijavu postoji i **registracija** (pravi nalog građanina). Operater i kontrolor
nalozi se prave u bazi (seeder), jer admin ekran za odobravanje nije deo ove dve funkcionalnosti.

Primeri za demonstraciju: tablice **NP-123-AA** (ima aktivnu kartu → predlog „ispravno"),
**NP-999-ZZ** (nikad nije imao kartu → „neplaćeno"), **NP-456-BB** (karta istekla → „isteklo").

---

## 5. Kako sve radi (za odbranu)

```
Angular (browser)  --HTTP + JSON + Bearer token-->  Laravel API  --Eloquent-->  MySQL
```

1. Korisnik se prijavi: Angular šalje `POST /api/login`. Laravel proveri lozinku i vrati **token** (Sanctum).
2. Angular čuva token u `localStorage`. Svaki sledeći zahtev dobija zaglavlje `Authorization: Bearer <token>`
   (to radi **interceptor**).
3. Laravel na svakoj zaštićenoj ruti proverava tri stvari: **token** (`auth:sanctum`), **aktivan nalog**
   (`active`) i **ulogu** (`role:...`).
4. Angular **guard-ovi** dodatno ne puštaju korisnika na stranicu za koju nema ulogu (to je samo
   udobnost – prava zaštita je na serveru).

### Gde je šta u kodu

**Backend (`backend/`)**

| Šta | Fajl |
|---|---|
| Sve rute API-ja i ko sme gde | `routes/api.php` |
| Prijava, registracija, odjava | `app/Http/Controllers/AuthController.php` |
| Kupovina / produženje / otkazivanje karte, cena | `app/Http/Controllers/TicketController.php` |
| Provera vozila (`lookup`) i evidencija kontrole + kazna | `app/Http/Controllers/ControlController.php` |
| Lista i naplata kazni | `app/Http/Controllers/FineController.php` |
| Zone sa brojem slobodnih mesta | `app/Http/Controllers/ZoneController.php` |
| Provera uloge / aktivnosti naloga | `app/Http/Middleware/RoleMiddleware.php`, `EnsureUserIsActive.php` |
| Aliasi middleware-a, JSON greške | `bootstrap/app.php` |
| Tabele baze | `database/migrations/` |
| Test podaci | `database/seeders/DatabaseSeeder.php`, `DemoPodaciSeeder.php` |
| Dozvoljene adrese za Angular (CORS) | `config/cors.php` |

**Frontend (`frontend/src/app/`)**

| Šta | Fajl |
|---|---|
| Rute aplikacije + guard-ovi po ulozi | `app.routes.ts`, `core/guards.ts` |
| Navbar (samo dve funkcionalnosti) | `app.component.html` |
| Prijava, token, trenutni korisnik (signal) | `core/auth.service.ts` |
| Dodavanje tokena na svaki zahtev, 401 → login | `core/auth.interceptor.ts` |
| Pozivi ka API-ju (HttpClient) | `core/api.services.ts` |
| Tipovi podataka | `core/models.ts` |
| Adresa API-ja | `core/api.config.ts` |
| Lista karata (filter, paginacija, produži, otkaži) | `pages/tickets/ticket-list.component.*` |
| Kupovina karte (reaktivna forma, cena uživo) | `pages/tickets/ticket-form.component.*` |
| Detalji karte (preostalo vreme) | `pages/tickets/ticket-detail.component.*` |
| Tabovi „Kontrola i kazne" | `pages/controls/controls-shell.component.*` |
| Provera vozila + potvrda kontrole | `pages/controls/vehicle-check.component.*` |
| Istorija kontrola | `pages/controls/control-history.component.*` |
| Kazne + naplata | `pages/controls/fines.component.*` |
| Obojeni status, paginacija (deljene komponente) | `shared/` |

### Poslovna pravila (ista kao u prvom projektu)

- Cena karte = `tarifa zone po satu × minuti / 60`; trajanje ne sme preći maksimum zone.
- Kontrola predlaže: **ispravno** (aktivna karta ili važeća rezidentska dozvola),
  **isteklo** (ranije je imao kartu), **neplaćeno** (nikad nije imao kartu).
- Rezultat različit od „ispravno" automatski pravi **kaznu od 3000 RSD** (0..1 kazna po kontroli).
- Plaćanje karte je simulirano (karta je aktivna odmah po kupovini).

### Dopune u odnosu na prvi projekat

- Produženje karte proverava da je karta aktivna i da ukupno trajanje ne prelazi maksimum zone.
- Otkazati se može samo aktivna karta; kazna se ne može platiti dva puta;
  kontrolor naplaćuje samo kazne iz svojih kontrola.
- Pri izboru zone prikazuje se broj slobodnih mesta (konkretno mesto se ne bira – kao ni u prvom projektu,
  jer karta se vezuje za zonu).

### Moguća pitanja

- **Zašto Angular + Laravel API, a ne Blade?** Frontend i backend su odvojeni: Angular je SPA (stranice se
  menjaju bez ponovnog učitavanja), a Laravel samo vraća JSON. Isti API bi mogao da koristi i mobilna aplikacija.
- **Kako se čuva prijava?** Token (Laravel Sanctum) u `localStorage`, šalje se kao Bearer token.
- **Ko brani pristup?** Server: `auth:sanctum` + `active` + `role` middleware. Klijent: guard-ovi i sakrivene stavke menija.
- **Šta je interceptor?** Funkcija koja presreće svaki HTTP zahtev; ovde dodaje token i hvata 401.
- **Šta je signal?** Angularov reaktivni „kontejner" za vrednost; koristi se za trenutnog korisnika, podatke u listama
  i uživo izračunatu cenu (`computed`).
- **Šta je CORS?** Pravilo pregledača: Angular (port 4200) sme da zove API (port 8000) samo ako ga server dozvoli – `config/cors.php`.

---

## 6. Ako nešto ne radi

| Problem | Rešenje |
|---|---|
| Angular piše „Server nije dostupan" | Nije pokrenut `php artisan serve` ili je MySQL ugašen u XAMPP-u |
| `SQLSTATE ... Unknown database` | Nisi napravila bazu `parkcity_angular` u phpMyAdmin-u |
| `No application encryption key` | Pokreni `php artisan key:generate` |
| `composer install` javlja security advisory | Pokreni `composer update` pa ponovo `composer install` |
| Posle izmene `.env` ništa se ne menja | `php artisan config:clear`, pa ponovo `php artisan serve` |
| Prijava uspe, ali posle toga 401/403 | Odjavi se i prijavi ponovo; proveri da je `APP_KEY` generisan |
| Port 4200 ili 8000 zauzet | Zatvori stari prozor ili pokreni `ng serve --port 4300` (tada dodaj `http://localhost:4300` u `CORS_ALLOWED_ORIGINS` u `backend\.env`) |
| Hoću sve podatke ispočetka | `php artisan migrate:fresh --seed` |
