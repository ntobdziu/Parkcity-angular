<?php

namespace App\Http\Controllers;

use App\Models\Control;
use App\Models\Fine;
use App\Models\ResidentPermit;
use App\Models\Vehicle;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControlController extends Controller
{
    const DEFAULT_FINE_AMOUNT = 3000;

    /** GET /api/controls  ->  kontrolor vidi svoje kontrole, admin sve */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Control::with(['vehicle:id,plate,brand,model', 'fine', 'controller:id,name'])->latest('timestamp');

        if ($user->role === 'kontrolor') {
            $query->where('controller_id', $user->id);
        }

        return response()->json($query->paginate(10));
    }

    /**
     * POST /api/controls/lookup  { plate }
     * Trazi vozilo i PREDLAZE rezultat kontrole:
     *   ispravno  -> ima aktivnu kartu ili vazecu rezidentsku dozvolu
     *   isteklo   -> ranije je imalo kartu, ali vise nije vazeca
     *   neplaceno -> nikad nije imalo kartu
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->merge(['plate' => strtoupper(trim((string) $request->input('plate')))]);
        $data = $request->validate(['plate' => ['required', 'string', 'max:20']]);

        $vehicle = Vehicle::with('owner:id,name')->where('plate', $data['plate'])->first();

        if (! $vehicle) {
            return response()->json([
                'message' => 'Vozilo sa navedenom registracijom nije pronađeno u sistemu.',
            ], 404);
        }

        $activeTicket = $vehicle->activeTicket()->with('zone:id,name')->first();

        $activePermit = ResidentPermit::with('zone:id,name')
            ->where('plate', $vehicle->plate)
            ->where('status', 'aktivna')
            ->whereDate('valid_from', '<=', today())
            ->whereDate('valid_until', '>=', today())
            ->first();

        $lastTicket = $vehicle->parkingTickets()->latest('end_time')->first();

        if ($activeTicket || $activePermit) {
            $suggested = 'ispravno';
        } elseif ($lastTicket) {
            $suggested = 'isteklo';
        } else {
            $suggested = 'neplaceno';
        }

        return response()->json([
            'vehicle' => $vehicle,
            'active_ticket' => $activeTicket,
            'active_permit' => $activePermit,
            'suggested_result' => $suggested,
        ]);
    }

    /**
     * POST /api/controls  { vehicle_id, location, result }
     * Upisuje kontrolu; ako rezultat nije "ispravno", automatski nastaje kazna (0..1 po kontroli).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'location' => ['required', 'string', 'max:255'],
            'result' => ['required', 'in:ispravno,neplaceno,isteklo'],
        ]);

        $control = Control::create([
            'controller_id' => $request->user()->id,
            'vehicle_id' => $data['vehicle_id'],
            'location' => $data['location'],
            'timestamp' => now(),
            'result' => $data['result'],
        ]);

        if ($data['result'] !== 'ispravno') {
            Fine::create([
                'control_id' => $control->id,
                'vehicle_id' => $data['vehicle_id'],
                'amount' => self::DEFAULT_FINE_AMOUNT,
                'status' => 'neplacena',
                'issued_at' => now(),
            ]);
        }

        ActivityLogger::log("Izvršena kontrola vozila (rezultat: {$data['result']})");

        return response()->json($control->load(['vehicle:id,plate,brand,model', 'fine']), 201);
    }
}
