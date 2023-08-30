<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\ReservationMotel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationMotelController extends Controller
{
    /**
     * 
     * Danh sách reservation motel
     * 
     */
    public function getAll(Request $request)
    {
        $reservationMotels = ReservationMotel::when($request->status != null, function ($query) use ($request) {
            $query->where('status', $request->status);
        })
            ->when($request->user_id != null, function ($query) use ($request) {
                $query->where('host_id', $request->user_id);
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
     * Thong tin 1 dịch vụ
     * 
     */
    public function getOne(Request $request)
    {

        $reservationMotelId = request("reservation_motel_id");

        $reservationMotelExist = ReservationMotel::where(
            [
                ['id', $reservationMotelId],
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
}
