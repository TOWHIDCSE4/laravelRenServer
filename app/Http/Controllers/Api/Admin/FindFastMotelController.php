<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\StatusFindFastMotelDefineCode;
use App\Http\Controllers\Controller;
use App\Models\findFastMotel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FindFastMotelController extends Controller
{
    /**
     * 
     * Danh sách find fast motel
     * 
     */
    public function getAll(Request $request)
    {
        $listFindFastMotel = findFastMotel::when($request->status != null, function ($query) use ($request) {
            $query->where('status', $request->status);
        })
            ->when(request('has_wc') != null, function ($query) {
                $query->where('has_wc', filter_var(request('has_wc'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_wifi') != null, function ($query) {
                $query->where('has_wifi', filter_var(request('has_wifi'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_park') != null, function ($query) {
                $query->where('has_park', filter_var(request('has_park'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_window') != null, function ($query) {
                $query->where('has_window', filter_var(request('has_window'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_security') != null, function ($query) {
                $query->where('has_security', filter_var(request('has_security'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_free_move') != null, function ($query) {
                $query->where('has_free_move', filter_var(request('has_free_move'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_own_owner') != null, function ($query) {
                $query->where('has_own_owner', filter_var(request('has_own_owner'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_air_conditioner') != null, function ($query) {
                $query->where('has_air_conditioner', filter_var(request('has_air_conditioner'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_water_heater') != null, function ($query) {
                $query->where('has_water_heater', filter_var(request('has_water_heater'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_kitchen') != null, function ($query) {
                $query->where('has_kitchen', filter_var(request('has_kitchen'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_fridge') != null, function ($query) {
                $query->where('has_fridge', filter_var(request('has_fridge'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_washing_machine') != null, function ($query) {
                $query->where('has_washing_machine', filter_var(request('has_washing_machine'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mezzanine') != null, function ($query) {
                $query->where('has_mezzanine', filter_var(request('has_mezzanine'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_wardrobe') != null, function ($query) {
                $query->where('has_wardrobe', filter_var(request('has_wardrobe'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_tivi') != null, function ($query) {
                $query->where('has_tivi', filter_var(request('has_tivi'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_pet') != null, function ($query) {
                $query->where('has_pet', filter_var(request('has_pet'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_balcony') != null, function ($query) {
                $query->where('has_balcony', filter_var(request('has_balcony'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_finger_print') != null, function ($query) {
                $query->where('has_finger_print', filter_var(request('has_finger_print'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_kitchen_stuff') != null, function ($query) {
                $query->where('has_kitchen_stuff', filter_var(request('has_kitchen_stuff'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_table') != null, function ($query) {
                $query->where('has_table', filter_var(request('has_table'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_picture') != null, function ($query) {
                $query->where('has_picture', filter_var(request('has_picture'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_decorative_lights') != null, function ($query) {
                $query->where('has_decorative_lights', filter_var(request('has_decorative_lights'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_tree') != null, function ($query) {
                $query->where('has_tree', filter_var(request('has_tree'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_pillow') != null, function ($query) {
                $query->where('has_pillow', filter_var(request('has_pillow'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mattress') != null, function ($query) {
                $query->where('has_mattress', filter_var(request('has_mattress'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_shoes_rasks') != null, function ($query) {
                $query->where('has_shoes_rasks', filter_var(request('has_shoes_rasks'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_curtain') != null, function ($query) {
                $query->where('has_curtain', filter_var(request('has_curtain'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mirror') != null, function ($query) {
                $query->where('has_mirror', filter_var(request('has_mirror'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_sofa') != null, function ($query) {
                $query->where('has_sofa', filter_var(request('has_sofa'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_ceiling_fans') != null, function ($query) {
                $query->where('has_ceiling_fans', filter_var(request('has_ceiling_fans'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->OrderBy('created_at', 'desc')
            ->paginate(request('limit') == null ? 20 : request('limit'));

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listFindFastMotel,
        ]);
    }

    /**
     * 
     * Thêm find fast motel
     * 
     */
    public function create(Request $request)
    {
        if (empty($request->name)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                '400' => MsgCode::NAME_IS_REQUIRED[1],
            ]);
        }

        // $serviceSellNameExists = findFastMotel::where('name', $request->name)->first();

        // if ($serviceSellNameExists  != null) {
        //     return ResponseUtils::json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
        //     ]);
        // }


        $created =   findFastMotel::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'status' => StatusFindFastMotelDefineCode::NOT_CONSULT,
            "province" => $request->province,
            "district" => $request->district,
            "wards" => $request->wards,
            "price" => $request->price,
            "capacity" => $request->capacity,
            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            "address_detail" => $request->address_detail,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>    $created
        ]);
    }

    /**
     * Cập nhật 1 dịch vụ
     * 
     */
    public function update(Request $request)
    {
        $find_fast_motel_id = request("find_fast_motel_id");

        $findFastMotelExist = findFastMotel::where(
            'id',
            $find_fast_motel_id
        )->first();

        if ($request->status != null) {
            if (StatusFindFastMotelDefineCode::getStatusMotelCode($request->status, false) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STATUS_FIND_FAST_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_STATUS_FIND_FAST_MOTEL_EXISTS[1],
                ]);
            }
        }

        if ($findFastMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[1],
            ]);
        }

        $findFastMotelExist->update([
            'name' => $request->name ?? $findFastMotelExist->name,
            'phone_number' =>  $request->phone_number ?? $findFastMotelExist->phone_number,
            'status' =>  $request->status ?? $findFastMotelExist->status,
            "province" =>  $request->province ?? $findFastMotelExist->province,
            "district" =>  $request->district ?? $findFastMotelExist->district,
            "wards" =>  $request->wards ?? $findFastMotelExist->wards,
            "note" =>  $request->note ?? $findFastMotelExist->note,
            "price" =>  $request->price ?? $findFastMotelExist->price,
            "capacity" =>  $request->capacity ?? $findFastMotelExist->capacity,
            "province_name" =>  Place::getNameProvince($request->province) ?? $findFastMotelExist->province_name,
            "district_name" =>  Place::getNameDistrict($request->district) ?? $findFastMotelExist->district_name,
            "wards_name" =>  Place::getNameWards($request->wards) ?? $findFastMotelExist->wards_name,
            "address_detail" =>  $request->address_detail ?? $findFastMotelExist->address_detail,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $findFastMotelExist
        ]);
    }

    /**
     * Thong tin 1 dịch vụ
     * 
     */
    public function getOne(Request $request)
    {

        $find_fast_motel = request("find_fast_motel_id");

        $findFastMotelExist = findFastMotel::where(
            'id',
            $find_fast_motel
        )->first();

        if ($findFastMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $findFastMotelExist,
        ]);
    }

    /**
     * Xóa 1 dịch vụ
     * 
     */
    public function delete(Request $request)
    {

        $find_fast_motel = request("find_fast_motel_id");

        $findFastMotelExist = findFastMotel::where(
            'id',
            $find_fast_motel
        )->first();

        if ($findFastMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[1],
            ]);
        }

        $idDeleted = $findFastMotelExist->id;
        $findFastMotelExist->delete();



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
