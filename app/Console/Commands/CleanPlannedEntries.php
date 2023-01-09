<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\OperationsController;

class CleanPlannedEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanentries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all entries data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   
        $handle = new \App\Jobs\InsertPlannedEntry(); 
        $handle->handle();
        return Command::SUCCESS;
    }
}
