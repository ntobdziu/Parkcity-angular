<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** POST /api/login  ->  { token, user } */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Pogrešan email ili lozinka.']]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => $user->status === 'pending'
                    ? 'Vaš nalog čeka odobrenje administratora.'
                    : 'Vaš nalog je deaktiviran. Obratite se administratoru.',
            ], 403);
        }

        $token = $user->createToken('angular')->plainTextToken;
        ActivityLogger::log('Prijava na sistem', $user);

        return response()->json(['token' => $token, 'user' => $this->payload($user)]);
    }

    /** POST /api/register  ->  novi nalog gradjanina (odmah aktivan) */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => 'gradjanin',
            'status' => 'active',
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('angular')->plainTextToken;
        ActivityLogger::log('Registracija novog naloga (građanin)', $user);

        return response()->json(['token' => $token, 'user' => $this->payload($user)], 201);
    }

    /** POST /api/logout  ->  brise token kojim je zahtev poslat */
    public function logout(Request $request): JsonResponse
    {
        ActivityLogger::log('Odjava sa sistema');
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Odjavljeni ste.']);
    }

    /** GET /api/me */
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->payload($request->user()));
    }

    private function payload(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'role', 'status', 'phone']);
    }
}
