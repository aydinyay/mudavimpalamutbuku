<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('qr_uuid')->unique();
            $table->enum('target_type', ['table', 'lounger', 'menu_general'])->default('table');
            $table->unsignedBigInteger('target_id')->nullable()->index();
            $table->string('url');
            $table->string('image_path')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->unsignedBigInteger('scan_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
