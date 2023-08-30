<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportProblem;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ParamUtils;
use App\Helper\StatusReportProblemDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group  Admin/Báo cáo sự cố
 *
 * APIs Báo cáo sự cố
 */
class AdminReportProblemController extends Controller
{
    /**
     * 
     * Danh sách báo cáo sự cố
     * 
     * @bodyParam date_from date ngày bắt đầu
     * @bodyParam date_to date ngày kết thúc
     * 
     */
    public function getAll(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $limit = $request->limit ?: 20;

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
            $dateTo = $dateTo . ' 23:59:59';
            $dateFrom = $dateFrom . ' 00:00:01';
        }

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }


        $listReportProblem = ReportProblem::join('motels', 'report_problems.motel_id', '=', 'motels.id')
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('motels.user_id', $request->user->id);
                }
            })
            ->orderBy('report_problems.severity', 'asc')
            ->orderBy('report_problems.created_at', 'desc')
            ->when($request->user_id != null, function ($query) use ($request) {
                $query->where('motels.user_id', $request->user_id);
            })
            ->when($request->motel_id != null, function ($query) use ($request) {
                $query->where('report_problems.motel_id', $request->motel_id);
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('report_problems.status', $request->status);
            })
            ->when($request->severity != null, function ($query) use ($request) {
                $query->where('report_problems.severity', $request->severity);
            })
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                if ($dateFrom != null) {
                    $query->where('report_problems.created_at', '>=', $dateFrom);
                }
                if ($dateTo != null) {
                    $query->where('report_problems.created_at', '<=', $dateTo);
                }
            })
            ->select('report_problems.*')
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listReportProblem
        ]);
    }

    /**
     * 
     * Tạo 1 báo cáo sự cố
     * 
     * @bodyParam user_id string Tên
     * @bodyParam motel_id string Tên
     * @bodyParam reason string Tên
     * @bodyParam describe_problem string Tên
     * @bodyParam status int Trạng thái báo cáo [0: Đang tiến hành, 1: Đã hủy, 2: Đã hoàn thành]
     * @bodyParam severity int Mức độ nghiêm trọng [0: Thấp 1: Bình thường, 2: Cao ]
     * @bodyParam images array ảnh sự cố
     * 
     */
    public function create(Request $request)
    {
        $images = $request->images ?: [];

        if (!DB::table('motels')->where('id', $request->motel_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        if ($request->reason == null || trim($request->reason) == '') {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::REASON_CANNOT_EMPTY[0],
                'msg' => MsgCode::REASON_CANNOT_EMPTY[1],
            ]);
        }

        if (!empty($images) && !is_array($images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        }

        if (StatusReportProblemDefineCode::getStatusSeverityCode($request->severity) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_SEVERITY_STATUS[0],
                'msg' => MsgCode::INVALID_SEVERITY_STATUS[1],
            ]);
        }

        $reportProblemCreate = ReportProblem::create([
            'user_id' => $request->user->id,
            'motel_id' => $request->motel_id,
            'reason' => $request->reason,
            'describe_problem' => $request->describe_problem,
            'images' => json_encode($images),
            'status' => 0,
            'severity' => $request->severity
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reportProblemCreate
        ]);
    }

    /**
     * 
     * Cập nhật 1 báo cáo sự cố
     * 
     * @bodyParam reason string Tên
     * @bodyParam motel_id int
     * @bodyParam describe_problem string Tên
     * @bodyParam images array ảnh sự cố
     * @bodyParam status int Trạng thái báo cáo [0: Đang tiến hành, 1: Đã hủy, 2: Đã hoàn thành]
     * 
     */
    public function update(Request $request)
    {
        $reportProblemId = request('report_problem_id');
        $images = [];

        if (!DB::table('motels')->where('id', $request->motel_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        if (!DB::table('motels')->where('id', $request->motel_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        $reportProblemExist = ReportProblem::where([
            ['id', $reportProblemId],
            ['motel_id', $request->motel_id]
        ])->first();

        if ($reportProblemExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1],
            ]);
        }

        if ($request->reason == null || trim($request->reason) == '') {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::REASON_CANNOT_EMPTY[0],
                'msg' => MsgCode::REASON_CANNOT_EMPTY[1],
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

        if (StatusReportProblemDefineCode::getStatusSeverityCode($request->severity) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_SEVERITY_STATUS[0],
                'msg' => MsgCode::INVALID_SEVERITY_STATUS[1],
            ]);
        }

        if (isset($request->images)) {
            if (!is_array($request->images)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_IMAGES[0],
                    'msg' => MsgCode::INVALID_IMAGES[1],
                ]);
            }
            $images = $request->images;
        } else {
            $images = $reportProblemExist->images;
        }

        $timeDone = $request->status == StatusReportProblemDefineCode::COMPLETED ? Carbon::now() : $reportProblemExist->time_done;

        $reportProblemExist->update([
            'reason' => $request->reason ?? $reportProblemExist->reason,
            'link_video' => $request->link_video ?? $reportProblemExist->link_video,
            'describe_problem' => $request->describe_problem ?? $reportProblemExist->describe_problem,
            'images' => json_encode($images),
            'severity' => ($request->severity) ?? $reportProblemExist->severity,
            'time_done' => $timeDone,
            'status' => $request->status ?? $reportProblemExist->status
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
     * Lấy 1 báo cáo sự cố
     * 
     * @urlParam report_problem_id int Mã báo cáo sự cố
     * 
     */
    public function getOne(Request $request)
    {
        $reportProblemId = request('report_problem_id');
        $reportProblemExist = ReportProblem::where([
            ['id', $reportProblemId]
        ])->first();

        if (empty($reportProblemExist)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1],
            ]);
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
     * @urlParam report_problem_id int Mã báo cáo sự cố
     * 
     */
    public function Delete(Request $request)
    {
        $reportProblemId = request('report_problem_id');
        $reportProblemExist = ReportProblem::where([
            ['id', $reportProblemId]
        ]);

        if (!$reportProblemExist->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1],
            ]);
        }

        $reportProblemExist->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
