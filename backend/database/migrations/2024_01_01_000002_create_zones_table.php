<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("zones", function (Blueprint $table) {
            $table->id();
            $table->string("name", 100);
            $table->string("location");
            $table->decimal("tariff_per_hour", 10, 2)->default(0);
            $table->integer("max_duration_minutes")->nullable();
            $table->time("work_hours_from");
            $table->time("work_hours_to");
            $table->enum("type", ["standardna", "rezidentska", "invalidska"])->default("standardna");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("zones");
    }
};
