<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utility\MathController;
use Illuminate\Http\Request;
use App\Models\BudgetBackersModel;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BudgetController extends Controller
{
    /* @var string $dateTime */
    protected $startDateTime;
    protected $endDateTime;
    protected $tags;
    protected $category;
    protected $account;
    protected $type;
    protected $pagination = 50;
    protected $paginate = null;

    /**
    * @param string $dateTime
    * @param string $account
    */
    public function __construct()
    {
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Display a listing of the resource.
     * @param bool $salary set to true only salary incoming
     * @return \Illuminate\Http\Response
     */
    public function incoming(bool $salary = false)
    {

    }

    /**
     * Display a listing of the resource.
     * @param string $category
     * @return \Illuminate\Http\Response
     */
    public function incomingByCategory(string $category)
    {

    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function expenses()
    {

    }

    /**
     * Display a listing of the resource.
     * @param string $category
     * @return \Illuminate\Http\Response
     */
    public function expensesByCategory(string $category)
    {

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
        //
    }

    /*
    * @param \Illuminate\Database\Eloquent\Builder $data
    * @param bool $relations
    *
    * @return \Illuminate\Database\Eloquent\Builder 
    */
    protected function get(\Illuminate\Database\Eloquent\Builder $data, $relations = true)
    {
      if($relations === true) {
        $data->with("subCategory.category")
        ->with("account")
        ->with("payee.account")
        ->with("label")
        ->with("transferTo");
      }

        
      if(!empty($this->startDateTime)) {
        $data->where("created_at", "<=", $this->startDateTime);
      }

      if(!empty($this->endDateTime)) {
        $data->where("created_at", ">=", $this->endDateTime);
      }

      if(!empty($this->account)) {
        $data->whereIn("account_id",$this->account);
      }

      if(!empty($this->category)) {
        $data->whereIn("category_id",$this->category);
      }

      if(!empty($this->type)) {
        $data->whereIn("type",$this->type);
      }
      
      $filer = $this->tags;
      if(!empty($this->tags)) {
        $data->whereHas('label', function($q) use($filer){
          $q->whereIn('labels.id', $filer);
       });
      }

      $data->orderBy("created_at","desc");
      if(!empty($this->pagination)) {
        $data = $data->paginate($this->paginate);
      } else {
        $data = $data->get();
      }
      return $data;
    }

    /**
     * get toal by type
     * @param array $types of entries to analyze
     * @param Illuminate\Database\Eloquent\Collection $entry
     * 
     * @return array with all sum
     */
    protected function getTotalType(array $types, \Illuminate\Database\Eloquent\Collection $entries) 
    {

      try {
        $result["incoming"] = null;
        $result["transfer"] = null;
        $result["expenses"] = null;
        $result["debit"] = null;

        foreach($entries as $entry) {
          foreach($types as $t) {
            if($entry->type == $t) {
              $result[$t][] = $entry;
            }
          }
        }
        
        $result["incoming"] = empty($result['incoming']) ? $result['incoming'] : MathController::sum($result['incoming']);
        $result["transfer"] = empty($result['transfer']) ? $result['transfer'] : MathController::sum($result['transfer']);
        $result["expenses"] = empty($result['expenses']) ? $result['expenses'] : MathController::sum($result['expenses']);
        $result["debit"]    = empty($result['debit'])    ? $result['debit']    : MathController::sum($result['debit']);

        return $result;

      } catch(Exception $e) {
        Log::error("Unabe to Math total amout ".$e);
      }

    }

    /**
     * get all entry data
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPlannesEntries()
    {
      return Cache::tags(["stored_data","budget"])->remember("PlannesEntries",env("CACHE_TTL"), function() {
        $data = \App\Models\Entry::where("planned", 1);
        $$expenses = $this->get($data);
        return $expenses;
      });

    }

}
