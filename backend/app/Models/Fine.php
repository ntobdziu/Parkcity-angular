<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        "control_id", "vehicle_id", "amount", "status",
        "payment_method", "issued_at", "paid_at",
    ];

    protected function casts(): array
    {
        return [
            "issued_at" => "datetime",
            "paid_at" => "datetime",
            "amount" => "float",
        ];
    }

    public function control()
    {
        return $this->belongsTo(Control::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
