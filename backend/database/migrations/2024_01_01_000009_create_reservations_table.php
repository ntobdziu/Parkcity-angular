<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("reservations", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("spot_id")->constrained("parking_spots")->cascadeOnDelete();
            $table->dateTime("start_time");
            $table->dateTime("end_time");
            $table->enum("status", ["aktivna", "zavrsena", "otkazana"])->default("aktivna");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("reservations");
    }
};
