<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\DatetimeUtils;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdminBillController extends Controller
{
    /*
     * 
     * Danh sách hóa đơn 
     * 
     * @queryParam motel_id int tìm hóa đơn theo mã phòng
     * @queryParam motel_name string số phòng ()
     * @queryParam phone_number string số điện thoại
     * @queryParam status int trạng thái hóa đơn
     * @queryParam date_from datetime ngày bắt đầu tìm
     * @queryParam date_to datetime ngày bắt kết thúc
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string search theo: content,
     * 
     */
    public function getAll(Request $request)
    {
        $motel_id = $request->motel_id;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        //Config datetime
        $carbon = DatetimeUtils::getNow();
        $date1 = null;
        $date2 = null;
        $isExist = null;
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => RESPONSE::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($motel_id != null) {
            $isExist = DB::table('motels')
                ->where([['id', $motel_id], ['status', StatusMotelDefineCode::MOTEL_HIRED]])
                ->exists();

            if (!$isExist) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_MOTEL_EXISTS[1],
                ]);
            }
        }

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }

        $listBill = Bill::sortByRelevance(true)
            ->join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where([
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
            ])
            ->when($request->user_id != null, function ($query) use ($request) {
                $query->where('contracts.user_id', $request->user_id);
            })
            ->when($request->phone_number != null, function ($query) use ($request) {
                $query->where('user_contracts.renter_phone_number', 'LIKE', '%' . $request->phone_number . '%');
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('bills.status', $request->status);
            })
            ->when($isExist, function ($query) use ($motel_id) {
                $query->where('user_contracts.motel_id', $motel_id);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('bills.date_payment', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('bills.date_payment', '<=', $dateTo);
            })
            ->when(!empty($sortBy) && Bill::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->select('bills.*')
            ->distinct('bills.id')
            ->get()
            ->each(function ($items) {
                $items->append('host');
            });
        $listBill = $listBill->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listBill
        ]);
    }
}
