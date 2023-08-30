<?php

namespace App\Helper;

use App\Models\Customer;

class CustomerUtils
{

    static function getCustomerPassersby($request)
    {
        $customerPasserBy = Customer::where('store_id', $request->store->id)
            ->where('is_passersby', true)->first();

        if ($customerPasserBy == null) {
            $customerPasserBy = Customer::create(
                [
                    'area_code' => '+84',
                    'name' => "Khách vãng lai",
                    'name_str_filter' => StringUtils::convert_name_lowercase("Khách vãng lai"),
                    'phone_number' => "----------",
                    'email' => "",
                    'password' => bcrypt('DOAPP_BCRYPT_PASS'),
                    'official' => true,
                    "is_passersby" => true
                ]
            );
        }

        return  $customerPasserBy;
    }
}
