<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Http\Controllers\Controller;
use App\Models\HistoryPotentialUser;
use App\Models\MsgCode;
use App\Models\PotentialUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PotentialController extends Controller
{
    /**
     * Cập nhật 1 khách hàng tiềm năng
     * 
     */
    public function update(Request $request)
    {
        $potentialUserExists = PotentialUser::where('id', $request->potential_user_id)
            ->first();

        if ($potentialUserExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_POTENTIAL_USER_EXISTS[0],
                'msg' => MsgCode::NO_POTENTIAL_USER_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $potentialUserExists->update([
            'status' => $request->status != null ? $request->status : $potentialUserExists->status,
            'title' => $request->title != null ? $request->title : $potentialUserExists->title,
            'type_from' => $request->type_from != null ? $request->type_from : $potentialUserExists->type_from,
            'value_reference' => $request->value_reference != null ? $request->value_reference : $potentialUserExists->value_reference
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $potentialUserExists->first(),
        ], 200);
    }

    /**
     * Thông tin 1 khách hàng tiềm năng
     * 
     */
    public function getOne(Request $request)
    {
        $potentialUserExists = PotentialUser::where('id', $request->potential_user_id)
            ->where('potential_users.is_has_contract', false)
            ->first();

        if ($potentialUserExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_POTENTIAL_USER_EXISTS[0],
                'msg' => MsgCode::NO_POTENTIAL_USER_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $potentialUserExists,
        ], 200);
    }

    /**
     * Xóa 1 khách hàng tiềm năng
     * 
     */
    public function delete(Request $request)
    {
        $potentialUserExists = PotentialUser::where('id', $request->potential_user_id)
            ->first();

        if ($potentialUserExists == null) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_POTENTIAL_USER_EXISTS[0],
                'msg' => MsgCode::NO_POTENTIAL_USER_EXISTS[1],
            ], Response::HTTP_BAD_REQUEST);
        }

        $potentialUserExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $potentialUserExists->first(),
        ], 200);
    }

    /**
     * 
     * Danh sách khách hàng tiềm năng
     * 
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        // $isRenter = request('is_renter') != null ? request('is_renter') : false;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $listHistoryPotentialUser = PotentialUser::where(function ($query) use ($request) {
            if ($request->user->is_admin == true && request('user_id') != null) {
                $query->where('potential_users.user_host_id', request('user_id'));
            } else if (request('user_guest_id')) {
                $query->where('potential_users.user_guest_id', request('user_guest_id'));
            } else {
                $query->where('potential_users.user_host_id', $request->user->id);
            }
        })
            ->where(function ($query) {
                $query->where('potential_users.is_has_contract', request('is_has_contract') ?? false);
            })
            // ->where(function ($query) {
            //     $query->where('potential_users.is_renter', request('is_renter') ?? true);
            // })
            // ->when($request->is_renter != null, function ($query) {
            //     $query->where('potential_users.is_renter', request('is_renter'));
            // })
            // ->when($request->user->is_admin == true, function ($query) {
            //     $query->where('user_host_id', request('user_id'));
            // })
            ->when($request->type_from != null, function ($query) {
                $query->where('potential_users.type_from', request('type_from'));
            })
            ->when($request->status != null, function ($query) {
                $query->where('potential_users.status', request('status'));
                if (request('status') == StatusHistoryPotentialUserDefineCode::COMPLETED) {
                    $query->where('is_has_contract', false);
                }
            })
            ->when($request->user_guest_id != null, function ($query) {
                $query->where('potential_users.user_guest_id', request('user_guest_id'));
            })
            ->when(!empty($sortBy) && Schema::hasColumn('potential_users', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search, null, true, true);
            })
            ->paginate($limit);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listHistoryPotentialUser
        ], 200);
    }

    /**
     * 
     * Danh sách lịch sử khách hàng tiềm năng
     * 
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     */
    public function getAllHistoryPotential(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $motels = HistoryPotentialUser::where('user_host_id', $request->user->id)
            ->when($request->type_from != null, function ($query) {
                $query->where('type_from', request('type_from'));
            })
            ->when($request->user_guest_id != null, function ($query) {
                $query->where('user_guest_id', request('user_guest_id'));
            })
            ->when($request->user->is_admin == true && $request->user_host_id != null, function ($query) {
                $query->where('user_host_id', request('user_host_id'));
            })
            ->when(!empty($sortBy) && Schema::hasColumn('mo_posts', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search, null, true, true);
            })
            ->paginate($limit);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $motels
        ], 200);
    }
}
