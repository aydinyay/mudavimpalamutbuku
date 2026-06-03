<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_tr');
            $table->string('name_en');
            $table->string('name_de');
            $table->string('icon_emoji', 10)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('available_for')->nullable(); // ["lunch","dinner","all_day"]
            $table->timestamps();
        });

        Schema::create('allergens', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name_tr');
            $table->string('name_en');
            $table->string('name_de');
            $table->string('icon_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('menu_categories')->cascadeOnDelete();
            $table->string('sku')->nullable()->unique();
            $table->string('image_path')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->decimal('price_eur', 8, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_seasonal')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->unsignedSmallInteger('preparation_time_minutes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unique(['menu_item_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('menu_item_allergens', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained('allergens')->cascadeOnDelete();
            $table->primary(['menu_item_id', 'allergen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_allergens');
        Schema::dropIfExists('menu_item_translations');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('allergens');
        Schema::dropIfExists('menu_categories');
    }
};
