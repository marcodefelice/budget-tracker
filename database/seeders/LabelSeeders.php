<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Labels;

class LabelSeeders extends Seeder
{
    const COLORS = [
      "bg-blueGray-200 text-blueGray-600",
      "bg-red-200 text-red-600",
      "bg-orange-200 text-orange-600",
      "bg-amber-200 text-amber-600",
      "bg-teal-200 text-teal-600",
      "bg-lightBlue-200 text-lightBlue-600",
      "bg-indigo-200 text-indigo-600",
      "bg-purple-200 text-purple-600",
      "bg-pink-200 text-pink-600",
      "bg-emerald-200 text-emerald-600 border-white",
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $lang = env("LANG");
      $path = __DIR__.'/../sql/label.json';
      $data = (array) json_decode(file_get_contents($path));

      foreach ($data[$lang] as $key => $value) {
        $db = new Labels();
        $db->uuid = uniqid();
        $db->name = strtolower($value);
        $db->color = self::COLORS[rand(0,9)];
        $db->save();
      }
    }
}
