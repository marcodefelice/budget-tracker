<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry;
use App\Models\SubCategory;
use App\Models\PaymentsTypes;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Labels;
use App\Models\Payee;
use App\Models\PlannedEntries;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\Label;

class EntriesController extends BudgetController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $data = Entry::all();
    Cache::tags(["new_data","entry"])->put("entries",$data,env("CACHE_TTL"));
    return response($data);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {

    //check if planned
    $today = strtotime(date("Y-m-d 23:59:59", time()));
    $data = strtotime($request->created_at);

    $planned = 0;
    if ($data > $today) {
      $planned = 1;
    }

    if (!empty($request->planning)) {
      $this->savePlannedEntrie($request);
    }

    //save a new label
    if(!empty($request->newLabel)) {
      $labels = explode(",",$request->newLabel);
      $label = Labels::whereIn("name",$labels)->get();
      if(empty($label->count())) {
        //save the new label
        $label = new Labels();
        $label->uuid = uniqid();
        $label->color = \App\Http\Controllers\OperationsController::rand_color();
        $label->name = $request->newLabel;
        $label->save();
        $request->label = [$label->id];
      } else {
        foreach($label as $l) {
          $labelsFound[] = $l->id;
        }
        $request->label = $labelsFound;
      }
    }


    $request->amount = ($request->type == "transfer") ? -$request->amount : $request->amount;
    $db = $this->buildEntry($request, new Entry());
    $db->created_at = $request->created_at;
    $db->planned = $planned;
    $db->transfer = ($request->type == "transfer") ? 1 : 0;


    if (!empty($request->transferto)) {
      $transferto = Account::findOrFail($request->transferto);
      $db->transfer_id = $transferto->id;
    }

    $db->save();

    $account = Account::findOrFail($request->account);
    if ($request->type == "transfer") {
      $request->amount = $request->amount * -1; //riconvert
      $db = $this->buildEntry($request, new Entry());

      $db->uuid = uniqid();
      $db->planned = $planned;
      $db->account_id = $request->transferto;
  
      $transferto = Account::findOrFail($account->id);
      $db->transfer_id = $transferto->id;
      $db->transfer = 1;
      $db->created_at = $data;
      $db->save();
    }

    //update payee
    $debitName = $request->debit_name;
    if (!empty($debitName)) {
      //first check if already exist
      $payees = \App\Models\Payee::where("name", $debitName)->first();

      if($payees->type == 1) {
        $payees->destroy();
        unset($payees);
      } 

      if (empty($payees)) {

        $payees = new \App\Models\Payee();
        $payees->uuid = uniqid();
        $payees->type = 0; //type = 1 is forget debit
        $payees->name = $debitName;
        $payees->account_id = $account->id;
        $payees->save();
      }

      $entry = Entry::find($db->id);
      $entry->payee_id = $payees->id;
      $entry->save();
    }

    $labels = [];
    foreach ($request->label as $key => $value) {
      $label = Labels::findOrFail($value);
      $labels[] = $label->id;
    }

    if (!empty($labels)) {
      $db->label()->detach();
      $db->label()->attach($labels);
    }

    Log::info("Stored new entry with id " . $db->id);

    Cache::tags("entry")->lush();

    return response("ok");
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return void
   */
  private function savePlannedEntrie(Request $request)
  {
    if(empty($request->created_at)) {
      $request->created_at = date("y-m-d h:i:s",time());
    }
    //check if planned
    $today = strtotime(date("Y-m-d 23:59:59", time()));
    $data = strtotime($request->created_at);

    $plannedEntrie = new \App\Models\PlannedEntries();
    $plannedEntrie = $this->buildEntry($request,$plannedEntrie);
    $plannedEntrie->created_at = $request->created_at;
    $plannedEntrie->planning = $request->planning;

    $plannedEntrie->created_at = $request->created_at;
    if($data <= $today) {
      switch($request->planning) {
        case "daily":
          $increment = "+1 Day";
          break;
        case "monthli":
          $increment = "+1 Month";
          break;
        case "yearli":
          $increment = "+1 Year";
          break;
        default:
          $increment = "+0 Day";
          break;
      }
      $plannedEntrie->created_at = date("Y-m-d",strtotime($increment, strtotime($request->created_at)));
    }

    $plannedEntrie->save();
  }

  /**
   * get all planned entries
   * @return \Illuminate\Http\Response
   */
  public function getPlannedEntries() {
    return Cache::tags(["stored_data","entry"])->remember("getPlannedEntries",env("CACHE_TTL"),function() {
      $plannedEntries = \App\Models\PlannedEntries::with("subCategory.category")->with("account")->get();
      return response($plannedEntries);
    });

  }

  /**
   * Create a entry object
   * @param \Illuminate\Http\Request  $request
   * @param \App\Models\PlannedEntries|\App\Models\Entry $data
   * @return \Illuminate\Database\Eloquent\Builder 
   */
  private function buildEntry(Request $request,$entry)
  {

    $paymentType = PaymentsTypes::findOrFail($request->payment_type);
    $account = Account::findOrFail($request->account);
    $currency = Currency::findOrFail($request->currency);
    if (empty($request->category)) {
      $category = SubCategory::where("uuid", "635bd34d25f01")->firstOrFail();
    } else {
      $category = SubCategory::findOrFail($request->category);
    }

    if (empty($request->uuid)) {
      $entry = $entry;
      $entry->uuid = uniqid();
    } else {
      $entry = Entry::where("uuid", $request->uuid)->firstOrFail();
      $entry->uuid = $request->uuid;
    }

    if($request->type == "debit") {
      $category = SubCategory::where("uuid", "635bd3499f86c")->firstOrFail();
    }

    if(!empty($request->debit)) {
      $entry->payee_id = $request->debit;
    }
    
    $entry->amount = $request->amount;
    $entry->note = $request->note;
    $entry->type = $request->type;
    $entry->waranty = 0;
    $entry->confirmed = $request->confirmed;
    $entry->category_id = $category->id;
    $entry->payment_type = $paymentType->id;
    $entry->account_id = $account->id;
    $entry->currency_id = $currency->id;

    return $entry;
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function showFromAccount($id, $filter = null)
  {
    $cache = sha1("entries-from-account-" . $id . json_encode($filter));
    return response(Cache::get($cache, function () use ($id, $cache, $filter) {
      $entries = Entry::with("subCategory.category")
        ->with("account")
        ->with("payee.account")
        ->with("label")
        ->with("transferTo");

      if (!empty($id)) {
        $account = Account::findOrFail($id);
        $entries = $entries->where("account_id", $account->id);
      } else {
        $entries = $entries->where("transfer", 0);
      }

      $entries = $entries->orderBy("created_at", "desc");

      if (!empty($filter['labels'])) {
        $entries->whereHas('label', function ($q) use ($filter) {
          $q->where('labels.id', $filter['labels']);
        });
      }

      if (!empty($filter['category'])) {
        $entries->whereIn("category_id", [$filter['category']]);
      }

      if (!empty($filter['type'])) {
        $entries->where("type", $filter['type']);
      }

      $entries = $entries->paginate(50);
      Cache::tags(["stored_data","entry"])->forever($cache, $entries);

      return $entries;
    }));
  }

  /**
   * find the specified resource.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function find(Request $request)
  {

    $cache = json_encode($request->toArray());
    if(Cache::has($cache)) {
      return Cache::get($cache);
    }

    //TODO: request validate
    $this->dateTime = date("Y/m/d H:i:s", strtotime("-1 month", time()));
    $this->pagination = 0;

    if (!empty($request->month)) {
      $this->endDateTime = date("Y/m/01 H:i:s", strtotime($request->month));
      $this->startDateTime = date("Y/m/01 H:i:s", strtotime("+1 Month", strtotime($request->month)));
    }

    if (!empty($request->year)) {
      $this->endDateTime = date("Y/m/d H:i:s", strtotime($request->year . "/01/01"));
      $this->startDateTime = date("Y/m/d H:i:s", strtotime("+1 Year", strtotime($request->year . "/01/01")));
    }

    if (!empty($request->year) && !empty($request->month)) {
      $this->endDateTime = date("Y/m/d H:i:s", strtotime($request->year . "/" . $request->month . "/01"));
      $this->startDateTime = date("Y/m/d H:i:s", strtotime("+1 Month", strtotime($request->year . "/" . $request->month . "/01")));
    }

    $entry = Entry::where("id", ">", 0);

    if (!empty($request->text)) {
      $entry = $entry->where("note", "like", "%$request->text%");
    }

    if (!empty($request->account)) {
      $this->account = $request->account;
    }

    if (!empty($request->category)) {
      $this->category = $request->category;
    }

    $type = $request->type;
    if (empty($request->type)) {
      $type = ["incoming","expenses","debit","transfer"];
    } 

    $this->type = $type;

    if (!empty($request->tags)) {
      $this->tags = $request->tags;
    }

    $entries = $this->get($entry);

    $total = $this->getTotalType($type,$entries);
    $response = ["data" => $entries,"total" => $total];
    Cache::tags(["stored_data","entry"])->put($cache,$response,env("CACHE_TTL"));

    return response($response);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    return Cache::tags(["stored_data","entry"])->remember("label-".$id,env("CACHE_TTL"),function() use($id) {
      $entries = Entry::with("label")->where("id", $id)->firstOrFail();
      return response($entries);
    });

  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    Entry::findOrFail($id)->delete();
    Cache::tags("entry")->flush();
    Log::info("Deleted entry with id " . $id);
  }

  /**
   * delete planned entry
   */
  public function deletePlanned($id) {
    PlannedEntries::findOrFail($id)->delete();
    Cache::tags("entry")->flush();
    Log::info("Deleted planned entry with id " . $id);
  }
}
