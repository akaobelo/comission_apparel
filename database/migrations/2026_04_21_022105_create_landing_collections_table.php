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
        Schema::create('landing_collections', function (Blueprint $table) {
            $table->id();
            $table->string('tab_name'); // e.g., 'Tackle Football'
            $table->string('title'); // e.g., 'Springfield Secondary School'
            $table->text('description'); // e.g., '4-Way stretch compression fit...'
            $table->string('image_path')->nullable(); // e.g., '/images/soccer-model.png'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_collections');
    }
};
