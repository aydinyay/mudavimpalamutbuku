<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();            // URL'deki gizli token
            $table->string('guest_name')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating')->nullable();        // 1-5, null=henüz yanıtlanmadı
            $table->text('feedback')->nullable();             // Düşük puana ek yorum
            $table->enum('outcome', ['sent_to_google', 'sent_to_admin', 'pending'])->default('pending');
            $table->timestamp('sent_at')->nullable();         // Anket linki ne zaman gönderildi
            $table->timestamp('responded_at')->nullable();
            $table->boolean('admin_read')->default(false);
            $table->timestamps();
        });

        Schema::create('instagram_posts', function (Blueprint $table) {
            $table->id();
            $table->string('instagram_id')->unique();
            $table->string('media_type');                     // IMAGE, VIDEO, CAROUSEL_ALBUM
            $table->string('media_url');
            $table->string('thumbnail_url')->nullable();      // Video thumbnail
            $table->text('caption')->nullable();
            $table->string('permalink');
            $table->timestamp('posted_at');
            $table->boolean('is_visible')->default(true);     // Admin toggle
            $table->timestamps();
            $table->index(['is_visible', 'posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instagram_posts');
        Schema::dropIfExists('survey_responses');
    }
};
