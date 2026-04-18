<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_stores', function (Blueprint $table) {
            // package_a, package_b, package_c, individual, or null (not yet selected)
            $table->string('package_type')->nullable()->after('status');
            $table->text('description')->nullable()->after('name');
        });

        Schema::table('store_items', function (Blueprint $table) {
            // Link store items to the admin-managed design catalog
            $table->foreignId('design_catalog_id')->nullable()->after('id')->constrained('design_catalog')->onDelete('set null');
        });

        Schema::table('parent_orders', function (Blueprint $table) {
            // Allow admin/coach to mark an order as edited
            $table->boolean('is_edited')->default(false)->after('status');
            $table->string('edited_by')->nullable()->after('is_edited'); // 'admin' or 'coach'
        });
    }

    public function down(): void
    {
        Schema::table('team_stores', function (Blueprint $table) {
            $table->dropColumn(['package_type', 'description']);
        });
        Schema::table('store_items', function (Blueprint $table) {
            $table->dropForeign(['design_catalog_id']);
            $table->dropColumn('design_catalog_id');
        });
        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropColumn(['is_edited', 'edited_by']);
        });
    }
};
