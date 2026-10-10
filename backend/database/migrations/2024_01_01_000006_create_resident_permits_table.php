<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("resident_permits", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("zone_id")->constrained("zones")->cascadeOnDelete();
            $table->string("plate", 20);
            $table->date("valid_from");
            $table->date("valid_until");
            $table->enum("status", ["na_cekanju", "aktivna", "istekla", "odbijena"])->default("na_cekanju");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("resident_permits");
    }
};
