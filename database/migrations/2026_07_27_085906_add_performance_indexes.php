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
        Schema::table('team_stores', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('is_archived');
            $table->index('pricing_approved');
            $table->index('sort_order');
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->index('team_store_id');
            $table->index('design_catalog_id');
            $table->index('sort_order');
        });

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->index('team_store_id');
            $table->index('batch_id');
            $table->index('is_archived');
        });

        Schema::table('design_catalog', function (Blueprint $table) {
            $table->index('design_collection_id');
            $table->index('sport');
            $table->index('type');
            $table->index('sort_order');
        });

        Schema::table('store_rosters', function (Blueprint $table) {
            $table->index('team_store_id');
        });

        Schema::table('design_collections', function (Blueprint $table) {
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_collections', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
        });

        Schema::table('store_rosters', function (Blueprint $table) {
            $table->dropIndex(['team_store_id']);
        });

        Schema::table('design_catalog', function (Blueprint $table) {
            $table->dropIndex(['design_collection_id']);
            $table->dropIndex(['sport']);
            $table->dropIndex(['type']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropIndex(['team_store_id']);
            $table->dropIndex(['batch_id']);
            $table->dropIndex(['is_archived']);
        });

        Schema::table('store_items', function (Blueprint $table) {
            $table->dropIndex(['team_store_id']);
            $table->dropIndex(['design_catalog_id']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('team_stores', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_archived']);
            $table->dropIndex(['pricing_approved']);
            $table->dropIndex(['sort_order']);
        });
    }
};
