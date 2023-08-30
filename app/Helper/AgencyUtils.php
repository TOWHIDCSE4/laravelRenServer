<?php

namespace App\Helper;

use App\Models\Agency;

class AgencyUtils
{

    static function isAgency()
    {
        $customer = request('customer', $default = null);

        if ($customer != null) {
            $agency = Agency::where('customer_id',    $customer->id)->first();


            if ($agency == null) return false;

            if ($agency->status != 1) {
                return false;
            }
            return true;
        }
        return false;
    }
}
