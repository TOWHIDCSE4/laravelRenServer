<?php

namespace App\Utils;

use App\Helper\Helper;
use App\Models\Product;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class ProductNew
{

    static function getNewProductIds($store_id)
    {
        // $nowDateTime = Helper::getTimeNowDateTime()->modify('-5 day')->format('Y-m-d');
        // $ids =  Product::select('id')->where('store_id', $store_id)
        //     ->where('created_at', '>=',  $nowDateTime)
        //     ->orderBy('created_at','desc')
        //     ->get()->pluck("id")->toArray();

        $ids =  DB::table('products')->select('id')->orderBy('id', 'desc')->where(
            'status',
            0
        )->where('store_id', $store_id)->take(15)->get()->pluck('id')->toArray();

        return $ids;
    }
}
