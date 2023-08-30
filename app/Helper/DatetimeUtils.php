<?php

namespace App\Helper;

use Carbon\Carbon;

class DatetimeUtils
{

    static function getNow($place = 'Asia/Ho_Chi_Minh')
    {
        $carbon = Carbon::now($place);

        return $carbon;
    }
}
