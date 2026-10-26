<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ParentOrder;

class RestoreHistoricalRepOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:restore-historical-reps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restores historical orders that were mistakenly attributed to newer sales reps upon coach assignment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('orders:restore-historical-reps is deprecated and disabled. Order attribution is strictly managed by client assignment dates and explicit admin assignments.');
        return 0;
    }
}
