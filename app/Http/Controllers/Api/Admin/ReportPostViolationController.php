<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusReportPostViolationDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\ReportPostViolation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPostViolationController extends Controller
{
    /**
     * Lấy loại danh sách đăng trợ giúp
     * 
     * @queryParam title string tiêu đề loại bài đăng
     * @queryParam limit int số item trong trang
     * @queryParam sort_by string tên cột (title, created_at)
     * @queryParam descending string tên cột (title, created_at)
     * 
     */
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: false, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $reportPostViolations = ReportPostViolation::when($sortBy != null && ReportPostViolation::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
            $query->orderBy($sortBy, $descending);
        })
            ->when($request->user_id != null, function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->mo_post_id != null, function ($query) use ($request) {
                $query->where('mo_post_id', $request->mo_post_id);
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('status', $request->status);
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
            'data' => $reportPostViolations
        ]);
    }

    /**
     * Update 1 báo cáo vi phạm
     * 
     * @bodyParam status int
     */
    public function update(Request $request)
    {
        $reportPostViolation = request("report_post_violation_id");

        $reportPostViolationExist = ReportPostViolation::where(
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

    /**
     * Thong tin 1 báo cáo vi phạm
     * 
     */
    public function getOne(Request $request)
    {
        $reportPostViolation = request("report_post_violation_id");

        $reportPostViolationExist = ReportPostViolation::where(
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

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $reportPostViolationExist,
        ]);
    }

    /**
     * Xóa 1 báo cáo vi phạm
     * 
     */
    public function delete(Request $request)
    {

        $reportPostViolation = request("report_post_violation_id");

        $reportPostViolationExist = ReportPostViolation::where(
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

        $idDeleted = $reportPostViolationExist->id;
        $reportPostViolationExist->delete();



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
