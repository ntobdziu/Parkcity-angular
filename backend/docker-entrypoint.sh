#!/bin/sh
set -e

# 1) Napravi tabele (bezbedno za ponavljanje, ne brise podatke)
php artisan migrate --force

# 2) Popuni demo podacima samo ako je baza prazna (nema korisnika)
USERS=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -n 1 | tr -dc '0-9')
if [ -z "$USERS" ] || [ "$USERS" = "0" ]; then
    echo ">> Baza je prazna, punim demo podatke (db:seed)..."
    php artisan db:seed --force
fi

# 3) Pokreni server na portu koji Render zadaje (PORT, obicno 10000)
exec php -S 0.0.0.0:${PORT:-10000} -t public public/index.php
