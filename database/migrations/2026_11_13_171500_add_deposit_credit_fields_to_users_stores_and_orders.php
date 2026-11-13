<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'deposit_amount')) {
                    $table->decimal('deposit_amount', 10, 2)->default(0.00)->after('is_tax_exempt');
                }
                if (!Schema::hasColumn('users', 'deposit_apply_to')) {
                    $table->string('deposit_apply_to', 30)->default('both')->after('deposit_amount');
                }
                if (!Schema::hasColumn('users', 'deposit_applied')) {
                    $table->boolean('deposit_applied')->default(false)->after('deposit_apply_to');
                }
                if (!Schema::hasColumn('users', 'deposit_applied_at')) {
                    $table->timestamp('deposit_applied_at')->nullable()->after('deposit_applied');
                }
                if (!Schema::hasColumn('users', 'deposit_applied_batch_id')) {
                    $table->string('deposit_applied_batch_id')->nullable()->after('deposit_applied_at');
                }
                if (!Schema::hasColumn('users', 'deposit_notes')) {
                    $table->string('deposit_notes', 500)->nullable()->after('deposit_applied_batch_id');
                }
            });
        }

        if (Schema::hasTable('team_stores')) {
            Schema::table('team_stores', function (Blueprint $table) {
                if (!Schema::hasColumn('team_stores', 'deposit_amount')) {
                    $table->decimal('deposit_amount', 10, 2)->default(0.00)->after('is_tax_exempt');
                }
                if (!Schema::hasColumn('team_stores', 'deposit_apply_to')) {
                    $table->string('deposit_apply_to', 30)->default('both')->after('deposit_amount');
                }
                if (!Schema::hasColumn('team_stores', 'deposit_applied')) {
                    $table->boolean('deposit_applied')->default(false)->after('deposit_apply_to');
                }
                if (!Schema::hasColumn('team_stores', 'deposit_applied_at')) {
                    $table->timestamp('deposit_applied_at')->nullable()->after('deposit_applied');
                }
                if (!Schema::hasColumn('team_stores', 'deposit_applied_batch_id')) {
                    $table->string('deposit_applied_batch_id')->nullable()->after('deposit_applied_at');
                }
                if (!Schema::hasColumn('team_stores', 'deposit_notes')) {
                    $table->string('deposit_notes', 500)->nullable()->after('deposit_applied_batch_id');
                }
            });
        }

        if (Schema::hasTable('parent_orders')) {
            Schema::table('parent_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('parent_orders', 'deposit_credit_applied')) {
                    $table->decimal('deposit_credit_applied', 10, 2)->default(0.00)->after('fee_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $cols = array_filter([
                    'deposit_amount',
                    'deposit_apply_to',
                    'deposit_applied',
                    'deposit_applied_at',
                    'deposit_applied_batch_id',
                    'deposit_notes',
                ], fn($c) => Schema::hasColumn('users', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('team_stores')) {
            Schema::table('team_stores', function (Blueprint $table) {
                $cols = array_filter([
                    'deposit_amount',
                    'deposit_apply_to',
                    'deposit_applied',
                    'deposit_applied_at',
                    'deposit_applied_batch_id',
                    'deposit_notes',
                ], fn($c) => Schema::hasColumn('team_stores', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('parent_orders') && Schema::hasColumn('parent_orders', 'deposit_credit_applied')) {
            Schema::table('parent_orders', function (Blueprint $table) {
                $table->dropColumn('deposit_credit_applied');
            });
        }
    }
};
