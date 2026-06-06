<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->text('spotify_access_token')->nullable();
            $table->text('spotify_refresh_token')->nullable();
            $table->timestamp('spotify_token_expires_at')->nullable();
            $table->boolean('spotify_enabled')->default(false);
            $table->boolean('spotify_widget_on_website')->default(true);
            $table->boolean('spotify_widget_on_menu')->default(true);
            $table->string('spotify_fallback_playlist_url')->nullable();
            $table->string('spotify_fallback_playlist_name')->nullable();
        });

        Schema::create('spotify_cache', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['now_playing', 'recent', 'queue'])->unique();
            $table->json('data')->nullable();
            $table->boolean('is_playing')->default(false);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotify_cache');
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'spotify_access_token', 'spotify_refresh_token',
                'spotify_token_expires_at', 'spotify_enabled',
                'spotify_widget_on_website', 'spotify_widget_on_menu',
                'spotify_fallback_playlist_url', 'spotify_fallback_playlist_name',
            ]);
        });
    }
};
