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
            $table->integer('sort_order')->default(0)->after('wholesale_price');
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('retail_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_items', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('design_catalog', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
