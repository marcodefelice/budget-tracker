<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\CredemService;

class ImportCredem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:importcredem';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run import data CREDEM service from csv file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $import = new CredemService();
        $import->handle();
        return Command::SUCCESS;
    }
}
