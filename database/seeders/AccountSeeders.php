<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $lang = env("LANG");
      $path = __DIR__.'/../sql/account.json';
      $data = (array) json_decode(file_get_contents($path));
      foreach ($data[$lang] as $key => $value) {
        $db = new Account();
        $db->uuid = uniqid();
        $db->name = strtolower($value);
        $db->save();
      }
    }
}
