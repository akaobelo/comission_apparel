<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('position_title');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('organization_name');
            $table->string('apparel_category', 100);
            $table->unsignedInteger('estimated_quantity');
            $table->string('package_type', 50)->default('full_program_bundle');
            $table->date('target_delivery_date')->nullable();
            $table->text('design_vision')->nullable();
            $table->string('status', 30)->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
