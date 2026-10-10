<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", "location", "tariff_per_hour", "max_duration_minutes",
        "work_hours_from", "work_hours_to", "type",
    ];

    protected function casts(): array
    {
        return [
            "tariff_per_hour" => "float",
            "max_duration_minutes" => "integer",
        ];
    }

    public function spots()
    {
        return $this->hasMany(ParkingSpot::class);
    }

    public function parkingTickets()
    {
        return $this->hasMany(ParkingTicket::class);
    }

    public function residentPermits()
    {
        return $this->hasMany(ResidentPermit::class);
    }
}
