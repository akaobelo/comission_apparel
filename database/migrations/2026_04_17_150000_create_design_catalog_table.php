<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Springfield Eagles - Home Jersey"
            $table->string('type'); // uniform_top, uniform_bottom, warmup_top, warmup_bottom, backpack, arm_sleeve, accessory
            $table->string('category')->default('individual'); // package_a, package_b, package_c, individual
            $table->string('image_url')->nullable();
            $table->boolean('has_name_field')->default(false); // for backpacks / personalized items
            $table->boolean('has_number_field')->default(false); // for jerseys that need number
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_catalog');
    }
};
