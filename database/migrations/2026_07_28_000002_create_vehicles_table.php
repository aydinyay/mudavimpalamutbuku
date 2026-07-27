<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plaka');
            $table->string('plate_key')->unique();
            $table->string('marka');
            $table->string('model');
            $table->unsignedSmallInteger('model_yili');
            $table->string('sase_no');
            $table->string('motor_no');
            $table->string('renk');
            $table->string('yakit_cinsi');
            $table->foreignId('sahip_party_id')->nullable()->constrained('vehicle_parties')->restrictOnDelete();
            $table->unsignedBigInteger('guncel_km')->default(0);
            $table->unsignedBigInteger('sonraki_bakim_km')->nullable();
            $table->date('sonraki_bakim_tarihi')->nullable();
            $table->date('sonraki_muayene_tarihi')->nullable();
            $table->date('sigorta_bitis_tarihi')->nullable();
            $table->text('onceki_sahipler_notu')->nullable();
            $table->json('donanimlar')->nullable();
            $table->string('erisim_sifresi_hash');
            $table->unsignedInteger('credential_version')->default(1);
            $table->boolean('vitrin_acik')->default(false);
            $table->text('notlar')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
