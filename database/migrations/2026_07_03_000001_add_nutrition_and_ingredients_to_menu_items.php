<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Yasal zorunluluk: içindekiler (31 Aralık 2026)
            $table->text('ingredients_tr')->nullable()->after('is_gluten_free');
            $table->text('ingredients_en')->nullable()->after('ingredients_tr');
            $table->text('ingredients_de')->nullable()->after('ingredients_en');

            // Et kökeni zorunluluğu
            $table->string('meat_origin', 100)->nullable()->after('ingredients_de');

            // Besin değerleri (31 Aralık 2027)
            $table->unsignedSmallInteger('serving_size_g')->nullable()->after('meat_origin');
            $table->unsignedSmallInteger('calories')->nullable()->after('serving_size_g');
            $table->decimal('protein_g', 5, 1)->nullable()->after('calories');
            $table->decimal('fat_g', 5, 1)->nullable()->after('protein_g');
            $table->decimal('carbs_g', 5, 1)->nullable()->after('fat_g');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn([
                'ingredients_tr', 'ingredients_en', 'ingredients_de',
                'meat_origin',
                'serving_size_g', 'calories', 'protein_g', 'fat_g', 'carbs_g',
            ]);
        });
    }
};
