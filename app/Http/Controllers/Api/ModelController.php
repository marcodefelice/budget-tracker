<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Models;
use App\Models\SubCategory;
use App\Models\PaymentsTypes;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Labels;
use Illuminate\Support\Facades\Cache;

class ModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return response(
        Cache::get("Models",function() {
          $Models = Models::with("label")->get();
          Cache::tags(["stored_data"])->forEver($Models,"Models");
          return $Models;
        })
      );
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
      //TODO: validate a Request
      $category = SubCategory::findOrFail($request->category);
      $paymentType = PaymentsTypes::findOrFail($request->payment_type);
      $account = Account::findOrFail($request->account);
      $currency = Currency::findOrFail($request->currency);

      $db = new Models();
      $db->uuid = uniqid();
      $db->name = (empty($request->name)) ? uniqid() : $request->name;
      $db->amount = $request->amount;
      $db->note = $request->note;
      $db->type = $request->type;
      $db->transfer = ($request->type == "traferimento") ? 1 : 0;
      $db->waranty = 0;
      $db->confirmed = 1;
      $db->planned = 0;
      $db->category_id = $category->id;
      $db->payment_type = $paymentType->id;
      $db->account_id = $account->id;
      $db->currency_id = $currency->id;
      $db->save();

      $labels = [];
      foreach ($request->label as $key => $value) {
        $label = Labels::findOrFail($value);
        $labels[] = $label->id;
      }

      if(!empty($labels)) {
        $db->label()->detach();
        $db->label()->attach($labels);
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }
}
