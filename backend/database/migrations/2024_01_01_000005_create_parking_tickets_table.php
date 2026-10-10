<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("parking_tickets", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("vehicle_id")->constrained("vehicles")->cascadeOnDelete();
            $table->foreignId("zone_id")->constrained("zones")->cascadeOnDelete();
            $table->enum("channel", ["app", "sms", "automat"])->default("app");
            $table->dateTime("start_time");
            $table->dateTime("end_time");
            $table->decimal("price", 10, 2);
            $table->enum("status", ["aktivna", "istekla", "otkazana"])->default("aktivna");
            $table->string("qr_code")->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("parking_tickets");
    }
};
