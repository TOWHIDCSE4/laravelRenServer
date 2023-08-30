<?php

namespace App\Helper;

class ResponseUtils
{

    static function json($data)
    {
        return response()->json($data, $data["code"]);
    }
}
