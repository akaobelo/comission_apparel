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
        Schema::table('parent_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('parent_orders', 'wholesale_cost')) {
                $table->decimal('wholesale_cost', 10, 2)->nullable()->after('subtotal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_orders', function (Blueprint $table) {
            if (Schema::hasColumn('parent_orders', 'wholesale_cost')) {
                $table->dropColumn('wholesale_cost');
            }
        });
    }
};
