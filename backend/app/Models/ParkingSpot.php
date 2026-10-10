<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpot extends Model
{
    use HasFactory;

    protected $fillable = ["zone_id", "code", "status", "special_type"];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, "spot_id");
    }
}
