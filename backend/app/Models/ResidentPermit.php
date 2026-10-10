<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentPermit extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "zone_id", "plate", "valid_from", "valid_until", "status"];

    protected function casts(): array
    {
        return [
            "valid_from" => "date",
            "valid_until" => "date",
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
