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
        Schema::table('design_catalog', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            $table->string('image_url')->nullable()->change();
            $table->json('types')->nullable();
            $table->json('image_paths')->nullable();
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            $table->string('image_url')->nullable()->change();
            $table->json('types')->nullable();
            $table->json('image_paths')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_catalog', function (Blueprint $table) {
            $table->dropColumn(['types', 'image_paths']);
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->dropColumn(['types', 'image_paths']);
        });
    }
};
