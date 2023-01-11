<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry;
use App\Http\Controllers\Utility\MathController;
use App\Models\Account;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Cache;
use App\Models\ActionJobConfiguration;

class StatsController extends BudgetController
{
  private $walletFixAccount;

  /*
    * get stats incoming
    * @return \Illuminate\Http\Response
    */
  public function getStatsIncomingMonthWallet()
  {
    return response(Cache::get("getStatsIncomingMonthWallet", function () {
      $start = date("Y/m/01 00:01:01", strtotime("-1 Month", time()));
      $count = 0;

      //TODO: make these choose how an options
      $salaryCat = SubCategory::whereIn(
        "uuid",
        [
          "6357ff2aaf8eb",
          "6357ff2ab1a47",
          "635bd34262b0a",
          "635bd34262b0a",
          "635bd34ac3e9b",
          "635bd34c00eec",
          "635bd349b66a4"
        ]
      )->get();

      while ($count <= 1) {

        $next =  date("Y/m/01 00:01:01", strtotime("+1 Month", strtotime($start)));
        $before = date("Y/m/01 00:01:01", strtotime($start));

        $data = Entry::where("type", "incoming");
        $data->where("transfer", 0);

        foreach ($salaryCat as $cat) {
          $category[] = $cat->id;
        }

        $data->whereIn("category_id", $category);
        $data->where("created_at", "<=", $next);
        $data->where("created_at", ">=", $before);
        $data->where("planned", 0);
        $entries[] = $data->get();

        $count++;
        $start = date("Y/m/01 00:01:01", time());
      }

      $total_month = MathController::sum($entries[0]);
      $total_month_before = MathController::sum($entries[1]);
      $percentage = MathController::getPercentage($total_month, $total_month_before);

      $returnData = [
        "total_month" => $total_month_before,
        "total_before" => $total_month,
        "percend_different" => $percentage
      ];

      Cache::tags(["stored_data","stats"])->forever("getStatsIncomingMonthWallet", $returnData);
      return $returnData;
    }));
  }


  /*
    * get stats expensive
    * @return \Illuminate\Http\Response
    */
  public function getStatsExpensiveMonthWallet()
  {
    return response(Cache::get("getStatsExpensiveMonthWallet", function () {
      $start = date("Y/m/01 00:01:01", strtotime("-1 Month", time()));
      $count = 0;

      while ($count <= 1) {

        $next =  date("Y/m/01 00:01:01", strtotime("+1 Month", strtotime($start)));
        $before = date("Y/m/01 00:01:01", strtotime($start));

        $data = Entry::where("type", "expenses");
        $data->where("transfer", 0);
        $data->where("created_at", "<=", $next);
        $data->where("created_at", ">=", $before);
        $data->where("planned", 0);
        $entries[] = $data->get();

        $count++;
        $start = date("Y/m/01 00:01:01", time());
      }

      $total_month = MathController::sum($entries[0]);
      $total_month_before = MathController::sum($entries[1]);
      $percentage = MathController::getPercentage($total_month, $total_month_before);

      $returnData = [
        "total_month" => $total_month_before,
        "total_before" => $total_month,
        "percend_different" => $percentage
      ];

      Cache::tags(["stored_data","stats"])->forever("getStatsExpensiveMonthWallet", $returnData);
      return $returnData;
    }));
  }

  /**
   * get only planned entry of month
   * @param string $type expenses | incoming | debit
   * 
   * @return array
   */
  public function getStatsPlannedMonthWallet()
  {
    return response(Cache::get("gestStatsPlannedMonthWallet", function () {

      $date = new \DateTime('now');
      $date->modify('last day of this month');
      $startTime = $date->format('Y-m-d');

      $date->modify("first day of this month");
      $endTime = $date->format('Y-m-d');

      $data = Entry::where("planned", 1);
      $this->startDateTime = $startTime;
      $this->endDateTime = $endTime;
      $this->pagination = false;
      $entries = $this->get($data, false);

      $returnData = $this->getTotalType(["expenses", "incoming"], $entries);

      Cache::tags(["stored_data","stats"])->forever("getStatsPlannedMonthWallet", $returnData);
      return $returnData;
    }));
  }

