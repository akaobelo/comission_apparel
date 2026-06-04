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
        Schema::table('sizing_charts', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('image_path');
            $table->string('image_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sizing_charts', function (Blueprint $table) {
            $table->dropColumn('image_paths');
            $table->string('image_path')->nullable(false)->change();
        });
    }
};
