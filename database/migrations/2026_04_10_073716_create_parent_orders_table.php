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
        Schema::create('parent_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_store_id')->constrained()->onDelete('cascade');
            $table->string('athlete_name');
            $table->string('gender')->nullable();
            $table->json('items_json'); // Stores {"item_id": 1, "size": "M", "qty": 1, "name": "Uniform"}
            $table->text('special_notes')->nullable();
            $table->string('status')->default('Pending Coach Approval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_orders');
    }
};
