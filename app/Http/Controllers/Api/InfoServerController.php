<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

/**
 * @group  Admin/Thông tin server
 */
class InfoServerController extends Controller
{
    /**
     * Thông tin server
     */
    public function info(Request $request)
    {
        $mytime = Carbon::now();
        echo $mytime->toDateTimeString();
        $dt = new DateTime();
        echo $dt->format('Y-m-d H:i:s');
        phpinfo();
    }
}
