<?php

namespace App\Utils;

use App\Helper\Helper;
use App\Models\Product;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class ProductTopSale
{

    static function getTopSaleProductIds($store_id)
    {

        $after_res = DB::table('products')->where(
            'store_id',
            $store_id
        )->select('id', 'store_id', 'sold')
            ->where(
                'status',
                '<>',
                1
            )
            ->where(
                'sold',
                '>',
               0
            )
            ->orderBy('sold', 'desc')->take(20)->get();


        $pluck_ids = [];
        $po = 0;
        foreach ($after_res as $pro) {
            array_push($pluck_ids, $pro->id);
            $po++;
        }

        return  $pluck_ids;
    }
}
