<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        "name", "email", "password", "role", "status", "phone",
    ];

    protected $hidden = [
        "password", "remember_token",
    ];

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    public function isAdmin(): bool { return $this->role === "admin"; }
    public function isOperater(): bool { return $this->role === "operater"; }
    public function isKontrolor(): bool { return $this->role === "kontrolor"; }
    public function isGradjanin(): bool { return $this->role === "gradjanin"; }
    public function isActive(): bool { return $this->status === "active"; }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function parkingTickets()
    {
        return $this->hasMany(ParkingTicket::class);
    }

    public function residentPermits()
    {
        return $this->hasMany(ResidentPermit::class);
    }

    public function controlsPerformed()
    {
        return $this->hasMany(Control::class, "controller_id");
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
