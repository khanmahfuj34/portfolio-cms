<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portfolio_id')
                ->constrained('portfolios')
                ->cascadeOnDelete();

            $table->string('title', 150);
            $table->text('description')->nullable();

            $table->string('thumbnail', 255)->nullable();

            $table->string('live_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();

            $table->json('technologies')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_visible')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(
                ['portfolio_id', 'is_visible', 'sort_order'],
                'portfolio_projects_visibility_order_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_projects');
    }
};
