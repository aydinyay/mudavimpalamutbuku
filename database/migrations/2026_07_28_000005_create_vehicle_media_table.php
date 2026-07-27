<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('vehicle_expense_id')->nullable()->constrained('vehicle_expenses')->cascadeOnDelete();
            $table->foreignId('vehicle_issue_id')->nullable()->constrained('vehicle_issues')->cascadeOnDelete();
            $table->enum('tur', ['foto', 'video']);
            $table->enum('role', ['genel', 'bolum', 'hasar_once', 'hasar_sonra', 'parca', 'ruhsat', 'sigorta_police', 'muayene_belgesi', 'fatura', 'diger']);
            $table->string('dosya_yolu');
            $table->string('mime_type', 100);
            $table->string('caption')->nullable();
            $table->timestamps();
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE vehicle_media ADD CONSTRAINT vehicle_media_one_parent CHECK (NOT (vehicle_expense_id IS NOT NULL AND vehicle_issue_id IS NOT NULL))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_media');
    }
};
