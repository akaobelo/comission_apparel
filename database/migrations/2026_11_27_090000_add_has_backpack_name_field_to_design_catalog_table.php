<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('design_catalog', 'has_backpack_name_field')) {
            Schema::table('design_catalog', function (Blueprint $table) {
                $table->boolean('has_backpack_name_field')->default(false)->after('has_number_field');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('design_catalog', 'has_backpack_name_field')) {
            Schema::table('design_catalog', function (Blueprint $table) {
                $table->dropColumn('has_backpack_name_field');
            });
        }
    }
};
