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
        Schema::table('quote_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('quote_requests', 'addressed_by')) {
                $table->string('addressed_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('quote_requests', 'addressed_at')) {
                $table->timestamp('addressed_at')->nullable()->after('addressed_by');
            }
            if (!Schema::hasColumn('quote_requests', 'sales_rep_id')) {
                $table->unsignedBigInteger('sales_rep_id')->nullable()->after('sales_rep');
                $table->index('sales_rep_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('quote_requests', 'addressed_by')) $columns[] = 'addressed_by';
            if (Schema::hasColumn('quote_requests', 'addressed_at')) $columns[] = 'addressed_at';
            if (Schema::hasColumn('quote_requests', 'sales_rep_id')) $columns[] = 'sales_rep_id';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
