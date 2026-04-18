<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table: assigns specific catalog designs to specific coaches
        Schema::create('design_catalog_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_catalog_id')->constrained('design_catalog')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['design_catalog_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_catalog_user');
    }
};
