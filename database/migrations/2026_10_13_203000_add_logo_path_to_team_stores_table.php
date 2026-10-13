<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('team_stores') && !Schema::hasColumn('team_stores', 'logo_path')) {
            Schema::table('team_stores', function (Blueprint $table) {
                $table->string('logo_path')->nullable()->after('slug');
            });

            // Backfill existing stores with their user's logo_path
            $stores = DB::table('team_stores')
                ->join('users', 'team_stores.user_id', '=', 'users.id')
                ->whereNotNull('users.logo_path')
                ->select('team_stores.id', 'users.logo_path')
                ->get();

            foreach ($stores as $store) {
                DB::table('team_stores')
                    ->where('id', $store->id)
                    ->update(['logo_path' => $store->logo_path]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('team_stores') && Schema::hasColumn('team_stores', 'logo_path')) {
            Schema::table('team_stores', function (Blueprint $table) {
                $table->dropColumn('logo_path');
            });
        }
    }
};
