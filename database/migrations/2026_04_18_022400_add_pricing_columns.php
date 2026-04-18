<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_stores', function (Blueprint $table) {
            $table->boolean('pricing_approved')->default(false)->after('status');
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->decimal('wholesale_price', 8, 2)->nullable()->after('type');
            $table->decimal('retail_price', 8, 2)->nullable()->after('wholesale_price');
        });

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->decimal('total_retail_price', 8, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('team_stores', function (Blueprint $table) {
            $table->dropColumn('pricing_approved');
        });
        Schema::table('store_items', function (Blueprint $table) {
            $table->dropColumn(['wholesale_price', 'retail_price']);
        });
        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropColumn('total_retail_price');
        });
    }
};
