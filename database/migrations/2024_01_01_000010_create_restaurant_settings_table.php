<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name_tr')->default('Müdavim Şef Restaurant');
            $table->string('name_en')->default('Müdavim Şef Restaurant');
            $table->string('name_de')->default('Müdavim Şef Restaurant');
            $table->text('tagline_tr')->nullable();
            $table->text('tagline_en')->nullable();
            $table->text('tagline_de')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->text('address_tr')->nullable();
            $table->text('address_en')->nullable();
            $table->text('address_de')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->time('open_time')->default('09:00:00');
            $table->time('close_time')->default('02:00:00');
            // Season management
            $table->string('season_open_date', 5)->default('05-01');  // MM-DD
            $table->string('season_close_date', 5)->default('10-31'); // MM-DD
            $table->boolean('is_open_override')->nullable();           // null = use season, true/false = force
            // Reservation settings
            $table->unsignedSmallInteger('max_advance_days')->default(30);
            // SMS
            $table->string('sms_provider')->nullable();
            $table->string('sms_api_key')->nullable();
            $table->string('sms_sender_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_settings');
    }
};
