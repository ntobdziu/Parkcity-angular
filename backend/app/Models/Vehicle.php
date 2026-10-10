<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "plate", "brand", "model", "type", "year"];

    public function owner()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function parkingTickets()
    {
        return $this->hasMany(ParkingTicket::class);
    }

    public function controls()
    {
        return $this->hasMany(Control::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function activeTicket()
    {
        return $this->hasOne(ParkingTicket::class)
            ->where("status", "aktivna")
            ->where("start_time", "<=", now())
            ->where("end_time", ">=", now())
            ->latest("end_time");
    }
}
