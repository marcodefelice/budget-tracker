<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Managers\CategoryManagerController;
use Exception;

class CredemService extends DropboxService
{
  public $account = null;
  public $labels = [];

  private $fileOpen;
  private $fileName;
  private $fileToImport = null;
  private $dropbox;

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
    "custom_category"
  ];

  const FILE_PATH = "/CREDEM";

  const HEADER_CREDEM_CSV = [
    "Data contabile",  "Data valuta",  "Importo",  "Importo orig.",  "Divisa orig.",  "Canale",  "Categoria",  "Causale ABI",  "Causale",  "Descrizione:",  "Note"
  ];

  public function __construct($dropbox = true)
  {
    $this->dropbox = $dropbox;

    if (!$this->isConnect()) {
      Log::error("Unable to connect a DropBox");
    }

    parent::__construct(self::FILE_PATH);
    $this->fileName = "/credem_" . time() . ".csv";
  }

  public function handle()
  {
    $init = $this->init();
    $this->fileToImport = $init;
    if ($init !== false) {
      $filePath = storage_path();

      $this->createCsv($filePath);

      if ($this->dropbox === true) $this->moveFile();
      Log::info("Finish CREDEM process ####");
      if ($this->dropbox === true) unlink(storage_path() . $this->fileName);
      return true;
    }
    Log::warning("No csv file found to import");
    return false;
  }

  /*
  * open file
  * @return void
  */
  private function createFile()
  {
    $path = storage_path();
    $this->fileOpen = fopen($path . $this->fileName, "w")  or die("Unable to open file!");
  }

  /*
  * create a csv
  * @return void
  */
  private function createCsv(string $filePath)
  {
    $file = $this->getLastFile();
    if ($file === false) {
      Log::warning("No file csv foud for credem service");
      die("No file csv foud for credem service");
    }
    //now read a CSV
    $file_handle = fopen($filePath . "/" . $file, "r");
    while (!feof($file_handle)) {
      $line_of_text[] = fgetcsv($file_handle, 0, ";");
    }

    if ($this->checkFileCsv($line_of_text[0]) === false) {
      Log::warning("Header csv is not correct");
      return false;
    }

    $this->createFile();
    array_shift($line_of_text);
    $this->putCSV($line_of_text);
    $this->writefile(storage_path() . $this->fileName);

    fclose($file_handle);
  }

  /**
   * put data into new csv
   * @param array $data
   * @return void
   */
  protected function putCSV(array $data)
  {
    fputcsv($this->fileOpen, self::HEADER_CSV, ";");
    foreach ($data as $value) {
      Log::info("INSERT INTO CSV " . json_encode($value));
      try {
        $importo = str_replace(".", "", $value[2]);
        $importo = str_replace(",", ".", $importo);

        $categoryManager = new CategoryManagerController();
        $category = $categoryManager->getCategoryIdFromAction($value[9]);

        $labels = str_replace(",","|",$this->labels);
        if(empty($labels)) {
          $labels = $categoryManager->getLabelIdFromAction($value[9]);
        }

        $csv = [
          "account" => empty($this->account) ? "CREDEM" : $this->account->name,
          "category" => $category,
          "currency" => env("CREDEM_CURRENCY", "euro"),
          "amount" => $importo,
          "ref_currency_amount" => null,
          "type" => ($importo <= 0) ? "expenses" : "incoming",
          "payment_type" => env("CREDEM_PAYMENT_TYPE", "carta di credito"),
          "payment_type_local" => null,
          "note" => $value[9],
          "date" => $value[0],
          "gps_latitude" => null,
          "gps_longitude" => null,
          "gps_accuracy_in_meters" => null,
          "warranty_in_month" => 0,
          "transfer"  => 0,
          "payee"  => 0,
          "labels"  => $labels,
          "envelope_id"  => null,
          "custom_category"  => 0
        ];
        fputcsv($this->fileOpen, $csv, ";");
      } catch (Exception $e) {
        Log::error($e);
      }
    }

    fclose($this->fileOpen);
  }

  /**
   * return last file
   * @return array
   */
  private function getLastFile()
  {
    $files = [$this->fileToImport]; //Storage::disk("storage")->files();
    return end($files);
  }

  /**
   * check if csv file header is correct
   * @return boolean
   */
  private function checkFileCsv(array $header)
  {
    foreach ($header as $key => $value) {
      if (!in_array($value, self::HEADER_CREDEM_CSV)) {
        return false;
      }
    }
    return true;
  }

  public function __decostructor()
  {
  }
}
