<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      $this->call([
          \Database\Seeders\AccountSeeders::class,
          \Database\Seeders\CategorySeeders::class,
          \Database\Seeders\CurrencySeeders::class,
          \Database\Seeders\PaymentTypeSeeders::class,
          \Database\Seeders\LabelSeeders::class,
          \Database\Seeders\ActionJobConfigSeeders::class

      ]);
    }
}
