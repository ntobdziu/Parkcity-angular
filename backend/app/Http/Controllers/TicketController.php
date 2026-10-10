<?php

namespace App\Http\Controllers;

use App\Models\ParkingTicket;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    private const RELATIONS = ['vehicle:id,plate,brand,model', 'zone:id,name,tariff_per_hour,max_duration_minutes', 'user:id,name'];

    /**
     * GET /api/tickets?page=1&status=aktivna
     * Gradjanin vidi svoje karte, ostale uloge (operater, kontrolor, admin) vide sve.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', 'in:aktivna,istekla,otkazana']]);

        // karte kojima je vreme isteklo prebacujemo u "istekla"
        ParkingTicket::where('status', 'aktivna')->where('end_time', '<', now())->update(['status' => 'istekla']);

        $user = $request->user();
        $query = ParkingTicket::with(self::RELATIONS)->latest('start_time');

        if ($user->role === 'gradjanin') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->paginate(10));
    }

    /** GET /api/tickets/{ticket} */
    public function show(Request $request, ParkingTicket $ticket): JsonResponse
    {
        $this->authorizeView($request, $ticket);
        $ticket->syncStatus();

        return response()->json($ticket->load(self::RELATIONS));
    }

    /** POST /api/tickets  ->  kupovina karte (cena = tarifa zone x minuti / 60) */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'zone_id' => ['required', 'exists:zones,id'],
            'channel' => ['required', 'in:app,sms,automat'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
        ]);

        $user = $request->user();
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        abort_unless($vehicle->user_id === $user->id, 403, 'Vozilo ne pripada prijavljenom korisniku.');

        $zone = Zone::findOrFail($data['zone_id']);

        if ($zone->max_duration_minutes && $data['duration_minutes'] > $zone->max_duration_minutes) {
            throw ValidationException::withMessages([
                'duration_minutes' => ["Maksimalno dozvoljeno trajanje u ovoj zoni je {$zone->max_duration_minutes} minuta."],
            ]);
        }

        $start = now();
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);
        $price = round($zone->tariff_per_hour * ($data['duration_minutes'] / 60), 2);

        $ticket = ParkingTicket::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'zone_id' => $zone->id,
            'channel' => $data['channel'],
            'start_time' => $start,
            'end_time' => $end,
            'price' => $price,
            'status' => 'aktivna',
            'qr_code' => strtoupper(Str::random(10)),
        ]);

        ActivityLogger::log("Kupljena parking karta za vozilo {$vehicle->plate} (zona {$zone->name})");

        return response()->json($ticket->load(self::RELATIONS), 201);
    }

    /** POST /api/tickets/{ticket}/extend  ->  produzenje aktivne karte */
    public function extend(Request $request, ParkingTicket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 403, 'Karta ne pripada prijavljenom korisniku.');

        $data = $request->validate([
            'extra_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
        ]);

        $ticket->syncStatus();

        if ($ticket->status !== 'aktivna') {
            throw ValidationException::withMessages(['extra_minutes' => ['Moguće je produžiti samo aktivnu kartu.']]);
        }

        $zone = $ticket->zone;
        $totalMinutes = (int) round($ticket->start_time->diffInMinutes($ticket->end_time)) + $data['extra_minutes'];

        if ($zone->max_duration_minutes && $totalMinutes > $zone->max_duration_minutes) {
            throw ValidationException::withMessages([
                'extra_minutes' => ["Ukupno trajanje ne sme preći {$zone->max_duration_minutes} minuta u ovoj zoni."],
            ]);
        }

        $ticket->end_time = $ticket->end_time->copy()->addMinutes((int) $data['extra_minutes']);
        $ticket->price = round($ticket->price + $zone->tariff_per_hour * ($data['extra_minutes'] / 60), 2);
        $ticket->save();

        ActivityLogger::log("Produžena parking karta #{$ticket->id}");

        return response()->json($ticket->load(self::RELATIONS));
    }

    /** POST /api/tickets/{ticket}/cancel */
    public function cancel(Request $request, ParkingTicket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 403, 'Karta ne pripada prijavljenom korisniku.');

        $ticket->syncStatus();

        if ($ticket->status !== 'aktivna') {
            throw ValidationException::withMessages(['status' => ['Moguće je otkazati samo aktivnu kartu.']]);
        }

        $ticket->update(['status' => 'otkazana']);
        ActivityLogger::log("Otkazana parking karta #{$ticket->id}");

        return response()->json($ticket->load(self::RELATIONS));
    }

    private function authorizeView(Request $request, ParkingTicket $ticket): void
    {
        $user = $request->user();

        abort_unless($user->role !== 'gradjanin' || $ticket->user_id === $user->id, 403, 'Nemate pravo da vidite ovu kartu.');
    }
}
