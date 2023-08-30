<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use App\Models\ReportProblem;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ParamUtils;
use App\Helper\StatusReportProblemDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationAdminJob;
use App\Jobs\NotificationUserJob;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group User/Cộng đồng/Báo cáo Sự cố
 */

class ReportProblemController extends Controller
{
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

        $motelExist = DB::table('motels')->where('id', $request->motel_id)->first();

        if ($motelExist == null) {
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

        if (!is_array($images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        }

        if (count($images) > 0) {
            foreach ($images as $imageItem) {
                if ($imageItem == null || empty($imageItem)) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_IMAGES[0],
                        'msg' => MsgCode::INVALID_IMAGES[1],
                    ]);
                }
            }
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
            'describe_problem' => $request->describe_problem,
            'link_video' => $request->link_video,
            'reason' => $request->reason,
            'images' => json_encode($images),
            'status' => StatusReportProblemDefineCode::PROGRESSING,
            'time_done' => Carbon::now(),
            'severity' => $request->severity
        ]);

        // setup notifications
        $userHost = DB::table('users')->where('id', $motelExist->user_id)->first();
        if ($userHost != null && !$userHost->is_admin && $userHost->is_host) {
            NotificationUserJob::dispatch(
                $motelExist->user_id,
                "Bạn có sự cố phòng mới cần giải quyết",
                'Sự cố mới có ' . StatusReportProblemDefineCode::getStatusSeverityCode($request->severity, true) . ', tại phòng ' . $motelExist->motel_name,
                TypeFCM::NEW_REPORT_PROBLEM,
                NotiUserDefineCode::USER_IS_HOST,
                $reportProblemCreate->id,
            );
        }

        NotificationAdminJob::dispatch(
            null,
            "Bạn có sự cố phòng mới cần giải quyết",
            'Sự cố mới có ' . StatusReportProblemDefineCode::getStatusSeverityCode($request->severity, true) . ', tại phòng ' . $motelExist->motel_name,
            TypeFCM::NEW_REPORT_PROBLEM,
            NotiUserDefineCode::USER_IS_ADMIN,
            $reportProblemCreate->id,
        );

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

        $reportProblemExist = ReportProblem::where([
            ['id', $reportProblemId],
            ['motel_id', $request->motel_id],
            ['user_id', $request->user->id]
        ]);

        if (!$reportProblemExist->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1],
            ]);
        }
        $reportProblemOldData = $reportProblemExist->first();

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
            $images = $reportProblemOldData->images;
        }

        $reportProblemExist->update([
            'reason' => $request->reason,
            'link_video' => $request->link_video,
            'describe_problem' => $request->describe_problem,
            'images' => json_encode($images),
            'severity' => ($request->severity),
            'status' => $request->status
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ReportProblem::where('id', $reportProblemOldData->id)->first()
        ]);
    }

    /**
     * 
     * Danh sách báo cáo sự cố
     * 
     * @bodyParam date_from date ngày bắt đầu
     * @bodyParam date_to date ngày kết thúc
     * @bodyParam status int mã trạng thái
     * @bodyParam severity int mã mức độ
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

        $listReportProblem = ReportProblem::where('user_id', $request->user->id)
            ->orderBy('severity', 'asc')
            ->orderBy('created_at', 'desc')
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                if ($dateFrom != null && $dateTo != null) {
                    $query->where('created_at', '>=', $dateFrom);
                    $query->where('created_at', '<=', $dateTo);
                } else if ($dateFrom != null) {
                    $query->where('created_at', '>=', $dateFrom);
                } else if ($dateTo != null) {
                    $query->where('created_at', '<=', $dateTo);
                }
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->severity != null, function ($query) use ($request) {
                $query->where('severity', $request->severity);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
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
     * Lấy 1 báo cáo sự cố
     * 
     * @urlParam report_problem_id int Mã báo cáo sự cố
     * 
     */
    public function getOne(Request $request)
    {
        $reportProblemId = request('report_problem_id');
        $reportProblemExist = ReportProblem::where([
            ['id', $reportProblemId],
            ['user_id', $request->user->id]
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
            'code' => 200,
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
            ['id', $reportProblemId],
            ['user_id', $request->user->id]
        ]);

        if (!$reportProblemExist->exists()) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_PROBLEM_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_PROBLEM_EXISTS[1],
            ]);
        }

        $reportProblemExist->delete();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
