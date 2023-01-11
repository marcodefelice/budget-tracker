<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;
use Google\Service\Drive;
use App\Http\Services\DropboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Entry;
use App\Models\SubCategory;
use App\Models\PaymentsTypes;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Labels;
use Illuminate\Support\Facades\Cache;
use Exception;
use DateTime;

class ImportService extends DropboxService
{

  const COLORS = [
    "bg-blueGray-200 text-blueGray-600",
    "bg-red-200 text-red-600",
    "bg-orange-200 text-orange-600",
    "bg-amber-200 text-amber-600",
    "bg-teal-200 text-teal-600",
    "bg-lightBlue-200 text-lightBlue-600",
    "bg-indigo-200 text-indigo-600",
    "bg-purple-200 text-purple-600",
    "bg-pink-200 text-pink-600",
    "bg-emerald-200 text-emerald-600 border-white",
  ];

  const HEADER_CSV = [
    "account",
    "category",
    "currency",
    "amount",
    "ref_currency_amount",
    "type",
    "payment_type",
    "payment_type_local",
    "note",
    "date",
    "gps_latitude",
    "gps_longitude",
    "gps_accuracy_in_meters",
    "warranty_in_month",
    "transfer",
    "payee",
    "labels",
    "envelope_id",
    "custom_category",
    "created_at"
  ];

  public $filePath = null;

  /* @var \App\Models\Entry */
  private $items = null;

  public function __construct($dropbox = true)
  {
    $this->dropbox = $dropbox;
  }

  public function handle()
  {

    $init = $this->init();
    if ($init !== false) {
      $file = $this->getLastFile();
      $this->filePath = storage_path($file);

      $this->getLastItem();
      if ($this->saveData($this->readCsv()) === false) {
        Log::error("error while insert method");
      };
      if ($this->dropbox === true) $this->moveFile();
      Cache::flush();
      Log::info("Flush cache");
      Log::info("Finish process ####");
      Cache::tags(["entry","search","labels","stats"])->flush();
      return true;
    }
    Log::warning("No csv file found to import");
    return false;
  }

  /**
   * Convert CSV into ARRAY
   * @return array
   */
  private function readCsv()
  {
    $file_handle = fopen($this->filePath, 'r');
    while (!feof($file_handle)) {
      $line_of_text[] = fgetcsv($file_handle, 0, ";");
    }
    fclose($file_handle);
    unlink($this->filePath);
    //check if file header is correct
    if ($this->checkFileCsv($line_of_text[0]) === false) {
      return false;
    }

    return $line_of_text;
  }

  /**
   * Save data into DB
   * @param array $data
   * @return void
   */
  private function saveData(array $data)
  {
    try {

      foreach ($data as $key => $value) {

        if ($key != 0 && !empty($value)) {

          $amount = str_replace(",", ".", $value[3]);

          $category = SubCategory::where("name", strtolower($value[1]))->first();
          $paymentType = PaymentsTypes::where("name", strtolower($value[6]))->first();
          $account = Account::where("name", strtolower($value[0]))->first();
          $currency = Currency::where("name", strtolower($value[2]))->first();

          if (empty($account)) {
            $account = new Account();
            $account->uuid = uniqid();
            $account->name = strtolower($value[0]);
            $account->save();
          }

          if (empty($paymentType)) {
            $paymentType = new PaymentsTypes();
            $paymentType->uuid = uniqid();
            $paymentType->name = strtolower($value[6]);
            $paymentType->save();
          }

          if (empty($currency)) {
            $currency = new Currency();
            $currency->uuid = uniqid();
            $currency->name = strtolower($value[2]);
            $currency->save();
          }

          $data = Entry::with("subCategory")
            ->with("account")
            ->with("label")->with("currency")->where("account_id", $account->id)->where("amount", (float) $amount)->get()->first();

          $not_exist = empty($data);
          $toupdate = false;

          if ($not_exist === true) {
            $db = new Entry();
            $db->uuid = uniqid();
            $method = "Insert ";
          } else {
            $db = $data;
            $toupdate = $this->checkUpdateData($data, $value);
            $method = "Update ";
          }

          if (empty($category)) {
            $category = SubCategory::where("name", "altro")->first();
          }

          $db->amount = $amount;
          $db->note = $value[8];
          $db->type = $amount <= 0 ? "expenses" : "incoming";
          $db->transfer = $value[14] == "true" ? 1 : 0;
          $db->waranty = $value[13] == "true" ? 1 : 0;
          $db->confirmed = 1;
          $db->planned = 0;

          $db->payment_type = $paymentType->id;
          $db->account_id = $account->id;
          $db->currency_id = $currency->id;
          $db->created_at = $value[9];

          if ($toupdate === false) {
            $db->category_id = $category->id;
          }

          $db->save();

          $labels = [];
          $tags = explode("|", $value[16]);
          foreach ($tags as $key => $value) {
            $label = Labels::where("name", strtolower($value))->first();
            if (empty($label)) {
              $label = new Labels();
              $label->uuid = uniqid();
              $label->name = strtolower($value);
              $label->color = self::COLORS[rand(0, 9)];
              $label->save();
            }
            $labels[] = $label->id;

            if (!empty($labels)) {
              $db->label()->detach();
              $db->label()->attach($labels);
            }
          }

          Entry::where("transfer", 1)->update([
            "type" => "transfer"
          ]);

          Log::info($method . json_encode($db));
        }
      }
    } catch (Exception $e) {
      Log::error($e);
    }
  }

  /**
   * check if csv file header is correct
   * @return boolean
   */
  private function checkFileCsv(array $header)
  {
    foreach ($header as $key => $value) {
      if (!in_array($value, self::HEADER_CSV)) {
        Log::warning("Header csv is not correct missing " . $value);
        return false;
      }
    }
    return true;
  }

  /**
   * get last item
   *
   * @return \App\Models\Entry
   */
  private function getLastItem()
  {
    if (empty($this->items)) {
      $db = DB::table('entries')->orderBy('created_at', 'desc')->first();
      $this->items = $db;
    }
    return $this->items;
  }

  /**
   * check if date is passed
   * @param string $date
   * @param string $fromDate
   * @return bool
   */
  protected function checkPassedDate(string $date, string $fromDate = null)
  {
    //check if is a new data
    $datex = strtotime($date);
    $now = empty($fromDate) ? time() : strtotime($fromDate);
    if ($datex < $now) {
      return false;
    }
    return true;
  }

  /**
   * return last file
   * @return array
   */
  private function getLastFile()
  {
    $files = Storage::disk("storage")->files();
    return end($files);
  }

  protected function checkUpdateData(mixed $data, array $value)
  {
    Log::debug("CHECK UPDATE: " . json_encode($data->toArray()));
    if ($value[3] != $data->amount) {
      return true;
    }

    if ($value[0] != $data->account->name) {
      return true;
    }

    if ($value[3] != $data->currency->name) {
      return true;
    }

    if ($value[8] != $data->note) {
      return true;
    }

    return false;
  }
}
