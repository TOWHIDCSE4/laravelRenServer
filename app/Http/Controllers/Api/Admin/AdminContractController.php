<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class AdminContractController extends Controller
{
    /**
     * 
     * Danh cách hợp đồng
     * 
     * @queryParam contract_status int (2 đang hoạt động,1 quá hạn, 0 đã thanh lý )
     * @queryParam represent_name  string Tên người đại diện thuê
     * @queryParam phone_number string số điện thoại người thuê
     * @queryParam motel_name string tên phòng
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam money_from int
     * @queryParam money_to int
     * @bodyParam date_from date ngày bắt đầu
     * @bodyParam date_to date ngày kết thúc
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     * 
     */
    public function getAll(Request $request)
    {
        $contractStatus = $request->contract_status;
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;
        $limit = $request->limit ?: 20;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if ($request->filled('contract_status')) {
            if (StatusContractDefineCode::getStatusContractCode($contractStatus, false) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                    'msg' => MsgCode::NO_STATUS_EXISTS[1],
                ]);
            }
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

        if ($dateFrom != null || $dateTo != null) {
            if (($dateFrom != null && $dateTo != null) && (Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))) {
                $dateFrom = $dateFrom . ' 00:00:01';
                $dateTo = $dateTo . ' 23:59:59';
            } else if ($dateFrom != null && Helper::validateDate($dateFrom, 'Y-m-d')) {
                $dateFrom = $dateFrom . ' 00:00:01';
            } else if ($dateTo != null && Helper::validateDate($dateTo, 'Y-m-d')) {
                $dateTo = $dateTo . ' 23:59:59';
            } else {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }
        }

        $all = Contract::when($contractStatus != null, function ($query) use ($contractStatus) {
            if ($contractStatus == StatusContractDefineCode::PROGRESSING) {
                $query->whereIn('contracts.status', [StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
                $query->orderBy('contracts.status', 'asc');
            } else {
                $query->where('contracts.status', $contractStatus);
            }
        })
            ->when($request->user_id != null, function ($query) {
                $query->where('contracts.user_id', request('user_id'));
            })
            ->when($request->motel_id != null, function ($query) {
                $query->where('contracts.motel_id', request('motel_id'));
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('contracts.created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('contracts.created_at', '<=', $dateTo);
            })
            ->when($request->search != null, function ($query) {
                $query->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id');
                $query->join('renters', 'user_contracts.renter_phone_number', '=', 'renters.phone_number');
                $query->join('motels', 'contracts.motel_id', '=', 'motels.id');
                $query->where(function ($q) {
                    $q->where('renters.phone_number', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('renters.name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('motels.motel_name', 'LIKE', '%' . request('search') . '%');
                });
            })
            ->when($sortBy != null && Schema::hasColumn('contracts', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->distinct('contracts.id')
            ->select('contracts.*')
            ->paginate($limit);



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all,
        ]);
    }
}
