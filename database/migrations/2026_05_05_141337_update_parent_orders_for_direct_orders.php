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
            $table->foreignId('team_store_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('batch_id')->nullable();
            $table->boolean('is_archived')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_orders', function (Blueprint $table) {
            $table->foreignId('team_store_id')->nullable(false)->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'batch_id', 'is_archived']);
        });
    }
};
