<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusReportPostViolationDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationAdminJob;
use App\Models\MsgCode;
use App\Models\ReportPostFindMotelViolation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportPostFindMotelViolationController extends Controller
{
    /**
     * Báo cáo vi phạm bài đăng
     * 
     * @queryBody reason string lý do
     * @queryBody description string mô tả 
     * @queryBody mo_post_find_motel_id int 
     * @queryBody user_id int [optional] 
     * 
     */
    public function create(Request $request)
    {
        if ($request->reason == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::REASON_CANNOT_EMPTY[0],
                'msg' => MsgCode::REASON_CANNOT_EMPTY[1],
            ]);
        }

        $moPostExist = DB::table('mo_post_find_motels')
            ->join('users', 'mo_post_find_motels.user_id', '=', 'users.id')
            ->where('mo_post_find_motels.id', $request->mo_post_find_motel_id)
            ->select('mo_post_find_motels.*', 'users.name')
            ->first();
        if ($moPostExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_POST_FIND_MOTEL_EXISTS[1],
            ]);
        }

        $reportPostViolationCreate = ReportPostFindMotelViolation::create([
            'reason' => $request->reason,
            'description' => $request->description,
            'mo_post_find_motel_id' => $request->mo_post_find_motel_id,
            'status' => StatusReportPostViolationDefineCode::PROGRESSING,
            'user_id' => $request->user != null ? $request->user->id : null,
        ]);

        // NotificationAdminJob::dispatch(
        //     null,
        //     "Thông báo vi phạm",
        //     'Bài đăng ' . $moPostExist->title . ', của chủ nhà ' . $moPostExist->name,
        //     TypeFCM::NEW_REPORT_VIOLATION,
        //     NotiUserDefineCode::USER_IS_ADMIN,
        //     $reportPostViolationCreate->id,
        //     true
        // );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reportPostViolationCreate
        ]);
    }


    /**
     * Update 1 báo cáo vi phạm
     * 
     * 
     * @bodyParam status int
     * @bodyParam status int
     */
    public function update(Request $request)
    {

        $reportPostViolation = request("report_post_find_motel_violation_id");

        $reportPostViolationExist = ReportPostFindMotelViolation::where(
            'id',
            $reportPostViolation
        )->first();

        if ($reportPostViolationExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_REPORT_POST_VIOLATION_EXISTS[0],
                'msg' => MsgCode::NO_REPORT_POST_VIOLATION_EXISTS[1],
            ]);
        }

        if (StatusReportPostViolationDefineCode::getStatusMotelCode($request->status) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_REPORT_POST_VIOLATION_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_REPORT_POST_VIOLATION_EXISTS[1],
            ]);
        }

        $reportPostViolationExist->update([
            'status' => $request->status,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reportPostViolationExist,
        ]);
    }
}
