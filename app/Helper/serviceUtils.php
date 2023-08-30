<?php

namespace App\Helper;

use App\Models\User;
use App\Helper\ServiceUnitDefineCode;

class serviceUtils
{

    static function calculateServiceCloseItem($serviceTypeUnit, $serviceCharge, $newQuantity, $oldQuantity = 0)
    {
        if ($serviceTypeUnit == ServiceUnitDefineCode::SERVICE_INDEX) {
            return $serviceCharge * ($newQuantity - $oldQuantity);
        } else if ($serviceTypeUnit == ServiceUnitDefineCode::BY_QUANTITY) {
            return $serviceCharge * $newQuantity;
        } else if ($serviceTypeUnit == ServiceUnitDefineCode::PER_MOTEL) {
            return $serviceCharge * $newQuantity;
        } else if ($serviceTypeUnit == ServiceUnitDefineCode::PER_USE) {
            return $serviceCharge * $newQuantity;
        } else if ($serviceTypeUnit == ServiceUnitDefineCode::ANOTHER) {
            return $serviceCharge * $newQuantity;
        } else if ($serviceTypeUnit == ServiceUnitDefineCode::NOT_CAL) {
            return $serviceCharge * $newQuantity;
        }
        return 0;
    }
}
