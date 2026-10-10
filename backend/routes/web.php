<?php

use Illuminate\Support\Facades\Route;

// Backend je samo API. Ovo je provera da server radi: http://localhost:8000
Route::get('/', fn () => response()->json([
    'app' => 'ParkCity API',
    'status' => 'ok',
    'docs' => 'Svi endpointi su pod /api (vidi routes/api.php)',
]));
