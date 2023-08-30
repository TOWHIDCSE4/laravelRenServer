<?php

namespace App\Helper;

use App\Models\Agency;
use App\Models\Collaborator;
use App\Models\Customer;

class CollaboratorUtils
{

    static function isCollaborator($customer_id, $store_id)
    {
        $customer = Customer::where('id', $customer_id)->where('store_id', $store_id)->first();

        if ($customer != null) {
            if ($customer->is_collaborator == false) return false;

            $collaborator = Collaborator::where('customer_id',    $customer->id)->where('store_id', $store_id)->first();


            if ($collaborator == null) return false;

            if ($collaborator->status != 1) {
                return false;
            }
            return true;
        }
        return false;
    }
}
