<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helper\Place;

/**
 * @group  Nơi chốn
 */
class PlaceController extends Controller
{

    /**
     * Lấy danh sách vùng
     * @urlParam  type required mục cần lấy (  province(tỉnh,thành phố) | district(quận,huyện) | wards(phường,xã))
     * @urlParam  parent_id required id mục cha, riêng province có thể không cần
     */
    public function getWithType(Request $request)
    {

   
        
        $type = $request->route()->parameter('type');
        $parent_id = (int)$request->route()->parameter('parent_id');

        $data = array();


        if ($type == "province") {
            $data  =  Place::getListProvince(0);
        }

        if ($type == "district") {
            $data  =  Place::getListDistrict($parent_id);
        }

        if ($type == "wards") {
            $data  =  Place::getListWards($parent_id);
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' =>  $data,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);

    }
}
