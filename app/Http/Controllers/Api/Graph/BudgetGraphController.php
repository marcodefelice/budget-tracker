<?php

namespace App\Http\Controllers\Api\Graph;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BudgetBackersModel;
use App\Http\Controllers\Api\BudgetController;
use Carbon\Carbon;

class BudgetGraphController extends BudgetController
{

    /**
    * @param string $dateTime
    * @param string $account
    */
    public function __construct(string $dateTime = null, $account = "all") {
      parent::__construct($dateTime, $account);
    }

    /**
     * Display a listing of the resource.
     * @param bool $salary set to true only salary incoming
     * @return \Illuminate\Http\Response
     */
    public function incoming(bool $salary = false)
    {
      return $this->retriveData("Entrata");
    }

    /**
     * Display a listing of the resource.
     * @param string $category
     * @return \Illuminate\Http\Response
     */
    public function incomingByCategory(string $category)
    {
          return $this->retriveData("Entrata",$category);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function expenses()
    {
      return $this->retriveData("Spese");
    }

    /**
     * Display a listing of the resource.
     * @param string $category
     * @return \Illuminate\Http\Response
     */
    public function expensesByCategory(string $category)
    {
      return $this->retriveData("Spese",$category);
    }

    /*
    * sum a data
    * @param \Illuminate\Database\Eloquent\Collection $data
    * @return int
    */
    private function sum(\Illuminate\Database\Eloquent\Collection $data) {
      $sum = 0;
      foreach ($data as $key => $value) {
        $sum = $sum + floatval(str_replace(",",".",$value->amount));
      }
      return $sum;
    }

    /**
     * Display a listing of the resource.
     * @param string $type
     * @param string $category
     * @return \Illuminate\Http\Response
     */
    private function retriveData(string $type, string $category = null )
    {
      $now = date("Y",time());
      $before = $now - 10;
      $result = [];
      $total = 0;
      while ($before <= $now) {

        $data = BudgetBackersModel::where("type",$type);
        if(!empty($category)) $data->where("category",$category);
        $data->where("created_at",">=", $before."-01-01");
        $data->where("created_at","<=", $before."-12-31");
        $resultData = $data->get();

        if($resultData->count() != 0) {

          $result[] = [
            "year" => $before,
            "total" => $this->sum($resultData),
          ];
        }

        $before++;

      }

      return response($result);
    }
}
