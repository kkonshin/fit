<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('body', function (Blueprint $table): void {
            $table->index('created_at', 'body_created_at_index');
        });

        Schema::table('exercises', function (Blueprint $table): void {
            $table->index('created_at', 'exercises_created_at_index');
            $table->index(
                ['name', 'created_at'],
                'exercises_name_created_at_index',
            );
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->index('paid_at', 'expenses_paid_at_index');
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->index('body_id', 'images_body_id_index');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('body', function (Blueprint $table): void {
            $table->dropIndex('body_created_at_index');
        });

        Schema::table('exercises', function (Blueprint $table): void {
            $table->dropIndex('exercises_created_at_index');
            $table->dropIndex('exercises_name_created_at_index');
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropIndex('expenses_paid_at_index');
        });

        Schema::table('images', function (Blueprint $table): void {
            $table->dropIndex('images_body_id_index');
        });
    }
};
