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
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('Program Spotlight');
            $table->string('author')->nullable()->default('Commission Apparel');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('video_url')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('cta_text')->nullable()->default('Request A Custom Quote');
            $table->string('cta_url')->nullable()->default('/quote');
            $table->dateTime('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
