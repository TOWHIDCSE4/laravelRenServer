<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\DatetimeUtils;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusConfigCommissionDefineCode;
use App\Http\Controllers\Controller;
use App\Models\ConfigCommission;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConfigCommissionController extends Controller
{
    public function getAll(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $carbon = DatetimeUtils::getNow();
        $date1 = null;
        $date2 = null;
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }

        $listConfigCommission = ConfigCommission::sortByRelevance(true)
            // ->when($request->status != null, function ($query) use ($request) {
            //     $query->where('bills.status', $request->status);
            // })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->when(!empty($sortBy) && ConfigCommission::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->distinct()
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listConfigCommission
        ]);
    }

    /**
     * 
     * Thong tin
     * 
     */
    public function getOne(Request $request)
    {
        $configCommission = ConfigCommission::where(
            [
                ['id', request('config_commission_id')],
            ]
        )
            ->first();

        if ($configCommission == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CONFIG_COMMISSION_EXISTS[0],
                'msg' => MsgCode::NO_CONFIG_COMMISSION_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $configCommission,
        ]);
    }

    public function update(Request $request)
    {
        $configCommission = ConfigCommission::where(
            [
                ['id', request('config_commission_id')],
            ]
        )
            ->first();

        if ($configCommission == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CONFIG_COMMISSION_EXISTS[0],
                'msg' => MsgCode::NO_CONFIG_COMMISSION_EXISTS[1],
            ]);
        }

        if ($configCommission->status_host != StatusConfigCommissionDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::CONFIG_COMMISSION_HOST_WITH_ADMIN_NOT_COMPLETE[0],
                'msg' => MsgCode::CONFIG_COMMISSION_HOST_WITH_ADMIN_NOT_COMPLETE[1],
            ]);
        }


        $configCommission->update([
            "user_id" =>  $request->user_id,
            "money_commission_user" =>  $request->money_commission_user
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $configCommission,
        ]);
    }

    public function confirmCommission(Request $request)
    {
        $configCommission = ConfigCommission::where(
            [
                ['id', request('config_commission_id')],
            ]
        )
            ->first();

        if ($configCommission == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CONFIG_COMMISSION_EXISTS[0],
                'msg' => MsgCode::NO_CONFIG_COMMISSION_EXISTS[1],
            ]);
        }

        if ($configCommission->status_host == StatusConfigCommissionDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::CONFIG_COMMISSION_HOST_COMPLETED[0],
                'msg' => MsgCode::CONFIG_COMMISSION_HOST_COMPLETED[1],
            ]);
        }

        if (StatusConfigCommissionDefineCode::getConfigCommissionCode($request->status) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_CONFIG_COMMISSION_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_CONFIG_COMMISSION_EXISTS[1],
            ]);
        }


        $configCommission->update([
            "status_host"  => $request->status,
            'note' => $request->note
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $configCommission,
        ]);
    }
}
