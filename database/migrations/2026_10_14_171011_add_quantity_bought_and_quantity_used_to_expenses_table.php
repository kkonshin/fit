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
        if (! Schema::hasColumn('expenses', 'quantity_bought')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->integer('quantity_bought')->nullable();
            });
        }

        if (! Schema::hasColumn('expenses', 'quantity_used')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->integer('quantity_used')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('expenses', 'quantity_bought')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('quantity_bought');
            });
        }

        if (Schema::hasColumn('expenses', 'quantity_used')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('quantity_used');
            });
        }
    }
};
