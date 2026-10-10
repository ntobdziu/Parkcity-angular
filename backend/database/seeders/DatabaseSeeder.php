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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // 1) KORISNICI (svih 5 stanja/rola koje treba pokazati)
        // =====================================================
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@parkcity.rs',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '060 000 0001',
        ]);

        $operater = User::create([
            'name' => 'Operater Jedan',
            'email' => 'operater@parkcity.rs',
            'password' => Hash::make('password'),
            'role' => 'operater',
            'status' => 'active',
            'phone' => '060 000 0002',
        ]);

        $kontrolor = User::create([
            'name' => 'Kontrolor Jedan',
            'email' => 'kontrolor@parkcity.rs',
            'password' => Hash::make('password'),
            'role' => 'kontrolor',
            'status' => 'active',
            'phone' => '060 000 0003',
        ]);

        $gradjanin1 = User::create([
            'name' => 'Marko Marković',
            'email' => 'gradjanin@parkcity.rs',
            'password' => Hash::make('password'),
            'role' => 'gradjanin',
            'status' => 'active',
            'phone' => '060 000 0004',
        ]);

        $gradjanin2 = User::create([
            'name' => 'Ana Anić',
            'email' => 'ana@parkcity.rs',
            'password' => Hash::make('password'),
            'role' => 'gradjanin',
            'status' => 'active',
            'phone' => '060 111 2222',
        ]);

        $gradjanin3 = User::create([
            'name' => 'Petar Petrović',
            'email' => 'petar@parkcity.rs',
            'password' => Hash::make('password'),
            'role' => 'gradjanin',
            'status' => 'active',
            'phone' => '060 333 4444',
        ]);

        // nalog koji čeka odobrenje administratora (demo tok odobravanja)
        $noviOperater = User::create([
            'name' => 'Novi Operater (na čekanju)',
            'email' => 'novi.operater@parkcity.rs',
            'password' => Hash::make('password'),
            'role' => 'operater',
            'status' => 'pending',
        ]);

        // =====================================================
        // 2) ZONE
        // =====================================================
        $zone1 = Zone::create([
            'name' => 'Zona 1 - Centar',
            'location' => 'Centar grada',
            'tariff_per_hour' => 100,
            'max_duration_minutes' => 180,
            'work_hours_from' => '07:00',
            'work_hours_to' => '21:00',
            'type' => 'standardna',
        ]);

        $zone2 = Zone::create([
            'name' => 'Zona 2 - Naselje',
            'location' => 'Stambeno naselje',
            'tariff_per_hour' => 60,
            'max_duration_minutes' => null,
            'work_hours_from' => '08:00',
            'work_hours_to' => '20:00',
            'type' => 'rezidentska',
        ]);

        $zone3 = Zone::create([
            'name' => 'Zona 3 - Bolnica',
            'location' => 'Krug bolnice',
            'tariff_per_hour' => 80,
            'max_duration_minutes' => 120,
            'work_hours_from' => '00:00',
            'work_hours_to' => '23:59',
            'type' => 'invalidska',
        ]);

        // =====================================================
        // 3) PARKING MESTA
        // =====================================================
        $zone1Spots = collect(range(1, 8))->map(fn ($i) => ParkingSpot::create([
            'zone_id' => $zone1->id,
            'code' => 'Z1-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'status' => 'slobodno',
            'special_type' => $i === 1 ? 'invalidsko' : null,
        ]));

        $zone2Spots = collect(range(1, 5))->map(fn ($i) => ParkingSpot::create([
            'zone_id' => $zone2->id,
            'code' => 'Z2-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'status' => 'slobodno',
        ]));

        $zone3Spots = collect(range(1, 4))->map(fn ($i) => ParkingSpot::create([
            'zone_id' => $zone3->id,
            'code' => 'Z3-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'status' => 'slobodno',
            'special_type' => $i <= 2 ? 'invalidsko' : null,
        ]));

        // par mesta odmah "zauzeto" da izveštaj o popunjenosti ne bude prazan
        $zone1Spots[2]->update(['status' => 'zauzeto']);
        $zone1Spots[3]->update(['status' => 'zauzeto']);
        $zone3Spots[0]->update(['status' => 'zauzeto']);

        // =====================================================
        // 4) VOZILA
        // =====================================================
        $vehicle1 = Vehicle::create([
            'user_id' => $gradjanin1->id,
            'plate' => 'NP-123-AA',
            'brand' => 'Škoda',
            'model' => 'Octavia',
            'type' => 'putnicko',
            'year' => 2019,
        ]);

        $vehicle2 = Vehicle::create([
            'user_id' => $gradjanin2->id,
            'plate' => 'NP-456-BB',
            'brand' => 'Volkswagen',
            'model' => 'Golf 7',
            'type' => 'putnicko',
            'year' => 2021,
        ]);

        $vehicle3 = Vehicle::create([
            'user_id' => $gradjanin3->id,
            'plate' => 'NP-789-CC',
            'brand' => 'Renault',
            'model' => 'Kangoo',
            'type' => 'dostavno',
            'year' => 2017,
        ]);

        $vehicle4 = Vehicle::create([
            'user_id' => $gradjanin1->id,
            'plate' => 'NP-999-ZZ',
            'brand' => 'Yamaha',
            'model' => 'NMAX',
            'type' => 'motocikl',
            'year' => 2022,
        ]);

        // =====================================================
        // 5) PARKING KARTE (aktivna / istekla / otkazana)
        // =====================================================
        $ticketActive = ParkingTicket::create([
            'user_id' => $gradjanin1->id,
            'vehicle_id' => $vehicle1->id,
            'zone_id' => $zone1->id,
            'channel' => 'app',
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addHours(2),
            'price' => 200,
            'status' => 'aktivna',
            'qr_code' => strtoupper(Str::random(10)),
        ]);

        $ticketExpired = ParkingTicket::create([
            'user_id' => $gradjanin2->id,
            'vehicle_id' => $vehicle2->id,
            'zone_id' => $zone1->id,
            'channel' => 'automat',
            'start_time' => now()->subHours(5),
            'end_time' => now()->subHours(3),
            'price' => 200,
            'status' => 'istekla',
            'qr_code' => strtoupper(Str::random(10)),
        ]);

        ParkingTicket::create([
            'user_id' => $gradjanin3->id,
            'vehicle_id' => $vehicle3->id,
            'zone_id' => $zone2->id,
            'channel' => 'sms',
            'start_time' => now()->subDay(),
            'end_time' => now()->subDay()->addHours(1),
            'price' => 60,
            'status' => 'otkazana',
            'qr_code' => strtoupper(Str::random(10)),
        ]);

        // =====================================================
        // 6) KONTROLE + KAZNE (0,1)-(1,1) - tacno po EER modelu
        // =====================================================

        // a) vozilo SA aktivnom kartom -> rezultat ispravno -> BEZ kazne
        Control::create([
            'controller_id' => $kontrolor->id,
            'vehicle_id' => $vehicle1->id,
            'location' => 'Ulica Kralja Petra, Zona 1',
            'timestamp' => now()->subMinutes(10),
            'result' => 'ispravno',
        ]);

        // b) vozilo BEZ ikakve karte -> rezultat neplaceno -> kazna NEPLACENA
        $controlUnpaid = Control::create([
            'controller_id' => $kontrolor->id,
            'vehicle_id' => $vehicle4->id,
            'location' => 'Trg Republike, Zona 1',
            'timestamp' => now()->subHours(1),
            'result' => 'neplaceno',
        ]);
        Fine::create([
            'control_id' => $controlUnpaid->id,
            'vehicle_id' => $vehicle4->id,
            'amount' => 3000,
            'status' => 'neplacena',
            'issued_at' => now()->subHours(1),
        ]);

        // c) vozilo sa isteklom kartom -> rezultat isteklo -> kazna VEC PLACENA
        $controlPaid = Control::create([
            'controller_id' => $kontrolor->id,
            'vehicle_id' => $vehicle2->id,
            'location' => 'Bulevar oslobodjenja, Zona 1',
            'timestamp' => now()->subHours(2),
            'result' => 'isteklo',
        ]);
        Fine::create([
            'control_id' => $controlPaid->id,
            'vehicle_id' => $vehicle2->id,
            'amount' => 3000,
            'status' => 'placena',
            'payment_method' => 'kartica',
            'issued_at' => now()->subHours(2),
            'paid_at' => now()->subMinutes(40),
        ]);

        // =====================================================
        // 7) REZIDENTSKE DOZVOLE (na_cekanju / aktivna / odbijena)
        // =====================================================
        ResidentPermit::create([
            'user_id' => $gradjanin2->id,
            'zone_id' => $zone2->id,
            'plate' => $vehicle2->plate,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
            'status' => 'na_cekanju',
        ]);

        ResidentPermit::create([
            'user_id' => $gradjanin1->id,
            'zone_id' => $zone2->id,
            'plate' => $vehicle1->plate,
            'valid_from' => now()->subMonths(2)->toDateString(),
            'valid_until' => now()->addMonths(10)->toDateString(),
            'status' => 'aktivna',
        ]);

        ResidentPermit::create([
            'user_id' => $gradjanin3->id,
            'zone_id' => $zone2->id,
            'plate' => $vehicle3->plate,
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
            'status' => 'odbijena',
        ]);

        // =====================================================
        // 8) REZERVACIJE (aktivna / zavrsena / otkazana)
        // =====================================================
        Reservation::create([
            'user_id' => $gradjanin3->id,
            'spot_id' => $zone2Spots[0]->id,
            'start_time' => now()->addHours(3),
            'end_time' => now()->addHours(5),
            'status' => 'aktivna',
        ]);
        $zone2Spots[0]->update(['status' => 'rezervisano']);

        Reservation::create([
            'user_id' => $gradjanin1->id,
            'spot_id' => $zone1Spots[4]->id,
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(2)->addHours(2),
            'status' => 'zavrsena',
        ]);

        Reservation::create([
            'user_id' => $gradjanin2->id,
            'spot_id' => $zone3Spots[2]->id,
            'start_time' => now()->subDay(),
            'end_time' => now()->subDay()->addHours(1),
            'status' => 'otkazana',
        ]);

        // =====================================================
        // 9) LOG AKTIVNOSTI (da admin odmah ima sta da vidi)
        // =====================================================
        $logs = [
            [$admin, 'Prijava na sistem', now()->subHours(6)],
            [$operater, 'Kreirana zona Zona 1 - Centar', now()->subDays(10)],
            [$operater, 'Kreirano parking mesto Z1-01', now()->subDays(10)],
            [$gradjanin1, 'Registracija vozila NP-123-AA', now()->subDays(5)],
            [$gradjanin1, 'Kupljena parking karta za vozilo NP-123-AA (zona Zona 1 - Centar)', now()->subMinutes(30)],
            [$gradjanin2, 'Registracija vozila NP-456-BB', now()->subDays(3)],
            [$kontrolor, 'Izvrsena kontrola vozila (rezultat: ispravno)', now()->subMinutes(10)],
            [$kontrolor, 'Izvrsena kontrola vozila (rezultat: neplaceno)', now()->subHours(1)],
            [$kontrolor, 'Izvrsena kontrola vozila (rezultat: isteklo)', now()->subHours(2)],
            [$gradjanin3, 'Rezervisano parking mesto Z2-01', now()->subHours(4)],
            [$gradjanin2, 'Zahtev za rezidentsku dozvolu (zona #'.$zone2->id.')', now()->subDay()],
            [$admin, 'Odobren nalog korisnika operater@parkcity.rs', now()->subDays(15)],
        ];

        foreach ($logs as [$user, $action, $when]) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'timestamp' => $when,
            ]);
        }

        // Dodatni demo podaci (vise korisnika, karata, kontrola i kazni)
        $this->call(DemoPodaciSeeder::class);
    }
}
