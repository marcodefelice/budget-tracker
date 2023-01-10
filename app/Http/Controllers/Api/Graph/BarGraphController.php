<?php

namespace App\Http\Controllers\Api\Graph;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BudgetBackersModel;
use App\Models\SubCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpParser\Node\Stmt\Label;

class BarGraphController extends BudgetGraphController
{
    /**
     * bar graph of sum category
     * @param array $categoryID
     * @param string $time [ monthly, yearli ]
     * @param string $year
     * @param string $type
     * 
     * @return \Illuminate\Http\Response
     */
    public function getCategoryTime(array $categoryID, string $time, string $year, string $type = "expenses")
    {
        /**
         *  label = "name category"
         *  amount = "total amount" 
         */

        $cache = "getCategoryTime".sha1(json_encode($categoryID).$time.$year.$type);
        if(Cache::has($cache)) {
            return Cache::get($cache);
        }

        $ofYear = strtotime("$year/01/01");
        if ($time === "monthly") {
            $startTime = 0;
            $endTime = 11;
        }

        if ($time === "yearly") {
            $startTime = 0;
            $endTime = 4;
        }
        $t = $startTime;
        while ($t <= $endTime) {

            $timeStart = date("Y/m/01 00:01:0", strtotime("$t Month", $ofYear));
            $timeEnd = date("Y/m/01 00:01:01", strtotime("+1 Month", strtotime("$t Month", $ofYear)));
            $label = date("M", strtotime($timeStart));
            if ($time === "yearli") {
                $timeStart = date("Y/01/01 00:01:0", strtotime("-$t Year", time()));
                $timeEnd = date("Y/12/31 00:00:00", strtotime("-$t Year", time()));
                $label = date("Y", strtotime($timeStart));
            }

            $filter = [
                "category" => $categoryID,
                "type" => $type,
                "timeStart" => $timeStart,
                "timeEnd" => $timeEnd
            ];

            $data = $this->build($filter);

            foreach($data['total'] as $k => $v) {
                if($v < 0) {
                    $data['total'][$k] = $v * -1;
                }
            }

            $results[] = ["total" => $data['total'][$type], "label" => $label];
            $t++;
        }

        Cache::forever($cache,$results);

        return response($results);
    }

    /**
     * get all specific category char
     * @param string $dataStart
     * @param string $dataEnd
     * @param string $type
     * 
     * @return response
     */
    public function getCategory(string $dataStart, string $dataEnd, string $type = "expenses")
    {
        $cache = "getCategory".sha1($dataStart.$dataEnd);
        if(Cache::has($cache)) {
            return Cache::get($cache);
        }

            $timeStart = $dataStart;
            $timeEnd = $dataEnd;
            $category = SubCategory::all();
            $response = [];

            foreach($category as $cat) {
                $filter = [
                    "category" => [$cat->id],
                    "type" => $type,
                    "timeStart" => $timeStart,
                    "timeEnd" => $timeEnd
                ];
    
                $data = $this->build($filter);

                if(!empty($data['total'][$type])) {
                    $data['total'][$type] = $data['total'][$type] * -1;
                    $response[] = [
                        "label" => $cat->name,
                        "total" => $data['total'][$type],
                        "id" => $cat->id
                    ];   
                }
               
            }

        Cache::forever($cache,$response);

        return response($response);
    }

    /**
     * get all specific label char
     * @param string $dataStart
     * @param string $dataEnd
     * @param string $type
     * 
     * @return response
     */
    public function getLabel(string $dataStart, string $dataEnd, string $type = "expenses")
    {
        $cache = "getlabel".sha1($dataStart.$dataEnd);
        if(Cache::has($cache)) {
            return Cache::get($cache);
        }

            $timeStart = $dataStart;
            $timeEnd = $dataEnd;
            $labels = \App\Models\Labels::all();

            $response = [];

            foreach($labels as $label) {
                $filter = [
                    "tags" => [$label->id],
                    "type" => $type,
                    "timeStart" => $timeStart,
                    "timeEnd" => $timeEnd
                ];
    
                $data = $this->build($filter);

                if(!empty($data['total'][$type])) {
                    $data['total'][$type] = $data['total'][$type] * -1;
                    $response[] = [
                        "label" => $label->name,
                        "total" => $data['total'][$type],
                        "id" => $label->id
                    ];   
                }
               
            }

        Cache::forever($cache,$response);

        return response($response);
    }


    /**
     * build find method
     * @param array $request
     * @return array
     */
    private function build(array $request)
    {

        //TODO: request validate
        $this->pagination = 0;

        $this->endDateTime = $request["timeStart"];
        $this->startDateTime = $request["timeEnd"];
        $entry = \App\Models\Entry::where("planned", 0);

        if (!empty($request["account"])) {
            $this->account = $request["account"];
        }

        if (!empty($request["category"])) {
            $this->category = $request["category"];
        }

        $type = [$request["type"]];
        if (empty($request["type"])) {
            $type = ["incoming", "expenses", "debit", "transfer"];
        }

        $this->type = $type;
        if (!empty($request["tags"])) {
            $this->tags = $request["tags"];
        }

        $entries = $this->get($entry);

        $total = 0;
        if (!empty($entries)) {
            $total = $this->getTotalType($type, $entries);
        }
        $response = ["data" => $entries, "total" => $total];

        return $response;
    }
}
