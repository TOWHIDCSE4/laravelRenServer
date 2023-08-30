<?php

namespace App\Helper;


class ShipperHelper
{
    static function handleFile()
    {
        $shippers = config('saha.shipper.list_shipper');
        return $shippers;
    }

    static function getNameShipper($id)
    {
        $shippers = ShipperHelper::handleFile();
        foreach($shippers as $shipper) {
            if($shipper['id'] === $id) {
                return $shipper['name'];
            }
        }
        return null;
    }

}
