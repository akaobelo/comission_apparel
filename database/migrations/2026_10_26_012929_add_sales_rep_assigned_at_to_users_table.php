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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'sales_rep_assigned_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('sales_rep_assigned_at')->nullable()->after('sales_rep_id');
            });

            // Populate existing assignments with their timestamp or updated_at
            \DB::table('users')
                ->whereNotNull('sales_rep_id')
                ->whereNull('sales_rep_assigned_at')
                ->update(['sales_rep_assigned_at' => \DB::raw('COALESCE(updated_at, created_at)')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'sales_rep_assigned_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('sales_rep_assigned_at');
            });
        }
    }
};
