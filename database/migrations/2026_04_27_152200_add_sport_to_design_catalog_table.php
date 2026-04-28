<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_catalog', function (Blueprint $table) {
            $table->string('sport')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('design_catalog', function (Blueprint $table) {
            $table->dropColumn('sport');
        });
    }
};
