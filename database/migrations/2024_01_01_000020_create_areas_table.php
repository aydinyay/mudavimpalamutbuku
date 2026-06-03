<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_tr');
            $table->string('name_en');
            $table->string('name_de');
            $table->enum('type', ['indoor', 'terrace', 'beach', 'boat_dock'])->default('terrace');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            // Visual plan dimensions (pixels)
            $table->unsignedSmallInteger('plan_width_px')->default(800);
            $table->unsignedSmallInteger('plan_height_px')->default(600);
            $table->string('background_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
