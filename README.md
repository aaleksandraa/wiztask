# WizTask — Interni Task Manager

Interna web aplikacija za jednog admina: evidencija klijenata, projekata, taskova,
utrošenog vremena, cijena, statusa rada, naplate i fajlova/dokaza rada, sa izvještajima
po klijentu i periodu (PDF / Excel / print).

Stack: **Laravel 11 + Livewire 3 + Tailwind (Breeze) + MySQL** (lokalno SQLite).

## Moduli
- **Dashboard** — kartice (aktivni klijenti, taskovi u toku, završeno ovaj mjesec, za naplatu,
  neplaćen iznos, sati i vrijednost poslova ovaj mjesec) + liste (najnoviji, u toku, čekaju klijenta, za naplatu, sa rokom).
- **Klijenti** — CRUD, pretraga/filteri, profil sa tabovima (Pregled, Projekti, Taskovi, Vrijeme, Naplata, Fajlovi, Izvještaji).
- **Projekti** — CRUD, detalji, taskovi projekta, ukupno vrijeme/vrijednost, upload više fajlova.
- **Taskovi** — CRUD, svi filteri (klijent, projekat, status, prioritet, datum od/do, mjesec, godina,
  naplativo, status plaćanja, tip naplate, pretraga), promjena statusa, dupliranje, arhiviranje.
- **Vrijeme** — time_entries (globalna lista + unos na tasku), automatski zbir vremena i cijene na tasku.
- **Fajlovi** — polymorphic attachments (projekti + taskovi), upload više odjednom, galerija, kategorije, download, brisanje.
- **Izvještaji** — po klijentu i periodu, opcioni filteri, totali, export u PDF i Excel + print verzija.
- **Podešavanja** — naziv aplikacije, default valuta i satnica, dozvoljeni tipovi fajlova.

## Pravila obračuna
- `po satu` → ukupna cijena = ukupno vrijeme (iz time_entries) × satnica
- `fiksno` → ukupna cijena = fiksna cijena
- `bez naplate` → 0
- `uključeno u paket` → vrijeme se vodi, ali ne ulazi u dodatnu naplatu (0)

## Lokalno pokretanje
```bash
composer install
npm install
cp .env.example .env        # već postoji .env; po potrebi
php artisan key:generate
php artisan migrate --seed   # kreira admina + demo podatke (samo local)
npm run build                # ili: npm run dev
php artisan serve
```
Login: **admin@wiztask.test** / **password**

> Lokalno je baza SQLite (`database/database.sqlite`), radi bez MySQL servera.

## Deploy na Plesk (MySQL)
1. U `.env` prebaci na MySQL (vidi zakomentarisani blok):
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=wiztask
   DB_USERNAME=wiztask
   DB_PASSWORD=********
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tvoj-domen
   ```
2. Postavi document root na `public/`.
3. Komande na serveru:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder --force   # kreira admina i postavke
   php artisan storage:link
   npm ci && npm run build
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
4. Provjeri da je `storage/` i `bootstrap/cache/` upisivo, te da postoji symlink `public/storage`.

## Korisni podaci
- Upload disk: `public` (`storage/app/public`), izloženo preko `public/storage`.
- Dozvoljeni tipovi fajlova se podešavaju u Podešavanjima (default: jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip).
- Maksimalna veličina fajla: 20 MB (podesivo u `app/Livewire/Attachments/Manager.php`).
