<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Managers\CategoryManagerController;
use Exception;

class AmexService extends DropboxService
{
  public $account = null;
  public $labels = [];

  private $fileOpen;
  private $fileName;

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

  const FILE_PATH = "/AMEX";

  const HEADER_AMEX_CSV = [
    "Data", "Descrizione", "Titolare", "Numero di Carta", "Importo", "Dettagli completi", "Compare sul tuo estratto conto come", "Indirizzo", "Città/Stato", "CAP", "Paese", "Riferimento"
  ];

  private $dropbox;
  private $fileToImport = null;

  public function __construct($dropbox = true)
  {
    $this->dropbox = $dropbox;

    if (!$this->isConnect()) {
      Log::error("Unable to connect a DropBox");
    }

    parent::__construct(self::FILE_PATH);
    $this->fileName = "/amex_" . time() . ".csv";
  }

  public function handle()
  {

    $init = $this->init();
    $this->fileToImport = $init;
    if ($init !== false) {
      $filePath = storage_path();
      $this->createCsv($filePath);
      if ($this->dropbox === true) $this->moveFile();
      Log::info("Finish AMEX process ####");
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
      Log::warning("No file csv foud for amex service");
      die("No file csv foud for amex service");
    }
    //now read a CSV
    $file_handle = fopen($filePath . "/" . $file, "r");
    while (!feof($file_handle)) {
      $line_of_text[] = fgetcsv($file_handle, 0, ";");
    }

    if ($this->checkFileCsv($line_of_text[0]) === false) {
      Log::warning("Header csv is not correct");
      die("Header csv is not correct");
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
    foreach ($data as $key => $value) {
      Log::info("INSERT INTO CSV " . json_encode($value));
      try {
        $importo = str_replace(".", "", $value[4]);
        $importo = str_replace(";", ",", $value[4]);
        $importo = str_replace(",", ".", $importo);

        $importo = $importo * -1;

        $categoryManager = new CategoryManagerController();
        $category = $categoryManager->getCategoryIdFromAction($value[1]);

        $labels = implode("|",$this->labels);
        if(empty($labels)) {
          $labels = $categoryManager->getLabelIdFromAction($value[1]);
        }

        $csv = [
          "account" => empty($this->account) ? "AMEX" . $value[3] : $this->account->name,
          "category" => $category,
          "currency" => env("AMEX_CURRENCY", "euro"),
          "amount" => $importo,
          "ref_currency_amount" => null,
          "type" => ($importo <= 0) ? "expenses" : "incoming",
          "payment_type" => env("AMEX_PAYMENT_TYPE", "carta di credito"),
          "payment_type_local" => null,
          "note" => $value[1] . " ( " . $value[2] . " )",
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
      if (!in_array($value, self::HEADER_AMEX_CSV)) {
        return false;
      }
    }
    return true;
  }

  public function __decostructor()
  {
  }
}
