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
        Schema::create('portfolio_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portfolio_id')
                ->unique()
                ->constrained('portfolios')
                ->cascadeOnDelete();

            $table->string('full_name', 150);

            $table->string('professional_title', 150)
                ->nullable();

            $table->string('email', 255)
                ->nullable();

            $table->string('location', 150)
                ->nullable();

            $table->text('bio')
                ->nullable();

            $table->string('profile_photo_path', 255)
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_profiles');
    }
};
