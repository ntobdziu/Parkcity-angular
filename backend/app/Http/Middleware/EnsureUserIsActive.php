<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Nalog koji nije "active" (na cekanju ili deaktiviran) ne sme da koristi API,
     * cak i ako ima ranije izdat token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'active') {
            return response()->json([
                'message' => $user->status === 'pending'
                    ? 'Vaš nalog čeka odobrenje administratora.'
                    : 'Vaš nalog je deaktiviran.',
            ], 403);
        }

        return $next($request);
    }
}
