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
        Schema::create('portfolio_domains', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portfolio_id')
                ->constrained('portfolios')
                ->cascadeOnDelete();

            $table->string('domain', 255);
            $table->string('type', 20)->default('subdomain');
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_verified')->default(true);
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->unique('domain', 'portfolio_domains_domain_unique');
            $table->index(['portfolio_id', 'is_primary'], 'portfolio_domains_portfolio_primary_index');
            $table->index(['domain', 'is_verified'], 'portfolio_domains_domain_verified_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_domains');
    }
};
