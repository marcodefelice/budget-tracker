<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;
use Google\Client;
use Google\Service\Drive;
use Exception;

class GoogleDriveService
{

  public function init()
  {
    // Get the API client and construct the service object.
    $google = new GoogleDriveService();
    $client = $google->getClient();
    $service = new Drive($client);

    $optParams = array(
        'pageSize' => 10,
        'fields' => 'files(id,name,mimeType)',
        'q' => 'mimeType = "application/vnd.google-apps.folder" and "root" in parents',
        'orderBy' => 'name'
    );
    $results = $service->files->listFiles($optParams);
    $files = $results->getFiles();
    var_dump($files);die;

    // Print the names and IDs for up to 10 files.
    $optParams = array(
      'pageSize' => 10,
      'fields' => 'nextPageToken, files(id, name)'
    );
    $results = $service->files->listFiles($optParams);

    if (count($results->getFiles()) == 0) {
        print "No files found.\n";
    } else {
        print "Files:\n";
        foreach ($results->getFiles() as $file) {
            printf("%s (%s)\n", $file->getName(), $file->getId());
        }
    }
    die;
  }
  
  /**
   * Returns an authorized API client.
   * @return Google_Client the authorized client object
   */
  function getClient()
  {
    $client = new Client();
    $client->setApplicationName('Google Drive API PHP Quickstart');
    $client->setScopes('https://www.googleapis.com/auth/drive.readonly');
    $client->setAuthConfig(public_path('credentials.json'));
    $client->setAccessType('offline');
    $client->setRedirectUri("http://127.0.0.1");
    $client->setPrompt('select_account consent');

    return $client;
  }

}
