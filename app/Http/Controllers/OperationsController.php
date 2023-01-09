<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Http\Controllers\Controller;

class OperationsController {

    public static function cleanPlannedEntries() {

        $today = time();
        Entry::where("created_at","<=",date("Y-m-d",$today))->where("planned",1)->update(
            [
                "planned" => 0
            ]
        );

    }

    public static function rand_color() {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}