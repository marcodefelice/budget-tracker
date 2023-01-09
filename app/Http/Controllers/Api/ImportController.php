<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Services\CredemService;
use App\Http\Services\AmexService;
use App\Http\Services\ImportService;


class ImportController extends Controller
{
    private $importService;

    public function __construct()
    {

    }
    /**
     *  import function
     */
    public function import($request)
    {
        // TODO: VALIDATE
        $request->file->storeAs("import",'file.csv',"storage");
        switch($request->service) {
            case "CREDEM BANCA":
                $this->importService = new CredemService(false);
                break;
            case "AMEX":
                $this->importService = new AmexService(false);
                break;
        }

        if(!empty($request->account)) {
            $this->importService->account = \App\Models\Account::findOrFail($request->account);
        }

        if(!empty($request->label)) {
            $this->importService->labels = $request->label;
        }

        $this->importService->handle();

        $import = new ImportService(false);
        $import->handle();

        return response("ok",200);
    }
}
