<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Entry;
use App\Models\ActionJobConfiguration;
use Illuminate\Support\Facades\Cache;
use stdClass;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(
          Cache::get("accounts",function() {
            $account = Account::where("deleted_at",null)->get();
            Cache::tags(["stored_data"])->forEver($account,"accounts");
            return $account;
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
     * set a fix on total wallet amount
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function setTotalWallet(Request $request)
    {
        $lastRow = Entry::orderBy("id","desc")->firstOrFail();
        //TODO: validation
        $data = [
            "account_id" => $request->account_id, "amount" => $request->amount,"lastrow" => $lastRow->id
        ];

        $account = new ActionJobConfiguration();
        $account->config = json_encode($data);
        $account->action = "walletFix_configuration";
        $account->save();

        Cache::forget('wallet-'.$request->account_id);

        return response("ok");
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
