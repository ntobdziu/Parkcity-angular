<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Control;
use App\Models\Fine;
use App\Models\ParkingSpot;
use App\Models\ParkingTicket;
use App\Models\Reservation;
use App\Models\ResidentPermit;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Dodatni demo podaci: puno korisnika, vozila, karata (svi statusi),
 * rezervacija (proslost / u toku / buducnost / otkazane), dozvola
 * (svi statusi), kontrola i kazni (svi statusi) i logova.
 *
 * Pokrece se NAKON osnovnog seedera:
 *   php artisan migrate:fresh --seed
 *   php artisan db:seed --class=DemoPodaciSeeder
 */
class DemoPodaciSeeder extends Seeder
{
    public function run(): void
    {
        if (Vehicle::where('plate', 'NP-111-DD')->exists()) {
            $this->command->warn('Demo podaci su vec ubaceni. Pokreni: php artisan migrate:fresh --seed');
            return;
        }

        mt_srand(42); // isti raspored podataka pri svakom pokretanju

        $admin     = User::where('email', 'admin@parkcity.rs')->firstOrFail();
        $operater  = User::where('email', 'operater@parkcity.rs')->firstOrFail();
        $kontrolor = User::where('email', 'kontrolor@parkcity.rs')->firstOrFail();
        $marko     = User::where('email', 'gradjanin@parkcity.rs')->firstOrFail();
        $ana       = User::where('email', 'ana@parkcity.rs')->firstOrFail();
        $petar     = User::where('email', 'petar@parkcity.rs')->firstOrFail();

        $zone1 = Zone::where('type', 'standardna')->firstOrFail();
        $zone2 = Zone::where('type', 'rezidentska')->firstOrFail();
        $zone3 = Zone::where('type', 'invalidska')->firstOrFail();
        $zones = [$zone1, $zone2, $zone3];

        // ---------------------------------------------------------
        // 1) JOS KORISNIKA (svi statusi i role)
        // ---------------------------------------------------------
        $mk = fn ($name, $email, $role, $status, $phone = null) => User::create([
            'name' => $name, 'email' => $email, 'password' => Hash::make('password'),
            'role' => $role, 'status' => $status, 'phone' => $phone,
        ]);

        $jovana = $mk('Jovana Jovanović', 'jovana@parkcity.rs', 'gradjanin', 'active', '061 100 2001');
        $nikola = $mk('Nikola Nikolić', 'nikola@parkcity.rs', 'gradjanin', 'active', '061 100 2002');
        $milica = $mk('Milica Ilić', 'milica@parkcity.rs', 'gradjanin', 'active', '061 100 2003');
        $stefan = $mk('Stefan Stefanović', 'stefan@parkcity.rs', 'gradjanin', 'active', '061 100 2004');
        $mk('Ivana Kostić (deaktivirana)', 'ivana@parkcity.rs', 'gradjanin', 'inactive', '061 100 2005');
        $kontrolor2 = $mk('Kontrolor Dva', 'kontrolor2@parkcity.rs', 'kontrolor', 'active', '060 000 0013');
        $mk('Novi Kontrolor (na čekanju)', 'novi.kontrolor@parkcity.rs', 'kontrolor', 'pending');
        $operater2 = $mk('Operater Dva', 'operater2@parkcity.rs', 'operater', 'active', '060 000 0012');

        $gradjani = [$marko, $ana, $petar, $jovana, $nikola, $milica, $stefan];
        $kontrolori = [$kontrolor, $kontrolor2];

        // ---------------------------------------------------------
        // 2) JOS PARKING MESTA (da ima sta da se rezervise)
        // ---------------------------------------------------------
        $nova = [
            [$zone1, range(9, 14), fn ($i) => $i === 14 ? 'elektro' : null],
            [$zone2, range(6, 10), fn ($i) => null],
            [$zone3, range(5, 8),  fn ($i) => $i === 5 ? 'invalidsko' : null],
        ];
        foreach ($nova as [$zone, $range, $special]) {
            foreach ($range as $i) {
                $prefix = 'Z'.($zone->id === $zone1->id ? 1 : ($zone->id === $zone2->id ? 2 : 3));
                ParkingSpot::firstOrCreate(
                    ['zone_id' => $zone->id, 'code' => $prefix.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
                    ['status' => 'slobodno', 'special_type' => $special($i)]
                );
            }
        }

        // ---------------------------------------------------------
        // 3) VOZILA (ukupno oko 15)
        // ---------------------------------------------------------
        $vozilaPodaci = [
            [$marko,  'NP-111-DD', 'Opel',       'Astra',    'putnicko', 2018],
            [$ana,    'NP-222-EE', 'Fiat',       'Punto',    'putnicko', 2015],
            [$ana,    'NP-333-FF', 'Peugeot',    '208',      'putnicko', 2022],
            [$petar,  'NP-444-GG', 'Citroen',    'Berlingo', 'dostavno', 2016],
            [$jovana, 'NP-555-HH', 'Toyota',     'Yaris',    'putnicko', 2020],
            [$jovana, 'NP-666-II', 'Vespa',      'Primavera','motocikl', 2023],
            [$nikola, 'NP-777-JJ', 'BMW',        '320d',     'putnicko', 2019],
            [$nikola, 'NP-888-KK', 'Mercedes',   'Sprinter', 'dostavno', 2014],
            [$milica, 'NP-135-LL', 'Hyundai',    'i20',      'putnicko', 2021],
            [$stefan, 'NP-246-MM', 'Kia',        'Ceed',     'putnicko', 2017],
            [$stefan, 'NP-357-NN', 'Honda',      'PCX',      'motocikl', 2021],
        ];
        $vozila = Vehicle::whereIn('user_id', array_map(fn ($u) => $u->id, $gradjani))->get()->all();
        foreach ($vozilaPodaci as [$vlasnik, $tablica, $marka, $model, $tip, $god]) {
            $vozila[] = Vehicle::create([
                'user_id' => $vlasnik->id, 'plate' => $tablica, 'brand' => $marka,
                'model' => $model, 'type' => $tip, 'year' => $god,
            ]);
        }

        // ---------------------------------------------------------
        // 4) PARKING KARTE: istorija 30 dana + aktivne + otkazane
        // ---------------------------------------------------------
        $kanali = ['app', 'sms', 'automat'];
        $ticket = function (Vehicle $v, Zone $z, $start, int $min, string $status, string $kanal) {
            return ParkingTicket::create([
                'user_id' => $v->user_id, 'vehicle_id' => $v->id, 'zone_id' => $z->id,
                'channel' => $kanal, 'start_time' => $start, 'end_time' => $start->copy()->addMinutes($min),
                'price' => round($z->tariff_per_hour * $min / 60, 2),
                'status' => $status, 'qr_code' => strtoupper(Str::random(10)),
            ]);
        };

        // 45 isteklih karata u poslednjih 30 dana (za izvestaje o prihodu)
        for ($i = 0; $i < 45; $i++) {
            $v = $vozila[array_rand($vozila)];
            $z = $zones[array_rand($zones)];
            $max = $z->max_duration_minutes ?: 240;
            $min = max(30, (int) (round(mt_rand(30, $max) / 30) * 30));
            $start = now()->subDays(mt_rand(1, 30))->setTime(mt_rand(8, 18), [0, 15, 30, 45][mt_rand(0, 3)]);
            $ticket($v, $z, $start, min($min, $max), 'istekla', $kanali[mt_rand(0, 2)]);
        }

        // 5 aktivnih karata trenutno
        foreach ([[0, 0, 90], [4, 0, 150], [6, 0, 60], [8, 2, 90], [10, 1, 240]] as [$vi, $zi, $min]) {
            $ticket($vozila[$vi], $zones[$zi], now()->subMinutes(mt_rand(5, 40)), $min, 'aktivna', $kanali[mt_rand(0, 2)]);
        }

        // 6 otkazanih karata
        for ($i = 0; $i < 6; $i++) {
            $ticket($vozila[array_rand($vozila)], $zones[array_rand($zones)],
                now()->subDays(mt_rand(1, 14))->setTime(mt_rand(9, 17), 0), 60, 'otkazana', $kanali[mt_rand(0, 2)]);
        }

        // ---------------------------------------------------------
        // 5) REZIDENTSKE DOZVOLE (sva 4 statusa)
        // ---------------------------------------------------------
        $dozvola = fn ($u, $plate, $od, $do, $status) => ResidentPermit::create([
            'user_id' => $u->id, 'zone_id' => $zone2->id, 'plate' => $plate,
            'valid_from' => $od->toDateString(), 'valid_until' => $do->toDateString(), 'status' => $status,
        ]);

        $dozvola($jovana, 'NP-555-HH', now()->subMonths(3), now()->addMonths(9), 'aktivna');
        $dozvola($nikola, 'NP-777-JJ', now()->subMonths(1), now()->addMonths(11), 'aktivna');
        $dozvola($milica, 'NP-135-LL', now()->subDays(1),   now()->addYear(),    'na_cekanju');
        $dozvola($stefan, 'NP-246-MM', now(),               now()->addYear(),    'na_cekanju');
        $dozvola($marko,  'NP-111-DD', now()->subYear()->subMonth(), now()->subMonth(), 'istekla');
        $dozvola($ana,    'NP-333-FF', now()->subDays(5),   now()->addYear(),    'odbijena');
        $dozvola($petar,  'NP-444-GG', now()->subDays(2),   now()->addYear(),    'odbijena');

        // ---------------------------------------------------------
        // 6) REZERVACIJE: proslost, u toku, buducnost, otkazane
        // ---------------------------------------------------------
        $mesta = ParkingSpot::all()->keyBy('code');
        $rez = function (User $u, string $kod, $od, int $sati, string $status) use ($mesta) {
            $spot = $mesta[$kod];
            Reservation::create([
                'user_id' => $u->id, 'spot_id' => $spot->id,
                'start_time' => $od, 'end_time' => $od->copy()->addHours($sati), 'status' => $status,
            ]);
            if ($status === 'aktivna') {
                $spot->update(['status' => 'rezervisano']);
            }
        };

        // zavrsene (proslost)
        $rez($marko,  'Z1-02', now()->subDays(12), 2, 'zavrsena');
        $rez($ana,    'Z1-05', now()->subDays(9),  3, 'zavrsena');
        $rez($jovana, 'Z2-02', now()->subDays(8),  4, 'zavrsena');
        $rez($nikola, 'Z1-06', now()->subDays(6),  2, 'zavrsena');
        $rez($milica, 'Z3-03', now()->subDays(4),  1, 'zavrsena');
        $rez($stefan, 'Z1-07', now()->subDays(3),  2, 'zavrsena');
        $rez($petar,  'Z2-03', now()->subDays(1),  3, 'zavrsena');

        // otkazane
        $rez($marko,  'Z1-08', now()->subDays(5),  2, 'otkazana');
        $rez($jovana, 'Z3-04', now()->subDays(2),  1, 'otkazana');
        $rez($nikola, 'Z2-04', now()->addDay(),    2, 'otkazana');

        // u toku (pocela, jos traje)
        $rez($ana,    'Z1-09', now()->subMinutes(30), 3, 'aktivna');
        $rez($stefan, 'Z2-05', now()->subMinutes(15), 4, 'aktivna');

        // buduce (od danas do sledece nedelje)
        $rez($marko,  'Z1-10', now()->addHours(4),  2, 'aktivna');
        $rez($jovana, 'Z1-11', now()->addDay()->setTime(9, 0),   3, 'aktivna');
        $rez($milica, 'Z3-05', now()->addDays(2)->setTime(12, 0), 2, 'aktivna');
        $rez($nikola, 'Z2-06', now()->addDays(3)->setTime(8, 30), 5, 'aktivna');
        $rez($petar,  'Z1-12', now()->addDays(5)->setTime(16, 0), 2, 'aktivna');
        $rez($stefan, 'Z3-06', now()->addDays(7)->setTime(10, 0), 1, 'aktivna');

        // ---------------------------------------------------------
        // 7) KONTROLE + KAZNE
        // ---------------------------------------------------------
        $lokacije = ['Ulica Kralja Petra', 'Trg Republike', 'Bulevar oslobodjenja', 'Ulica Vuka Karadzica',
                     'Krug bolnice - ulaz', 'Stambeno naselje, blok 5', 'Pijaca', 'Stanica'];

        $kontrola = function (Vehicle $v, string $rezultat, $kada, ?string $kazna = null, ?string $nacin = null)
            use ($kontrolori, $lokacije) {
            $c = Control::create([
                'controller_id' => $kontrolori[mt_rand(0, 1)]->id,
                'vehicle_id' => $v->id,
                'location' => $lokacije[array_rand($lokacije)],
                'timestamp' => $kada,
                'result' => $rezultat,
            ]);
            if ($kazna !== null) {
                Fine::create([
                    'control_id' => $c->id, 'vehicle_id' => $v->id, 'amount' => 3000,
                    'status' => $kazna, 'payment_method' => $nacin,
                    'issued_at' => $kada,
                    'paid_at' => $kazna === 'placena' ? $kada->copy()->addHours(mt_rand(2, 48)) : null,
                ]);
            }
        };

        $v = fn (string $tablica) => collect($vozila)->firstWhere('plate', $tablica);

        // ispravno -> bez kazne (0 kazni)
        foreach (['NP-123-AA', 'NP-111-DD', 'NP-555-HH', 'NP-777-JJ', 'NP-135-LL', 'NP-246-MM'] as $i => $t) {
            $kontrola($v($t), 'ispravno', now()->subHours(($i + 1) * 5));
        }

        // neplaceno / isteklo -> kazna (razna stanja)
        $kontrola($v('NP-222-EE'), 'neplaceno', now()->subDays(1), 'neplacena');
        $kontrola($v('NP-333-FF'), 'isteklo',   now()->subDays(2), 'neplacena');
        $kontrola($v('NP-444-GG'), 'neplaceno', now()->subDays(3), 'placena',     'kartica');
        $kontrola($v('NP-666-II'), 'neplaceno', now()->subDays(4), 'placena',     'gotovina');
        $kontrola($v('NP-888-KK'), 'isteklo',   now()->subDays(6), 'placena',     'online');
        $kontrola($v('NP-357-NN'), 'neplaceno', now()->subDays(8), 'u_postupku');
        $kontrola($v('NP-789-CC'), 'neplaceno', now()->subDays(10), 'u_postupku');
        $kontrola($v('NP-999-ZZ'), 'isteklo',   now()->subDays(12), 'placena',    'kartica');
        $kontrola($v('NP-456-BB'), 'neplaceno', now()->subDays(15), 'neplacena');
        $kontrola($v('NP-123-AA'), 'isteklo',   now()->subDays(20), 'placena',    'online');

        // ---------------------------------------------------------
        // 8) LOG AKTIVNOSTI
        // ---------------------------------------------------------
        $logovi = [
            [$admin,     'Odobren nalog korisnika operater2@parkcity.rs', now()->subDays(20)],
            [$admin,     'Odobren nalog korisnika kontrolor2@parkcity.rs', now()->subDays(19)],
            [$admin,     'Deaktiviran nalog korisnika ivana@parkcity.rs', now()->subDays(7)],
            [$operater2, 'Odobrena rezidentska dozvola za NP-555-HH', now()->subDays(90)],
            [$operater,  'Odobrena rezidentska dozvola za NP-777-JJ', now()->subDays(30)],
            [$operater,  'Odbijena rezidentska dozvola za NP-333-FF', now()->subDays(4)],
            [$operater,  'Odbijena rezidentska dozvola za NP-444-GG', now()->subDays(1)],
            [$jovana,    'Registracija vozila NP-555-HH', now()->subDays(25)],
            [$nikola,    'Registracija vozila NP-777-JJ', now()->subDays(24)],
            [$milica,    'Zahtev za rezidentsku dozvolu (zona #'.$zone2->id.')', now()->subDay()],
            [$stefan,    'Zahtev za rezidentsku dozvolu (zona #'.$zone2->id.')', now()->subHours(3)],
            [$marko,     'Rezervisano parking mesto Z1-10', now()->subHours(2)],
            [$jovana,    'Rezervisano parking mesto Z1-11', now()->subHours(5)],
            [$nikola,    'Otkazana rezervacija mesta Z2-04', now()->subHours(8)],
            [$kontrolor, 'Izvrsena kontrola vozila (rezultat: neplaceno)', now()->subDays(1)],
            [$kontrolor2,'Izvrsena kontrola vozila (rezultat: isteklo)', now()->subDays(2)],
            [$kontrolor2,'Naplacena kazna (gotovina)', now()->subDays(4)],
            [$operater,  'Izmenjena tarifa zone Zona 1 - Centar', now()->subDays(14)],
        ];
        foreach ($logovi as [$korisnik, $akcija, $kada]) {
            ActivityLog::create(['user_id' => $korisnik->id, 'action' => $akcija, 'timestamp' => $kada]);
        }

        $this->command->info('Demo podaci ubaceni: korisnici, vozila, karte, rezervacije, dozvole, kontrole, kazne, logovi.');
    }
}
