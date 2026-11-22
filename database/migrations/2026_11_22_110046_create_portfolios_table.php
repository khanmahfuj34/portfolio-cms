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
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();

            // Portfolio owner (Super Admin)
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Basic portfolio information
            $table->string('name', 150);
            $table->string('slug', 150);
            $table->text('description')->nullable();

            // Publication status
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();

            // Timestamps
            $table->timestamps();

            // Unique slug per admin
            $table->unique(
                ['user_id', 'slug'],
                'portfolios_user_slug_unique'
            );

            // Admin portfolio filtering
            $table->index(
                ['user_id', 'status'],
                'portfolios_user_status_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
