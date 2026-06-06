<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique();
            $table->string('ip', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->default('desktop');
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('referrer_source', 30)->default('direct');
            $table->string('referrer_url', 500)->nullable();
            $table->string('landing_page', 500)->nullable();
            $table->unsignedSmallInteger('page_count')->default(1);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('first_seen_at');
            $table->index(['country_code', 'city']);
            $table->index('device_type');
            $table->index('referrer_source');
        });

        Schema::create('analytics_pageviews', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->index();
            $table->string('page_path', 500);
            $table->string('page_title', 200)->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('page_path');
            $table->index('created_at');
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->index();
            $table->string('event_type', 50);
            $table->json('event_data')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_pageviews');
        Schema::dropIfExists('analytics_sessions');
    }
};
