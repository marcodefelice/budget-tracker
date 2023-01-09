<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MathController
{
    /**
    * return percentage of Sum
    * @param int $from
    * @param int $to
    * @return int
    */
    static public function getPercentage(int $from, int $to)
    {
      try{
        $percent = $to / $from;
        $percent = $percent * 100;
        $percent = $percent - 100;
      } catch(\DivisionByZeroError $e) {
        Log::warning($e);
        $percent = 0;
      }

      return round($percent,2);
    }

    /*
    * sum of costo
    * @param \Illuminate\Database\Eloquent\Collection|array $data
    * @return integer
    */
    static public function sum( \Illuminate\Database\Eloquent\Collection|array $data)
    {
      $cost = 0;
      foreach ($data as $key => $value) {
        $cost = $value->amount + $cost;
      }
      return round($cost,2);
    }
}
