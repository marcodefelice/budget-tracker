<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\ImportService;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:importdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run import data service from csv file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $import = new ImportService();
        $import->handle();
        return Command::SUCCESS;
    }
}
