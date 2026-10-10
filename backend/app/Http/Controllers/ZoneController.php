<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\JsonResponse;

class ZoneController extends Controller
{
    /**
     * GET /api/zones
     * Zone sa brojem ukupnih i slobodnih mesta (za izbor zone pri kupovini karte).
     */
    public function index(): JsonResponse
    {
        $zones = Zone::withCount([
            'spots as spots_total',
            'spots as spots_free' => fn ($q) => $q->where('status', 'slobodno'),
        ])->orderBy('name')->get();

        return response()->json($zones);
    }
}
