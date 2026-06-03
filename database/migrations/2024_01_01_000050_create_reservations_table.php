<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('reservation_uuid')->unique();
            $table->enum('type', ['table', 'lounger', 'combined'])->default('table');
            // Guest info
            $table->string('guest_name');
            $table->string('guest_phone');
            $table->string('guest_email')->nullable();
            $table->string('guest_locale', 5)->default('tr');
            $table->unsignedTinyInteger('guest_count')->default(2);
            $table->text('special_requests')->nullable();
            // What was booked
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->foreignId('lounger_id')->nullable()->constrained('loungers')->nullOnDelete();
            // When
            $table->date('reservation_date');
            $table->time('arrival_time')->nullable();
            $table->boolean('all_day')->default(false); // for lounger full-day bookings
            // Status
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'no_show', 'completed'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            // Source
            $table->enum('source', ['website', 'admin', 'phone', 'walkin', 'boat'])->default('website');
            $table->text('internal_notes')->nullable();
            // Guest profile FK reserved for CRM module
            $table->unsignedBigInteger('guest_profile_id')->nullable()->index();
            $table->timestamps();
            $table->index(['reservation_date', 'status']);
        });

        Schema::create('reservation_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->enum('type', ['sms', 'whatsapp'])->default('sms');
            $table->text('message_body');
            $table->timestamp('sent_at')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_reminders');
        Schema::dropIfExists('reservations');
    }
};
