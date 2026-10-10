<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Upotreba u rutama: ->middleware('role:kontrolor,admin')
     * Ako uloga prijavljenog korisnika nije na listi -> 403 (JSON).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Nemate pravo pristupa ovoj funkciji.'], 403);
        }

        return $next($request);
    }
}
