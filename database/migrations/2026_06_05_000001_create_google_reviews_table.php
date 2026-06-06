<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('google_review_id')->unique();
            $table->string('author_name');
            $table->string('author_photo')->nullable();
            $table->tinyInteger('rating');                    // 1-5
            $table->text('comment')->nullable();
            $table->timestamp('review_time');
            $table->enum('display_status', ['visible', 'hidden', 'pending'])->default('pending');
            // visible=4-5 yıldız otomatik yayın, hidden=1-3 admin gizledi, pending=henüz işlenmedi
            $table->boolean('admin_read')->default(false);    // 1-3 yıldız admin okudumu
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index(['rating', 'display_status']);
        });

        Schema::create('review_sync_log', function (Blueprint $table) {
            $table->id();
            $table->string('source');                         // 'google', 'instagram'
            $table->integer('fetched')->default(0);
            $table->integer('new')->default(0);
            $table->string('status');                         // 'ok', 'error'
            $table->text('error')->nullable();
            $table->timestamp('synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_sync_log');
        Schema::dropIfExists('google_reviews');
    }
};
