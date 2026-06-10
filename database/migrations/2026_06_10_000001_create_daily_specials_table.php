<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('daily_specials')) return;
        Schema::create('daily_specials', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('title_tr')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_de')->nullable();
            $table->text('description_tr')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_de')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_specials');
    }
};
