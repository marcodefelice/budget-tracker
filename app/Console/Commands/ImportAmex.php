<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\AmexService;

class ImportAmex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:importamex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run import data AMEX service from csv file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $import = new AmexService();
        $import->handle();
        return Command::SUCCESS;
    }
}
