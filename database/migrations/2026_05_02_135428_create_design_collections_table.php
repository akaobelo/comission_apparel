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
        Schema::create('design_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::table('design_catalog', function (Blueprint $table) {
            $table->unsignedBigInteger('design_collection_id')->nullable()->after('id');
            $table->foreign('design_collection_id')->references('id')->on('design_collections')->nullOnDelete();
        });

        // Data migration
        $distinctCollections = DB::table('design_catalog')
            ->whereNotNull('collection_name')
            ->where('collection_name', '!=', '')
            ->select('collection_name')
            ->distinct()
            ->get();

        foreach ($distinctCollections as $col) {
            $firstDesign = DB::table('design_catalog')
                ->where('collection_name', $col->collection_name)
                ->orderBy('sort_order', 'desc')
                ->first();

            $imagePath = null;
            if ($firstDesign) {
                $images = json_decode($firstDesign->image_paths, true);
                if (!empty($images)) {
                    $imagePath = $images[0];
                } elseif (!empty($firstDesign->image_url)) {
                    $imagePath = $firstDesign->image_url;
                }
            }

            $collectionId = DB::table('design_collections')->insertGetId([
                'name' => $col->collection_name,
                'image_path' => $imagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('design_catalog')
                ->where('collection_name', $col->collection_name)
                ->update(['design_collection_id' => $collectionId]);
        }

        // Drop the old column
        Schema::table('design_catalog', function (Blueprint $table) {
            $table->dropColumn('collection_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_catalog', function (Blueprint $table) {
            $table->string('collection_name')->nullable()->after('name');
        });

        // Try to reverse the data migration
        $designs = DB::table('design_catalog')->whereNotNull('design_collection_id')->get();
        foreach ($designs as $design) {
            $collection = DB::table('design_collections')->where('id', $design->design_collection_id)->first();
            if ($collection) {
                DB::table('design_catalog')
                    ->where('id', $design->id)
                    ->update(['collection_name' => $collection->name]);
            }
        }

        Schema::table('design_catalog', function (Blueprint $table) {
            $table->dropForeign(['design_collection_id']);
            $table->dropColumn('design_collection_id');
        });

        Schema::dropIfExists('design_collections');
    }
};
