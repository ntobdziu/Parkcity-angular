<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Upisuje radnju u activity_logs. Ako korisnik nije prosledjen, uzima se trenutno prijavljeni.
     */
    public static function log(string $action, ?User $user = null): void
    {
        $userId = $user?->id ?? Auth::id();

        if (! $userId) {
            return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'timestamp' => now(),
        ]);
    }
}
