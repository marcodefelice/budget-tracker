<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utility\MathController;
use Illuminate\Http\Request;
use App\Models\Payee;
use Illuminate\Support\Facades\Cache;

class PayeeController extends BudgetController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
      return response(Payee::all());
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
      //
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
     $payee = Payee::find($id);
     $payee->type = 1;
     $payee->save();

     Cache::tags(["payee"])->flush();
     
     return response("Forget debit OK");
  }

  /**
   * get al debit with entry
   */
  public function getAllPayee() {
    if(Cache::has("get-all_payee")) {
        return response(Cache::get("get-all_payee"));
    }

    $data = Payee::with("entry")->get();

    foreach($data as $entries) {
        $amount = MathController::sum($entries->entry);
        $entries->amount = $amount;
    }

    Cache::tags(["stored_data","payee"])->forever("get-all_payee",$data);

    return response($data);
  }
}
