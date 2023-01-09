<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $lang = env("LANG");
      $path = __DIR__.'/../sql/currency.json';
      $data = (array) json_decode(file_get_contents($path));

      foreach ($data[$lang] as $key => $value) {
        $db = new Currency();
        $db->uuid = uniqid();
        $db->name = strtolower($value);
        $db->save();
      }
    }
}
