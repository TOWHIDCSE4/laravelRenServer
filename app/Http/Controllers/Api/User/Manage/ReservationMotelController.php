<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\StatusReservationMotelDefineCode;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationUserJob;
use App\Models\MsgCode;
use App\Models\ReservationMotel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReservationMotelController extends Controller
{
    /**
     * 
     * Danh sách reservation motel
     * 
     */
    public function getAll(Request $request)
    {
        $listReservationMotel = ReservationMotel::where('host_id', $request->user->id)
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->OrderBy('created_at', 'desc')
            ->paginate(request('limit') == null ? 20 : request('limit'));

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listReservationMotel,
        ]);
    }

    /**
     * 
     * Thêm reservation motel
     * 
     */
    public function create(Request $request)
    {
        if (!DB::table('mo_posts')->where('id', $request->mo_post_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        if (empty($request->name)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                '400' => MsgCode::NAME_IS_REQUIRED[1],
            ]);
        }

        // $serviceSellNameExists = reservationMotel::where('name', $request->name)->first();

        // if ($serviceSellNameExists  != null) {
        //     return ResponseUtils::json([
        //         'code' => 404,
        //         'success' => false,
        //         'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
        //     ]);
        // }


        $created =   reservationMotel::create([
            'name' => $request->name,
            'mo_post_id' => $request->mo_post_id,
            'host_id' => $request->user->id,
            'phone_number' => $request->phone_number,
            'status' => StatusReservationMotelDefineCode::NOT_CONSULT,
            "province" => $request->province,
            "district" => $request->district,
            "wards" => $request->wards,
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
        $reservationMotelId = request("reservation_motel_id");

        $reservationMotelExist = reservationMotel::where(
            'id',
            $reservationMotelId
        )->first();

        if ($request->status != null) {
            if (StatusReservationMotelDefineCode::getStatusReservationMotelCode($request->status) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STATUS_RESERVATION_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_STATUS_RESERVATION_MOTEL_EXISTS[1],
                ]);
            }
        }

        if ($reservationMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[1],
            ]);
        }

        if ($request->mo_post_id != null) {
            if (!DB::table('mo_posts')->where('id', $request->mo_post_id)->exists()) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_MOTEL_EXISTS[1],
                ]);
            }
        }

        $reservationMotelExist->update([
            'name' => $request->name ?? $reservationMotelExist->name,
            'host_id' => $request->host_id ?? $reservationMotelExist->host_id,
            'mo_post_id' => $request->mo_post_id ?? $reservationMotelExist->mo_post_id,
            'phone_number' =>  $request->phone_number ?? $reservationMotelExist->phone_number,
            'status' =>  $request->status ?? $reservationMotelExist->status,
            "province" =>  $request->province ?? $reservationMotelExist->province,
            "district" =>  $request->district ?? $reservationMotelExist->district,
            "wards" =>  $request->wards ?? $reservationMotelExist->wards,
            "note" =>  $request->note ?? $reservationMotelExist->note,
            "province_name" =>  Place::getNameProvince($request->province) ?? $reservationMotelExist->province_name,
            "district_name" =>  Place::getNameDistrict($request->district) ?? $reservationMotelExist->district_name,
            "wards_name" =>  Place::getNameWards($request->wards) ?? $reservationMotelExist->wards_name,
            "address_detail" =>  $request->address_detail ?? $reservationMotelExist->address_detail,
        ]);


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reservationMotelExist
        ]);
    }

    /**
     * Thong tin 1 dịch vụ
     * 
     */
    public function getOne(Request $request)
    {

        $reservationMotelId = request("reservation_motel_id");

        $reservationMotelExist = reservationMotel::where(
            'id',
            $reservationMotelId
        )->where('host_id', $request->user->id)->first();

        if ($reservationMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reservationMotelExist,
        ]);
    }

    /**
     * Xóa 1 dịch vụ
     * 
     */
    public function delete(Request $request)
    {

        $find_fast_motel = request("reservation_motel_id");

        $reservationMotelExist = reservationMotel::where(
            'id',
            $find_fast_motel
        )->where('host_id', $request->user->id)->first();

        if ($reservationMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[1],
            ]);
        }

        $idDeleted = $reservationMotelExist->id;
        $reservationMotelExist->delete();



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
