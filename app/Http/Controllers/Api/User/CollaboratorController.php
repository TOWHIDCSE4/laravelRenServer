<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CollaboratorController extends Controller
{
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $limit = $request->limit ?: 20;
        $descending = filter_var(($request->descending ?: true), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($dateFrom != null || $dateTo != null) {
            if ($dateFrom != null && $dateTo != null) {
                if (
                    !Helper::validateDate($dateFrom, 'Y-m-d')
                    || !Helper::validateDate($dateTo, 'Y-m-d')
                ) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateFrom != null) {
                if (!Helper::validateDate($dateFrom, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateTo != null) {
                if (!Helper::validateDate($dateTo, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
        }

        $users = User::where([
            ['referral_code', $request->user->self_referral_code]
        ])
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->distinct()
            ->when($request->search != null, function ($query) {
                $query->search(request('search'));
            })
            ->when(User::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $users
        ]);
    }
}
