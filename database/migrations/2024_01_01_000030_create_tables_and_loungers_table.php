<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('table_number');   // display: "T1", "12" etc.
            $table->string('table_code')->unique(); // URL slug: "masa-1", "teras-3"
            $table->unsignedTinyInteger('seats_min')->default(1);
            $table->unsignedTinyInteger('seats_max')->default(4);
            $table->enum('shape', ['round', 'square', 'rectangle'])->default('square');
            // Visual plan position
            $table->unsignedSmallInteger('pos_x')->default(0);
            $table->unsignedSmallInteger('pos_y')->default(0);
            $table->unsignedSmallInteger('width_px')->default(60);
            $table->unsignedSmallInteger('height_px')->default(60);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_reservable')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('loungers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('lounger_number');
            $table->string('lounger_code')->unique();
            $table->enum('type', ['sunbed', 'cabana', 'vip_cabana'])->default('sunbed');
            $table->unsignedSmallInteger('pos_x')->default(0);
            $table->unsignedSmallInteger('pos_y')->default(0);
            $table->decimal('daily_rate', 8, 2)->default(0);
            $table->boolean('includes_umbrella')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_reservable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loungers');
        Schema::dropIfExists('tables');
    }
};
