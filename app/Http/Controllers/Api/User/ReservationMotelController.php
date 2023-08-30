<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\NotiUserDefineCode;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Helper\StatusReservationMotelDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\HistoryPotentialUser;
use App\Models\MsgCode;
use App\Models\ReservationMotel;
use App\Utils\PotentialUserUtil;
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
        $reservationMotels = ReservationMotel::when($request->user != null, function ($query) use ($request) {
            $query->where('user_id', $request->user->id);
        })
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
            'data' => $reservationMotels,
        ]);
    }

    /**
     * 
     * Thêm reservation motel
     * 
     */
    public function create(Request $request)
    {
        $moPostExist = DB::table('mo_posts')->where('id', $request->mo_post_id)->first();
        if ($moPostExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }
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

        if ($request->user != null) {
            $created =   ReservationMotel::create([
                'mo_post_id' => $request->mo_post_id,
                'host_id' => $request->host_id,
                'user_id' => $request->user->id,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'status' => StatusReservationMotelDefineCode::NOT_CONSULT,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                "note" => $request->note,
                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
                "address_detail" => $request->address_detail,
            ]);
        } else {
            $created =   ReservationMotel::create([
                'mo_post_id' => $request->mo_post_id,
                'host_id' => $request->host_id,
                'user_id' => null,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'status' => StatusReservationMotelDefineCode::NOT_CONSULT,
                "province" => $request->province,
                "district" => $request->district,
                "wards" => $request->wards,
                "note" => $request->note,
                "province_name" => Place::getNameProvince($request->province),
                "district_name" => Place::getNameDistrict($request->district),
                "wards_name" => Place::getNameWards($request->wards),
                "address_detail" => $request->address_detail,
            ]);
        }

        // handle user potential
        if ($request->user) {

            PotentialUserUtil::updatePotential(
                $request->user->id,
                $moPostExist->user_id,
                $request->mo_post_id,
                $moPostExist->title,
                StatusHistoryPotentialUserDefineCode::TYPE_FROM_RESERVATION
            );

            HistoryPotentialUser::create([
                'user_guest_id' => $request->user->id,
                'user_host_id' => $moPostExist->user_id,
                'value_reference' => $request->mo_post_id,
                'type_from' =>  StatusHistoryPotentialUserDefineCode::TYPE_FROM_RESERVATION,
                'title' => $moPostExist->title
            ]);
        }

        NotificationUserJob::dispatch(
            $moPostExist->user_id,
            'Thông báo giữ chỗ',
            'Có 1 yêu cầu giữ chỗ phòng ' . $moPostExist->motel_name,
            TypeFCM::NEW_RESERVATION,
            NotiUserDefineCode::USER_IS_HOST,
            $created->id
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

        $reservationMotelId = request("reservation_motel_id");

        $reservationMotelExist = ReservationMotel::where(
            [
                ['id', $reservationMotelId],
                ['user_id', $request->user->id]
            ]
        )->first();

        if ($reservationMotelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_RESERVATION_MOTEL_EXISTS[1],
            ]);
        }

        if ($reservationMotelExist->status = StatusReservationMotelDefineCode::CONSULTED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::RESERVATION_MOTEL_HAS_CONSULTED[0],
                'msg' => MsgCode::RESERVATION_MOTEL_HAS_CONSULTED[1],
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
            'name' => $request->name ?? $request->name,
            'host_id' => $request->host_id ?? $reservationMotelExist->host_id,
            'mo_post_id' => $request->mo_post_id ?? $reservationMotelExist->mo_post_id,
            'phone_number' =>  $request->phone_number ?? $reservationMotelExist->phone_number,
            'status' =>  StatusReservationMotelDefineCode::NOT_CONSULT,
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

        $reservationMotelExist = ReservationMotel::where(
            [
                ['id', $reservationMotelId],
                ['user_id', $request->user->id]
            ]
        )->first();

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

        $reservationMotelId = request("reservation_motel_id");

        $reservationMotelExist = ReservationMotel::where(
            [
                ['id', $reservationMotelId],
                ['user_id', $request->user->id]
            ]
        )->first();

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
