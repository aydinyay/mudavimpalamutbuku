<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('vehicle_expense_id')->nullable()->constrained('vehicle_expenses')->nullOnDelete();
            $table->string('alan_adi');
            $table->text('eski_deger')->nullable();
            $table->text('yeni_deger')->nullable();
            $table->text('aciklama')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_change_logs');
    }
};
