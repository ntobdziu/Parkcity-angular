<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("vehicles", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->string("plate", 20)->unique();
            $table->string("brand", 50);
            $table->string("model", 50);
            $table->enum("type", ["putnicko", "dostavno", "motocikl"])->default("putnicko");
            $table->integer("year")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("vehicles");
    }
};
