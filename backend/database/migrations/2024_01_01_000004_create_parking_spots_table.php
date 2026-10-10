<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("parking_spots", function (Blueprint $table) {
            $table->id();
            $table->foreignId("zone_id")->constrained("zones")->cascadeOnDelete();
            $table->string("code", 20);
            $table->enum("status", ["slobodno", "zauzeto", "rezervisano", "nedostupno"])->default("slobodno");
            $table->enum("special_type", ["invalidsko", "elektro", "dostava"])->nullable();
            $table->timestamps();
            $table->unique(["zone_id", "code"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("parking_spots");
    }
};
