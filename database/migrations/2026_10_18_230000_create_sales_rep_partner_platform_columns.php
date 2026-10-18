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
        // 1. Add fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_rep_id')->nullable()->after('sales_rep');
            $table->foreign('sales_rep_id')->references('id')->on('users')->nullOnDelete();
            
            $table->string('partner_category')->nullable()->after('sales_rep_id'); // independent_sales, authorized_business, established_business
            $table->decimal('commission_rate', 5, 2)->default(50.00)->after('partner_category'); // 30.00, 40.00, 50.00, or custom
            $table->string('referral_slug')->nullable()->unique()->after('commission_rate');
        });

        // 2. Add sales_rep_id to team_stores table
        Schema::table('team_stores', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_rep_id')->nullable()->after('user_id');
            $table->foreign('sales_rep_id')->references('id')->on('users')->nullOnDelete();
        });

        // 3. Add financial & commission columns to parent_orders table
        Schema::table('parent_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_rep_id')->nullable()->after('user_id');
            $table->foreign('sales_rep_id')->references('id')->on('users')->nullOnDelete();

            $table->decimal('manufacturing_cost', 10, 2)->default(0.00)->after('total_paid');
            $table->decimal('tariff_amount', 10, 2)->default(0.00)->after('manufacturing_cost');
            $table->decimal('fee_deduction', 10, 2)->default(0.00)->after('tariff_amount');
            $table->decimal('fundraising_amount', 10, 2)->default(0.00)->after('fee_deduction');
            $table->decimal('net_margin', 10, 2)->default(0.00)->after('fundraising_amount');

            $table->decimal('applied_rep_commission_rate', 5, 2)->nullable()->after('net_margin');
            $table->decimal('rep_commission_earned', 10, 2)->default(0.00)->after('applied_rep_commission_rate');
            $table->string('commission_status')->default('pending')->after('rep_commission_earned'); // pending, cleared, paid
            $table->timestamp('paid_to_rep_at')->nullable()->after('commission_status');
            $table->text('financial_notes')->nullable()->after('paid_to_rep_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropForeign(['sales_rep_id']);
            $table->dropColumn([
                'sales_rep_id',
                'manufacturing_cost',
                'tariff_amount',
                'fee_deduction',
                'fundraising_amount',
                'net_margin',
                'applied_rep_commission_rate',
                'rep_commission_earned',
                'commission_status',
                'paid_to_rep_at',
                'financial_notes',
            ]);
        });

        Schema::table('team_stores', function (Blueprint $table) {
            $table->dropForeign(['sales_rep_id']);
            $table->dropColumn('sales_rep_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sales_rep_id']);
            $table->dropColumn([
                'sales_rep_id',
                'partner_category',
                'commission_rate',
                'referral_slug',
            ]);
        });
    }
};
