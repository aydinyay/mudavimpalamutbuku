<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('baslik');
            $table->text('aciklama')->nullable();
            $table->enum('kategori', ['bakim', 'ariza', 'kaza', 'gorev', 'diger']);
            $table->enum('durum', ['acik', 'devam_ediyor', 'tamamlandi']);
            $table->enum('oncelik', ['dusuk', 'normal', 'yuksek', 'acil'])->default('normal');
            $table->date('bildirilme_tarihi');
            $table->date('cozulme_tarihi')->nullable();
            $table->string('yapan_firma')->nullable();
            $table->unsignedBigInteger('km')->nullable();
            $table->text('notlar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_issues');
    }
};
