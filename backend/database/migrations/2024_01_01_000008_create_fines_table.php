<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("fines", function (Blueprint $table) {
            $table->id();
            $table->foreignId("control_id")->constrained("controls")->cascadeOnDelete();
            $table->foreignId("vehicle_id")->constrained("vehicles")->cascadeOnDelete();
            $table->decimal("amount", 10, 2);
            $table->enum("status", ["neplacena", "placena", "u_postupku"])->default("neplacena");
            $table->enum("payment_method", ["gotovina", "kartica", "online"])->nullable();
            $table->dateTime("issued_at");
            $table->dateTime("paid_at")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("fines");
    }
};
