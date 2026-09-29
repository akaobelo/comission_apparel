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
        Schema::table('team_stores', function (Blueprint $table) {
            $table->string('payment_mode')->default('in_house')->after('status'); // 'in_house' or 'online'
        });

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->string('payment_status')->default('not_applicable')->after('status'); // 'not_applicable', 'pending', 'paid', 'failed'
            $table->decimal('subtotal', 10, 2)->default(0.00)->after('total_retail_price');
            $table->decimal('tax_amount', 10, 2)->default(0.00)->after('subtotal');
            $table->decimal('fee_amount', 10, 2)->default(0.00)->after('tax_amount');
            $table->decimal('shipping_amount', 10, 2)->default(0.00)->after('fee_amount');
            $table->string('shipping_method')->default('coach_batch')->after('shipping_amount');
            $table->decimal('total_paid', 10, 2)->default(0.00)->after('shipping_method');
            $table->string('stripe_session_id')->nullable()->after('total_paid');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            $table->timestamp('paid_at')->nullable()->after('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_stores', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'subtotal',
                'tax_amount',
                'fee_amount',
                'shipping_amount',
                'shipping_method',
                'total_paid',
                'stripe_session_id',
                'stripe_payment_intent_id',
                'paid_at',
            ]);
        });
    }
};
