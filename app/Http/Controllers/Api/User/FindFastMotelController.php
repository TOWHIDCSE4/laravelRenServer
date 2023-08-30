<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\NotiUserDefineCode;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationAdminJob;
use App\Models\findFastMotel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
        DB::table('renters')
            ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
            ->where('user_contracts.contract_id', '')
            ->delete();
        if (empty($request->name)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ]);
        }

        if (empty($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_IS_REQUIRED[0],
                'msg' => MsgCode::PHONE_NUMBER_IS_REQUIRED[1],
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
            'status' => 0,
            "province" => $request->province,
            "district" => $request->district,
            "wards" => $request->wards,
            "note" => $request->note,
            "price" => $request->price,
            "capacity" => $request->capacity,
            "province_name" => Place::getNameProvince($request->province),
            "district_name" => Place::getNameDistrict($request->district),
            "wards_name" => Place::getNameWards($request->wards),
            "address_detail" => $request->address_detail,
            "has_wifi" => $request->has_wifi,
            "has_park" => $request->has_park,
            "has_wc" => $request->has_wc,
            "has_window" => $request->has_window,
            "has_security" => $request->has_security,
            "has_free_move" => $request->has_free_move,
            "has_own_owner" => $request->has_own_owner,
            "has_air_conditioner" => $request->has_air_conditioner,
            "has_water_heater" => $request->has_water_heater,
            "has_kitchen" => $request->has_kitchen,
            "has_fridge" => $request->has_fridge,
            "has_washing_machine" => $request->has_washing_machine,
            "has_mezzanine" => $request->has_mezzanine,
            "has_bed" => $request->has_bed,
            "has_wardrobe" => $request->has_wardrobe,
            "has_tivi" => $request->has_tivi,
            "has_pet" => $request->has_pet,
            "has_balcony" => $request->has_balcony,
            "has_finger_print" => $request->has_finger_print,
            "has_kitchen_stuff" => $request->has_kitchen_stuff,
            "has_table" => $request->has_table,
            "has_picture" => $request->has_picture,
            "has_decorative_lights" => $request->has_decorative_lights,
            "has_tree" => $request->has_tree,
            "has_pillow" => $request->has_pillow,
            "has_mattress" => $request->has_mattress,
            "has_shoes_rasks" => $request->has_shoes_rasks,
            "has_curtain" => $request->has_curtain,
            "has_mirror" => $request->has_mirror,
            "has_sofa" => $request->has_sofa,
            "has_ceiling_fans" => $request->has_ceiling_fans
        ]);

        NotificationAdminJob::dispatch(
            null,
            "Thông báo tìm phòng nhanh",
            'Có một yêu cầu tư vấn tìm phòng',
            TypeFCM::NEW_FIND_FAST_MOTEL,
            NotiUserDefineCode::USER_IS_ADMIN,
            $created->id,
        );


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

        if ($findFastMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_FIND_FAST_MOTEL_EXISTS[1],
            ]);
        }

        $findFastMotelExist->update([
            'name' => $request->name ?? $request->name,
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
            "has_wifi" => $request->has_wifi,
            "has_park" => $request->has_park,
            "has_wc" => $request->has_wc,
            "has_window" => $request->has_window,
            "has_security" => $request->has_security,
            "has_free_move" => $request->has_free_move,
            "has_own_owner" => $request->has_own_owner,
            "has_air_conditioner" => $request->has_air_conditioner,
            "has_water_heater" => $request->has_water_heater,
            "has_kitchen" => $request->has_kitchen,
            "has_fridge" => $request->has_fridge,
            "has_washing_machine" => $request->has_washing_machine,
            "has_mezzanine" => $request->has_mezzanine,
            "has_bed" => $request->has_bed,
            "has_wardrobe" => $request->has_wardrobe,
            "has_tivi" => $request->has_tivi,
            "has_pet" => $request->has_pet,
            "has_balcony" => $request->has_balcony,
            "has_finger_print" => $request->has_finger_print,
            "has_kitchen_stuff" => $request->has_kitchen_stuff,
            "has_table" => $request->has_table,
            "has_picture" => $request->has_picture,
            "has_decorative_lights" => $request->has_decorative_lights,
            "has_tree" => $request->has_tree,
            "has_pillow" => $request->has_pillow,
            "has_mattress" => $request->has_mattress,
            "has_shoes_rasks" => $request->has_shoes_rasks,
            "has_curtain" => $request->has_curtain,
            "has_mirror" => $request->has_mirror,
            "has_sofa" => $request->has_sofa,
            "has_ceiling_fans" => $request->has_ceiling_fans
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

        $find_fast_motel_id = request("find_fast_motel_id");

        $findFastMotelExist = findFastMotel::where(
            'id',
            $find_fast_motel_id
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