  /**
   * @return \Illuminate\Http\Response
   */
  public function getWallets()
  {
    return response(
      Cache::get("wallets", function () {
        $accounts = Account::where("deleted_at", null)->get();
        $response = [];
        foreach ($accounts as $account) {
          $total = $this->getTotalWallet($account->id, false);
          $response[] = [
            "account_id" => $account->id,
            "account_label" => $account->name,
            "total_wallet" => $total->original['total'],
            "color" => $account->color
          ];
        }
        Cache::tags(["stored_data","stats"])->forever("wallets", $response);
        return $response;
      })
    );
  }

  /**
   * get total wallet
   * p = param
   * i = incoming
   * formula: 
   * e = ( expenses - entry ) - p
   * i = ( entry + e )
   * @param int $id
   * @param bool $time
   * @return \Illuminate\Http\Response
   */
  public function getTotalWallet(int $id, $planned = false)
  {
    $cache = "wallet-";
    if ($planned === true) {
      $cache = "wallet-planned-";
    }

    return response(Cache::get($cache . $id, function () use ($id, $planned, $cache) {
      if (empty($id)) {
        $account = 0;
        $id = [41, 1, 11, 31, 21, 192]; //TODO: make configurable
      } else {
        $account = $id;
        $id = [$id];
      }

      $entry = Entry::where("type", "incoming")->whereIn("account_id", $id);
      $expenses = Entry::where("type", "expenses")->whereIn("account_id", $id);
      $debit = Entry::where("type", "debit")->whereIn("account_id", $id);
      $transfer = Entry::where("type", "transfer")->whereIn("account_id", $id);

      if ($planned === true) {
        $entry->whereIn("planned", [0, 1]);
        $expenses->whereIn("planned", [0, 1]);
        $debit->whereIn("planned", [0, 1]);

        $date = new \DateTime('now');
        $date->modify('last day of this month');
        $currentDate = $date->format('Y-m-d');

        $currentDate = date("Y-m-31", time());
        $entry->where("created_at", "<=", $currentDate);
        $expenses->where("created_at", "<=", $currentDate);
        $debit->where("created_at", "<=", $currentDate);
      } else {
        $entry->where("planned", 0);
        $expenses->where("planned", 0);
        $debit->where("planned", 0);
      }

      $this->walletFixAccount = $this->walletFix($account);

      if ($this->walletFixAccount->account_id == $account) {
        $entry = $entry->where("id", ">", $this->walletFixAccount->lastrow);
        $expenses = $expenses->where("id", ">", $this->walletFixAccount->lastrow);
        $debit = $debit->where("id", ">", $this->walletFixAccount->lastrow);
        $transfer = $transfer->where("id", ">", $this->walletFixAccount->lastrow);
      }

      $expenses = $expenses->get();
      $entry = $entry->get();
      $debit = $debit->get();
      $transfer = $transfer->get();


      $expenses = MathController::sum($expenses);

      $expenses = $expenses * -1;

      $entry = MathController::sum($entry);

      $debit = MathController::sum($debit);
      $transfer = MathController::sum($transfer);

      $total = $expenses - $entry;

      $total = $this->walletFixAccount->amount - $total;

      if ($total <= $debit) {
        $total = $debit + $total;
      } else {
        //FIXME: best use bcmath functions
        $debit = $debit * -1;
        $debit = $debit - $total;
        $total = $debit * -1;
      }

      if ($total <= $transfer) {
        $total = $transfer + $total;
      } else {
        //FIXME: best use bcmath functions
        $transfer = $transfer * -1;
        $transfer = $transfer - $total;
        $total = $transfer * -1;
      }

      $response = ["total" => round($total, 2)];
      Cache::tags(["stored_data","stats"])->forever($cache . $account, $response);
      return $response;
    }));
  }

  /**
   * get wallet fix 78187.79;
   * @param int $account
   * @return \stdClass
   */
  private function walletFix(int $account)
  {
    $fix = ActionJobConfiguration::where("action", "walletFix_configuration")->orderBy("id", "desc")->get();
    $config = json_decode(
      '{"account_id":' . $account . ',"amount":"0","lastrow":1}'
    );

    foreach ($fix as $f) {
      $config = json_decode($f->config);
      if ($config->account_id == $account) {
        return $config;
      }
    }

    return $config;
  }
}
