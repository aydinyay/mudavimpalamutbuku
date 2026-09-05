<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('review_photos', 'reviewer_photo')) return;
        Schema::table('review_photos', function (Blueprint $table) {
            $table->string('reviewer_photo')->nullable()->after('reviewer_name');
        });
    }

    public function down(): void
    {
        Schema::table('review_photos', function (Blueprint $table) {
            $table->dropColumn('reviewer_photo');
        });
    }
};
