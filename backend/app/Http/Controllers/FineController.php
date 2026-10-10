<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FineController extends Controller
{
    /**
     * GET /api/fines?status=neplacena
     * admin: sve kazne | kontrolor: kazne iz svojih kontrola | gradjanin: kazne za svoja vozila
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', 'in:neplacena,placena,u_postupku']]);

        $user = $request->user();

        $query = Fine::with([
            'vehicle:id,user_id,plate,brand,model',
            'vehicle.owner:id,name',
            'control:id,controller_id,location,timestamp,result',
        ])->latest('issued_at');

        if ($user->role === 'kontrolor') {
            $query->whereHas('control', fn ($q) => $q->where('controller_id', $user->id));
        } elseif ($user->role === 'gradjanin') {
            $query->whereHas('vehicle', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json($query->paginate(10));
    }

    /** POST /api/fines/{fine}/pay  { payment_method }  ->  naplata kazne (kontrolor, admin) */
    public function pay(Request $request, Fine $fine): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'kontrolor') {
            abort_unless($fine->control->controller_id === $user->id, 403, 'Ova kazna nije iz vaše kontrole.');
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:gotovina,kartica,online'],
        ]);

        if ($fine->status === 'placena') {
            throw ValidationException::withMessages(['payment_method' => ['Kazna je već plaćena.']]);
        }

        $fine->update([
            'status' => 'placena',
            'payment_method' => $data['payment_method'],
            'paid_at' => now(),
        ]);

        ActivityLogger::log("Naplaćena kazna #{$fine->id} ({$data['payment_method']})");

        return response()->json($fine->load([
            'vehicle:id,user_id,plate,brand,model',
            'vehicle.owner:id,name',
            'control:id,controller_id,location,timestamp,result',
        ]));
    }
}
