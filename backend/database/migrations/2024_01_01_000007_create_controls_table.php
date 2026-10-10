<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("controls", function (Blueprint $table) {
            $table->id();
            $table->foreignId("controller_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("vehicle_id")->constrained("vehicles")->cascadeOnDelete();
            $table->string("location");
            $table->dateTime("timestamp");
            $table->enum("result", ["ispravno", "neplaceno", "isteklo"]);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("controls");
    }
};
