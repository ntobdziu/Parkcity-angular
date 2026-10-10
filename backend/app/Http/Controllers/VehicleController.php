<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /** GET /api/vehicles  ->  vozila prijavljenog gradjanina */
    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::where('user_id', $request->user()->id)->orderBy('plate')->get();

        return response()->json($vehicles);
    }

    /** POST /api/vehicles  ->  dodavanje vozila (potrebno da bi gradjanin mogao da kupi kartu) */
    public function store(Request $request): JsonResponse
    {
        $request->merge(['plate' => strtoupper(trim((string) $request->input('plate')))]);

        $data = $request->validate([
            'plate' => ['required', 'string', 'max:20', 'unique:vehicles,plate'],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:putnicko,dostavno,motocikl'],
            'year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
        ]);

        $vehicle = Vehicle::create($data + ['user_id' => $request->user()->id]);
        ActivityLogger::log("Registracija vozila {$vehicle->plate}");

        return response()->json($vehicle, 201);
    }
}
