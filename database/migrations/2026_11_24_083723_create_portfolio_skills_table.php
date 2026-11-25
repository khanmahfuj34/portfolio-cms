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
        Schema::create('portfolio_skills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portfolio_id')
                ->constrained('portfolios')
                ->cascadeOnDelete();

            $table->string('name', 100);

            $table->timestamps();

            $table->unique(
                ['portfolio_id', 'name'],
                'portfolio_skills_portfolio_name_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_skills');
    }
};
