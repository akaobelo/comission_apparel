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
        Schema::table('store_rosters', function (Blueprint $table) {
            $table->string('parent_phone')->nullable()->after('parent_email');
            $table->string('parent_email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_rosters', function (Blueprint $table) {
            $table->dropColumn('parent_phone');
            $table->string('parent_email')->nullable(false)->change();
        });
    }
};
