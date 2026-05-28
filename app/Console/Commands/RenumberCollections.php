<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DesignCollection;
use App\Models\DesignCatalog;

class RenumberCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collections:renumber';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically renumbers all design catalog items to start from 1 sequentially within their collections.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting renumbering process...');

        $collections = DesignCollection::all();
        $totalUpdated = 0;

        foreach ($collections as $collection) {
            $items = DesignCatalog::where('design_collection_id', $collection->id)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
            $sort = 1;
            foreach ($items as $item) {
                if ($item->sort_order !== $sort) {
                    $item->update(['sort_order' => $sort]);
                    $totalUpdated++;
                }
                $sort++;
            }
        }

        // Also renumber unassigned items
        $unassigned = DesignCatalog::whereNull('design_collection_id')
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->get();
        $sort = 1;
        foreach ($unassigned as $item) {
            if ($item->sort_order !== $sort) {
                $item->update(['sort_order' => $sort]);
                $totalUpdated++;
            }
            $sort++;
        }

        $this->info("Successfully renumbered $totalUpdated items!");
        return Command::SUCCESS;
    }
}
