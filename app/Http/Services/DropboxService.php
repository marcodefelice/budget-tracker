<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;
use Dcblogdev\Dropbox\Facades\Dropbox;
use Illuminate\Support\Facades\Storage;
use Exception;

class DropboxService
{

  public $filepath;
  private $file;

  public function __construct(string $filepath = "")
  {
    $this->filepath = $filepath;
  }

  public function init()
  {
    //first check if file already exist
    $file = $this->getLastFileFromDisk();
    if(!empty($file)) {
      return $file;
      die;
    }
    
    try{
      if($this->isConnect()) {
        $file = $this->getLastFile();
        $this->file = $file;
        if($file !== false) {
          Dropbox::files()->download($file["path_display"],storage_path("/"));
          Log::info("Found a file: ".json_encode($file));
          return true;
        }
        return false;
      }
    } catch(Exception $e) {
      Log::error($e);
      die("Error connecting to dropbox service");
    }

  }

  /**
  * return last file
  * @return array
  */
  private function getLastFileFromDisk() {
    $files = Storage::disk("storage")->files("import");
    return end($files);
  }

  /**
   * Returns an authorized API client.
   * @return boolean if dropbox is connected
   */
  function isConnect()
  {
    Dropbox::connect();
    if (!Dropbox::isConnected()) {
            return true;
        } else {
            //display your details
            return false;
        }
  }

  public function listFiles($path = null) {
    if(is_null($path))  { $path = $this->filepath; }
    return Dropbox::files()->listContents($path);
  }

  public function __decostructor() {
    Dropbox::disconnect('app/dropbox');
  }

  private function getLastFile() {
    $listFiles = $this->listFiles();
    $files = $listFiles['entries'];
    $last = end($files);
    if($last['.tag'] != "file") {
      return false;
    }
    return $last;
  }

  private function fileExist(string $name)
  {
    $list = $this->listFiles("");
    foreach ($list["entries"] as $key => $value) {
      if($value["path_display"] == $name) {
        return true;
      }
    }
    return false;
  }

  protected function moveFile() {
    $file = $this->file["name"];
    $fileOldPath = $this->file["path_display"];
    
    try{
      $directory = "/".date("Ym");
      if($this->isConnect()) {

        if(!$this->fileExist($directory)) {
          Dropbox::files()->createFolder($directory);
        }
        Dropbox::files()->move($fileOldPath,$directory."/".$file,true,false);
      }
    } catch(Excemption $e) {
      Log::error($e);
      die("Error connecting to dropbox service");
    }
  }

  public function writefile($file,string $filePath = "") {
    try{
      if($this->isConnect()) {
          Dropbox::files()->upload($filePath,$file);
        }
    } catch(Excemption $e) {
      Log::error($e);
      die("Error connecting to dropbox service");
    }
  }

}
