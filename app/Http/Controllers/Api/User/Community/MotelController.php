<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Motel;
use App\Models\MsgCode;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use Illuminate\Http\Response as HttpResponse;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMotelDefineCode;

class MotelController extends Controller
{

    /**
     * Thong tin phòng trọ đang thuê
     * 
     */
    public function getOne(Request $request, $id)
    {
        $motelExists = Motel::where('motels.id', $id)
            ->join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
            ->join('renters', 'user_contracts.renter_phone_number', '=', 'renters.phone_number')
            // ->where('renters.user_id', $request->user->id)
            ->select('motels.*');


        if (!$motelExists->exists()) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motelExists->first(),
        ], 200);
    }

    /**
     * 
     * Danh cách người thuê trong phòng trọ đang thuê
     * 
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam money_from int
     * @queryParam money_to int
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     */
    public function getListRenterHasRenter(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;
        $search = $request->search;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => HttpResponse::HTTP_OK,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

        $motels = Motel::join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->join('renters', 'user_contracts.renter_phone_number', '=', 'renters.phone_number')
            ->where(function ($query) use ($request) {
                // $query->where('renters.user_id', $request->user->id);
                if ($request->has_contract) {
                    $query->where([
                        ['user_contracts.renter_phone_number', $request->user->phone_number],
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                    ]);
                }
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($request->province != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when($request->district != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->when($request->wards != null, function ($query) {
                $query->where('wards', request('wards'));
            })
            ->when(!empty($sortBy) && Motel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->distinct()
            ->select('motels.*')
            ->search($search)
            ->paginate($limit);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motels
        ], 200);
    }

    /**
     * 
     * Danh cách phòng trọ đang thuê
     * 
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam money_from int
     * @queryParam money_to int  
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     * 
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;
        $search = $request->search;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => HttpResponse::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }
        $motels = Motel::join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->join('renters', 'user_contracts.renter_phone_number', '=', 'renters.phone_number')
            ->where([
                ['renters.user_id', $request->user->id],
                ['user_contracts.renter_phone_number', $request->user->phone_number],
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
            ])
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($request->province != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when($request->district != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->when($request->wards != null, function ($query) {
                $query->where('wards', request('wards'));
            })
            ->when(!empty($sortBy) && Motel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->distinct()
            ->select('motels.*')
            ->search($search)
            ->paginate($limit);

        // $motels = Motel::join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
        //     ->join('renters', 'user_contracts.renter_phone_number', '=', 'renters.phone_number')
        //     ->where('renters.user_id', $request->user->id)
        //     ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
        //         $query->where($typeMoney, '>=', $fromMoney);
        //     })
        //     ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
        //         $query->where($typeMoney, '<=', $toMoney);
        //     })
        //     ->when($request->province != null, function ($query) {
        //         $query->where('province', request('province'));
        //     })
        //     ->when($request->district != null, function ($query) {
        //         $query->where('district', request('district'));
        //     })
        //     ->when($request->wards != null, function ($query) {
        //         $query->where('wards', request('wards'));
        //     })
        //     ->when(!empty($sortBy) && Motel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
        //         $query->orderBy($sortBy, $descending);
        //     })
        //     ->distinct()
        //     ->select('motels.*')
        //     ->search($search)
        //     ->paginate($limit);
        // $custom = collect(
        //     MotelUtils::getBadgesMotels($request->user->id)
        // );
        // $data = $custom->merge($motels);


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motels
        ], 200);
    }
}
