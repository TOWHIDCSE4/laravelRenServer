<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use App\Helper\ParamUtils;
use App\Models\ReportProblem;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Helper\StatusReportProblemDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use App\Jobs\PushNotificationUserJob;
use Carbon\Carbon;
use DateTime;

class ReportProblemController extends Controller
{
    /**
     * 
     * Danh sách báo cáo sự cố
     * 
     * @bodyParam motel int mã phòng
     * @bodyParam date_from date ngày bắt đầu
     * @bodyParam date_to date ngày kết thúc
     * 
     */
    public function getAll(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $limit = $request->limit ?: 20;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
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
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateFrom != null) {
                if (!Helper::validateDate($dateFrom, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateTo != null) {
                if (!Helper::validateDate($dateTo, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }

            $dateTo = $dateTo . ' 23:59:59';
            $dateFrom = $dateFrom . ' 00:00:01';
        }


        $listReportProblem = ReportProblem::join('motels', 'report_problems.motel_id', '=', 'motels.id')
            // ->where(function ($query) use ($request) {
            //     if ($request->user->is_admin != true) {
            //         $query->where('motels.user_id', $request->user->id);
            //     }
            // })
            ->when($request->user->is_admin != true, function ($query) use ($request) {
                $query->where('motels.user_id', $request->user->id);
            })
            ->when($request->user->is_admin == true && $request->user_id != null, function ($query) {
                $query->where('motels.user_id', request('user_id'));
            })
            ->orderBy('report_problems.severity', 'asc')
            ->orderBy('report_problems.created_at', 'desc')
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                if ($dateFrom != null) {
                    $query->where('report_problems.created_at', '>=', $dateFrom);
                }
                if ($dateTo != null) {
                    $query->where('report_problems.created_at', '<=', $dateTo);
                }
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('report_problems.status', $request->status);
            })
            ->when($request->motel_id != null, function ($query) use ($request) {
                $query->where('report_problems.motel_id', $request->motel_id);
            })
            ->when($request->severity != null, function ($query) use ($request) {
                $query->where('report_problems.severity', $request->severity);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->select('report_problems.*')
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listReportProblem
        ]);
    }

    /**
     * 
     * Danh sách báo cáo sự cố
     * 
     * @bodyParam motel_id int mã phòng
     * @bodyParam date_from date ngày bắt đầu
     * @bodyParam date_to date ngày kết thúc
     * 
     */
    public function getOne(Request $request, $id)
    {
        $reportProblem = ReportProblem::join('motels', 'report_problems.motel_id', '=', 'motels.id')
            ->where('report_problems.id', $id)
            ->when($request->user->is_admin != true, function ($query) use ($request) {
                $query->where('motels.user_id', $request->user->id);
            })

            ->select('report_problems.*')
            ->first();

        if ($reportProblem == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reportProblem
        ]);
    }

    /**
     * 
     * Cập nhật trạng thái 1 báo cáo sự cố
     * 
     * @bodyParam motel_id int
     * @bodyParam status int Trạng thái báo cáo [0: Đang tiến hành, 2: Đã hoàn thành]
     * 
     */
    public function update(Request $request, $id)
    {
        $reportProblemExist = ReportProblem::join('motels', 'report_problems.motel_id', '=', 'motels.id')
            ->where('report_problems.id', $id)
            ->when($request->user->is_admin != true, function ($query) use ($request) {
                $query->where('motels.user_id', $request->user->id);
            })

            ->select('report_problems.*')
            ->first();

        if ($reportProblemExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1],
            ]);
        }

        if (StatusReportProblemDefineCode::getStatusReportCode($request->status) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_REPORT_PROBLEM_STATUS[0],
                'msg' => MsgCode::INVALID_REPORT_PROBLEM_STATUS[1],
            ]);
        }

        $timeDone = $request->status == StatusReportProblemDefineCode::COMPLETED ? Carbon::now() : $reportProblemExist->time_done;


        $reportProblemExist->update([
            'status' => $request->status,
            'time_done' => $timeDone
        ]);

        // setup notifications
        if ($request->status == StatusReportProblemDefineCode::COMPLETED) {
            NotificationUserJob::dispatch(
                $reportProblemExist->user_id,
                "Sự cố phòng của bạn đã được giải quyết",
                'Sự cố phòng của bạn đã được giải quyết ',
                TypeFCM::REPORT_PROBLEM_DONE,
                NotiUserDefineCode::USER_NORMAL,
                $reportProblemExist->id,
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reportProblemExist
        ]);
    }

    /**
     * 
     * Xóa 1 báo cáo sự cố
     * 
     * @bodyParam report_problem_id int
     * 
     */
    public function delete(Request $request, $id)
    {
        $reportProblemExist = ReportProblem::join('motels', 'report_problems.motel_id', '=', 'motels.id')
            ->where('report_problems.id', $id)
            ->when($request->user->is_admin != true, function ($query) use ($request) {
                $query->where('motels.user_id', $request->user->id);
            })

            ->select('report_problems.*')
            ->first();

        if ($reportProblemExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1],
            ]);
        }

        // setup notifications
        // $motelExist = DB::table('motels')->where('id', $reportProblemExist->motel_id)->first();
        // PushNotificationUserJob::dispatch(
        //     $motelExist->user_id,
        //     "Sự cố phòng của bạn đã bị xóa",
        //     'Sự cố mới có ' . StatusReportProblemDefineCode::getStatusSeverityCode($request->severity, true) . ', tại phòng ' . $motelExist->motel_name ?? null . ', Đã bị xóa bởi chủ nhà',
        //     TypeFCM::REPORT_PROBLEM_DELETED_BY_HOST,
        //     NotiUserDefineCode::USER_NORMAL,
        //     null,
        // );

        $reportProblemExist->delete();


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
