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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_tax_exempt')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_tax_exempt')->default(false)->after('sales_rep');
            });
        }

        if (Schema::hasTable('team_stores') && !Schema::hasColumn('team_stores', 'is_tax_exempt')) {
            Schema::table('team_stores', function (Blueprint $table) {
                $table->boolean('is_tax_exempt')->default(false)->after('pricing_approved');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_tax_exempt')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_tax_exempt');
            });
        }

        if (Schema::hasTable('team_stores') && Schema::hasColumn('team_stores', 'is_tax_exempt')) {
            Schema::table('team_stores', function (Blueprint $table) {
                $table->dropColumn('is_tax_exempt');
            });
        }
    }
};
