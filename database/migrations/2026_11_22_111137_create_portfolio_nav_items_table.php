
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_nav_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portfolio_id')
                ->constrained('portfolios')
                ->cascadeOnDelete();

            $table->string('label', 80);

            $table->string('section_key', 80)->nullable();

            $table->string('url', 255)->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            // Efficiently retrieve ordered navigation items
            $table->index(
                ['portfolio_id', 'sort_order'],
                'portfolio_nav_items_order_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_nav_items');
    }
};
