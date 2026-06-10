<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('gallery_photos', 'alt_tr')) {
                $table->string('alt_tr')->nullable()->after('alt');
            }
            if (!Schema::hasColumn('gallery_photos', 'alt_en')) {
                $table->string('alt_en')->nullable()->after('alt_tr');
            }
            if (!Schema::hasColumn('gallery_photos', 'alt_de')) {
                $table->string('alt_de')->nullable()->after('alt_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->dropColumn(['alt_tr', 'alt_en', 'alt_de']);
        });
    }
};
