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
        Schema::table('portfolios', function (Blueprint $table) {
            $table->foreignId('theme_id')
                ->nullable()
                ->after('user_id')
                ->constrained('themes')
                ->restrictOnDelete();

            $table->index('theme_id', 'portfolios_theme_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropForeign(['theme_id']);
            $table->dropIndex('portfolios_theme_id_index');
            $table->dropColumn('theme_id');
        });
    }
};
