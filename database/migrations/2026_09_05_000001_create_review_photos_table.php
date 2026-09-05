<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('review_photos')) return;
        Schema::create('review_photos', function (Blueprint $table) {
            $table->id();
            $table->string('reviewer_name');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->json('filenames');
            $table->timestamp('review_time')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_photos');
    }
};
