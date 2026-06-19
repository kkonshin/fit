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
        Schema::create('body', function (Blueprint $table) {
            $table->id();
            $table->float('weight', 2)->nullable();
            $table->integer('pulse')->nullable();
            $table->integer('neck')->nullable();
            $table->integer('chest')->nullable();
            $table->integer('waist')->nullable();
            $table->integer('abdomen')->nullable();
            $table->integer('bicep')->nullable();
            $table->integer('wrist')->nullable();
            $table->integer('hips')->nullable();
            $table->integer('hip')->nullable();
            $table->integer('shin')->nullable();
            $table->integer('ankle')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('body');
    }
};
