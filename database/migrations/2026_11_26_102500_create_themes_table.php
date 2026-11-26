<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('slug', 180);
            $table->string('theme_key', 100);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('version', 20)->default('1.0.0');
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique('slug', 'themes_slug_unique');
            $table->unique('theme_key', 'themes_theme_key_unique');
            $table->index('status', 'themes_status_index');
            $table->index('category', 'themes_category_index');
            $table->index('is_featured', 'themes_is_featured_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
