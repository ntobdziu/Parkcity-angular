<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Control extends Model
{
    use HasFactory;

    protected $fillable = ["controller_id", "vehicle_id", "location", "timestamp", "result"];

    protected function casts(): array
    {
        return ["timestamp" => "datetime"];
    }

    public function controller()
    {
        return $this->belongsTo(User::class, "controller_id");
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fine()
    {
        return $this->hasOne(Fine::class);
    }
}
