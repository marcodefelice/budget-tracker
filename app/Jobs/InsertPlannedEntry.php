<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Entry;
use App\Models\PlannedEntries;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpParser\Node\Stmt\Catch_;

class InsertPlannedEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        Log::info("Start planned entry JOB");
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->insertEntry($this->getPlannedEntry());
        \App\Http\Controllers\OperationsController::cleanPlannedEntries();
        Cache::tags(["entry","search","stats"])->flush();

    }

    /**
     * get planned entry from date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPlannedEntry()
    {
        $date = date("Y-m-d H:i:s", time());
        $newDate = date("Y-m-d", strtotime($date . "+1 month"));

        $entry = PlannedEntries::where("created_at", "<=", $newDate)->get();
        Log::info("Found " . $entry->count() . " of new entry to insert");
        return $entry;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @return void
     */
    private function insertEntry(\Illuminate\Database\Eloquent\Collection $data)
    {
        try {
            foreach ($data as $request) {
                Log::info("INSERT:: " . json_encode($request));
                $entry = new Entry();

                $paymentType = $request->payment_type;
                $account = $request->account_id;
                $currency = $request->currency_id;
                $category = $request->category_id;

                $entry->uuid = uniqid();

                $entry->amount = $request->amount;
                $entry->note = $request->note;
                $entry->type = $request->type;
                $entry->transfer = 0;
                $entry->waranty = 0;
                $entry->confirmed = $request->confirmed;
                $entry->category_id = $category;
                $entry->payment_type = $paymentType;
                $entry->account_id = $account;
                $entry->currency_id = $currency;
                $entry->planned = 1;
                $entry->created_at = $request->created_at;

                $entry->save();

            }

            $this->updatePlanningEntry($data);

        } catch (Exception $e) {
            Log::critical("Unable to insert new planned entry " . $e);
        }
    }

    /**
     * update planning entry to next data
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @return void
     */
    private function updatePlanningEntry(\Illuminate\Database\Eloquent\Collection $data) {
        foreach($data as $e) {
            switch($e->planning) {
                case "daily":
                    $increment = "+1 Day";
                    break;
                    case "monthly":
                    $increment = "+1 Month";
                    break;
                    case "yearly":
                    $increment = "+1 Year";
                    break;
                    default:
                    $increment = "+0 Day";
                    break;
            }

            $date = date("Y-m-d",strtotime($increment, strtotime($e->created_at)));
            Log::info("Changed planned date ID: ".$e->id. " ".$e->created_at." --> ".$date);
            $e->updated_At = date("Y-m-d h:i:s",time());
            $e->created_at = $date;
            $e->save();
        }
    }
}
