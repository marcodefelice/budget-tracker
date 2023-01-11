<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Graph\BudgetGraphController;
use App\Http\Controllers\Api\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class);
Route::apiResource('accounts', \App\Http\Controllers\Api\AccountController::class);
Route::apiResource('payee', \App\Http\Controllers\Api\PayeeController::class);
Route::apiResource('paymentstype', \App\Http\Controllers\Api\PaymentTypeController::class);
Route::apiResource('currencies', \App\Http\Controllers\Api\CurrencyController::class);
Route::apiResource('labels', \App\Http\Controllers\Api\LabelsController::class);
Route::apiResource('entry', \App\Http\Controllers\Api\EntriesController::class);
Route::apiResource('model', \App\Http\Controllers\Api\ModelController::class);
Route::apiResource('debit', \App\Http\Controllers\Api\PayeeController::class);

Route::get('/debit/get-total/entry', function(Request $request) {
  $method = new \App\Http\Controllers\Api\PayeeController;
  return $method->getAllPayee();
});


Route::post('stats/graph/category/time', function(Request $request) {
  $method = new \App\Http\Controllers\Api\Graph\BarGraphController;
  return $method->getCategoryTime($request->category,$request->time,$request->year);
});

Route::post('stats/graph/category', function(Request $request) {
  $method = new \App\Http\Controllers\Api\Graph\BarGraphController;
  return $method->getCategory($request->timeStart,$request->timeEnd);
});

Route::post('stats/graph/label', function(Request $request) {
  $method = new \App\Http\Controllers\Api\Graph\BarGraphController;
  return $method->getLabel($request->timeStart,$request->timeEnd);
});

Route::post('stats/graph/entries', function(Request $request) {
  $method = new \App\Http\Controllers\Api\Graph\BarGraphController;
  return $method->getCategoryTime($request->category,$request->time,$request->year,"incoming");
});

Route::post('stats/graph/expenses', function(Request $request) {
  $method = new \App\Http\Controllers\Api\Graph\BarGraphController;
  return $method->getCategoryTime([],$request->time,$request->year);
});

Route::post('stats/graph/entries-expenses', function(Request $request) {
  $method = new \App\Http\Controllers\Api\Graph\BarGraphController;
  $incoming = $method->getCategory($request->timeStart,$request->timeEnd,"incoming");
  $expenses = $method->getCategory($request->timeStart,$request->timeEnd,"expenses");
  return [
    ["label" => "incoming",
    "total" => $incoming["total"]],
    ["label" => "expenses",
    "total" => $expenses["total"]]
  ];
});

// {"timeStart":"2022/12/01","timeEnd": "2022/12/31"}

Route::post('entries/import', function(Request $request) {
  $method = new \App\Http\Controllers\Api\ImportController();
  return $method->import($request);
});

Route::get('stats/month-wallet/incoming', function() {
  $stats = new StatsController();
  return $stats->getStatsIncomingMonthWallet();
});

Route::get('planned-entries', function() {
  $planned = new \App\Http\Controllers\Api\EntriesController();
  return $planned->getPlannedEntries();
});

Route::delete('planned-entries/{id}', function($id) {
  $planned = new \App\Http\Controllers\Api\EntriesController();
  return $planned->deletePlanned($id);
});

Route::get('stats/wallet/', function() {
  $stats = new StatsController();
  return $stats->getSumWallets(false);
});

Route::get('stats/wallet/{id}', function($id) {
  $stats = new StatsController();
  return $stats->getTotalWallet((int) $id,false);
});

Route::post('search/{id?}', function(Request $request) {
  $search = new \App\Http\Controllers\Api\EntriesController();
  return $search->find($request);
});

Route::get('stats/wallet-planned/', function(Request $request) {
  $stats = new StatsController();
  return $stats->getSumWallets(true);
});

Route::post('stats/wallet/', function(Request $request) {
  $stats = new \App\Http\Controllers\Api\AccountController();
  return $stats->setTotalWallet($request);
});

Route::get('stats/wallets', function() {
  $stats = new StatsController();
  return $stats->getWallets();
});

Route::get('stats/month-wallet/expenses', function() {
  $stats = new StatsController();
  return $stats->getStatsExpensiveMonthWallet();
});

Route::get('stats/month-wallet/planned', function() {
  $stats = new StatsController();
  return $stats->getStatsPlannedMonthWallet();
});

Route::get('entries/account/{id}/{filter?}', function($id,$filter = null) {
  $stats = new \App\Http\Controllers\Api\EntriesController();
  $filters = [];
  $filter = explode("&",$filter);
  foreach($filter as $f) {
    $element = explode("=",$f);
    if(!empty($f[1])) {
      $filters[$element[0]] = $element[1];
    }
  }
  return $stats->showFromAccount($id,$filters);
});

Route::middleware([\App\Http\Middleware\ApiAuthenticationMiddleware::class])->group(function () {
  Route::get("/dailybudget/", function (Request $request) {
      return $request->user();
  });

  Route::apiResource('graph', BudgetGraphController::class);
  Route::get("graph/incoming/{category}/{account?}/{datetime?}",function($category = "all",$account = "all", $datetime = "") {
    $response = new BudgetGraphController($datetime,$account);

    switch ($category) {
      case 'all':
        return $response->incoming();
        break;
      case "salary":
        return $response->incoming(true);
        break;
      default:
        return $response->incomingByCategory($category);
        break;
    }
  });

    Route::get("graph/expenses/{category}/{account?}/{datetime?}",function($category = "all",$account = "all", $datetime = "") {
      $response = new BudgetGraphController($datetime,$account);

      switch ($category) {
        case 'all':
          return $response->expenses();
          break;
        default:
          return $response->expensesByCategory($category);
          break;
      }

  });
});
