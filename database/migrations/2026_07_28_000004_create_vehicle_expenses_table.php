<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('vehicle_issue_id')->nullable()->constrained('vehicle_issues')->restrictOnDelete();
            $table->date('tarih');
            $table->string('aciklama');
            $table->decimal('tutar', 12, 2);
            $table->enum('kategori', ['yakit', 'bakim', 'yag_degisimi', 'lastik', 'sigorta', 'vergi', 'parca', 'iscilik', 'diger']);
            $table->foreignId('odeyen_party_id')->constrained('vehicle_parties')->restrictOnDelete();
            $table->foreignId('borclu_party_id')->nullable()->constrained('vehicle_parties')->restrictOnDelete();
            $table->enum('mahsup_durumu', ['borc_yok', 'alinacak', 'kapandi']);
            $table->dateTime('settled_at')->nullable();
            $table->unsignedBigInteger('km')->nullable();
            $table->string('parca_markasi')->nullable();
            $table->unsignedSmallInteger('uretim_yili')->nullable();
            $table->string('nereden_alindi')->nullable();
            $table->date('garanti_bitis_tarihi')->nullable();
            $table->boolean('vitrinde_goster')->default(false);
            $table->string('public_summary', 240)->nullable();
            $table->text('notlar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_expenses');
    }
};
