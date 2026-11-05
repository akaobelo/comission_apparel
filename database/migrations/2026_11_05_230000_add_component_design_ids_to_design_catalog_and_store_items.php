<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('design_catalog') && !Schema::hasColumn('design_catalog', 'component_design_ids')) {
            Schema::table('design_catalog', function (Blueprint $table) {
                $table->json('component_design_ids')->nullable()->after('types');
            });
        }

        if (Schema::hasTable('store_items') && !Schema::hasColumn('store_items', 'component_design_ids')) {
            Schema::table('store_items', function (Blueprint $table) {
                $table->json('component_design_ids')->nullable()->after('types');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('design_catalog') && Schema::hasColumn('design_catalog', 'component_design_ids')) {
            Schema::table('design_catalog', function (Blueprint $table) {
                $table->dropColumn('component_design_ids');
            });
        }

        if (Schema::hasTable('store_items') && Schema::hasColumn('store_items', 'component_design_ids')) {
            Schema::table('store_items', function (Blueprint $table) {
                $table->dropColumn('component_design_ids');
            });
        }
    }
};
