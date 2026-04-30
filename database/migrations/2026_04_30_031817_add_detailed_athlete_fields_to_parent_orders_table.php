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
            $table->string('athlete_first_name')->nullable()->after('team_store_id');
            $table->string('athlete_last_name')->nullable()->after('athlete_first_name');
            $table->string('jersey_name')->nullable()->after('gender');
            $table->string('jersey_number')->nullable()->after('jersey_name');
            $table->string('backpack_name')->nullable()->after('jersey_number');
            $table->string('guardian_first_name')->nullable()->after('backpack_name');
            $table->string('guardian_last_name')->nullable()->after('guardian_first_name');
            $table->string('guardian_phone')->nullable()->after('guardian_last_name');
            $table->string('guardian_email')->nullable()->after('guardian_phone');
        });

        // Migrate existing athlete_name data
        $orders = \Illuminate\Support\Facades\DB::table('parent_orders')->get();
        foreach ($orders as $order) {
            if (!empty($order->athlete_name)) {
                $parts = explode(' ', trim($order->athlete_name), 2);
                $firstName = $parts[0];
                $lastName = $parts[1] ?? '';
                \Illuminate\Support\Facades\DB::table('parent_orders')
                    ->where('id', $order->id)
                    ->update([
                        'athlete_first_name' => $firstName,
                        'athlete_last_name' => $lastName,
                    ]);
            }
        }

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropColumn('athlete_name');
            $table->string('athlete_first_name')->nullable(false)->change();
            $table->string('athlete_last_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_orders', function (Blueprint $table) {
            $table->string('athlete_name')->nullable()->after('team_store_id');
        });

        $orders = \Illuminate\Support\Facades\DB::table('parent_orders')->get();
        foreach ($orders as $order) {
            $fullName = trim($order->athlete_first_name . ' ' . $order->athlete_last_name);
            \Illuminate\Support\Facades\DB::table('parent_orders')
                ->where('id', $order->id)
                ->update([
                    'athlete_name' => $fullName ?: 'Unknown Athlete',
                ]);
        }

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropColumn([
                'athlete_first_name',
                'athlete_last_name',
                'jersey_name',
                'jersey_number',
                'backpack_name',
                'guardian_first_name',
                'guardian_last_name',
                'guardian_phone',
                'guardian_email'
            ]);
        });
    }
};
