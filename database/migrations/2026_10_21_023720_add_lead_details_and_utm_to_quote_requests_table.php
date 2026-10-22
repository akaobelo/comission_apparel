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
            $table->string('organization_type', 100)->nullable()->after('organization_name');
            $table->string('lead_source', 100)->default('quote_form')->after('status');
            $table->string('raw_quantity', 50)->nullable()->after('estimated_quantity');
            $table->string('utm_source')->nullable()->after('lead_source');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_term')->nullable()->after('utm_campaign');
            $table->string('utm_content')->nullable()->after('utm_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn([
                'organization_type',
                'lead_source',
                'raw_quantity',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
            ]);
        });
    }
};
