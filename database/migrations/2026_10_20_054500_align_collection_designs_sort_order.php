<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\DesignCollection;
use App\Models\DesignCatalog;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Inverts existing collection design sort orders so that the previous DESC
     * store display order maps sequentially 1..N in ASC. This synchronizes the public
     * store catalog with "Manage Sort Order" and "Update Existing Collections".
     */
    public function up(): void
    {
        $collections = DesignCollection::all();

        foreach ($collections as $collection) {
            $items = DesignCatalog::where('design_collection_id', $collection->id)
                ->orderBy('sort_order', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $sort = 1;
            foreach ($items as $item) {
                $item->update(['sort_order' => $sort]);
                $sort++;
            }
        }

        $unassigned = DesignCatalog::whereNull('design_collection_id')
            ->orderBy('sort_order', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $sort = 1;
        foreach ($unassigned as $item) {
            $item->update(['sort_order' => $sort]);
            $sort++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $collections = DesignCollection::all();

        foreach ($collections as $collection) {
            $items = DesignCatalog::where('design_collection_id', $collection->id)
                ->orderBy('sort_order', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $sort = 1;
            foreach ($items as $item) {
                $item->update(['sort_order' => $sort]);
                $sort++;
            }
        }
    }
};
