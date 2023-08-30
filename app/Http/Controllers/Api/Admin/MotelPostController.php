<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\HostRankDefineCode;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MoPost;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use App\Helper\ParamUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMoPostDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use App\Models\Motel;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @group Admin/Quản lý/Bài đăng tìm phòng trọ
 */

class MotelPostController extends Controller
{
    /**
     * 
     * Danh cách phòng đăng tìm phòng trọ
     * 
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

        $moPosts = MoPost::when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
            $query->where($typeMoney, '>=', $fromMoney);
        })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when(request('user_id') != null, function ($query) {
                $query->where('user_id', request('user_id'));
            })
            ->when(request('sex') != null, function ($query) {
                $query->where('sex', request('sex'));
            })
            ->when(request('number_floor') != null, function ($query) {
                $query->where('number_floor', request('number_floor'));
            })
            ->when(request('quantity_vehicle_parked') != null, function ($query) {
                $query->where('quantity_vehicle_parked', request('quantity_vehicle_parked'));
            })
            ->when(request('province') != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when(request('district') != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->when(request('wards') != null, function ($query) {
                $query->where('wards', request('wards'));
            })
            ->when(request('has_wifi') != null, function ($query) {
                $query->where('has_wifi', request('has_wifi')  ? 1 : 0);
            })
            ->when(request('has_park') != null, function ($query) {
                $query->where('has_park', request('has_park')  ? 1 : 0);
            })
            ->when(request('has_window') != null, function ($query) {
                $query->where('has_window', request('has_window')  ? 1 : 0);
            })
            ->when(request('has_security') != null, function ($query) {
                $query->where('has_security', request('has_security')  ? 1 : 0);
            })
            ->when(request('has_free_move') != null, function ($query) {
                $query->where('has_free_move', request('has_free_move')  ? 1 : 0);
            })
            ->when(request('has_own_owner') != null, function ($query) {
                $query->where('has_own_owner', request('has_own_owner')  ? 1 : 0);
            })
            ->when(request('has_air_conditioner') != null, function ($query) {
                $query->where('has_air_conditioner', request('has_air_conditioner')  ? 1 : 0);
            })
            ->when(request('has_water_heater') != null, function ($query) {
                $query->where('has_water_heater', request('has_water_heater')  ? 1 : 0);
            })
            ->when(request('has_kitchen') != null, function ($query) {
                $query->where('has_kitchen', request('has_kitchen')  ? 1 : 0);
            })
            ->when(request('has_fridge') != null, function ($query) {
                $query->where('has_fridge', request('has_fridge')  ? 1 : 0);
            })
            ->when(request('has_washing_machine') != null, function ($query) {
                $query->where('has_washing_machine', request('has_washing_machine')  ? 1 : 0);
            })
            ->when(request('has_mezzanine') != null, function ($query) {
                $query->where('has_mezzanine', request('has_mezzanine')  ? 1 : 0);
            })
            ->when(request('has_wardrobe') != null, function ($query) {
                $query->where('has_wardrobe', request('has_wardrobe')  ? 1 : 0);
            })
            ->when(request('has_tivi') != null, function ($query) {
                $query->where('has_tivi', request('has_tivi')  ? 1 : 0);
            })
            ->when(request('has_pet') != null, function ($query) {
                $query->where('has_pet', request('has_pet')  ? 1 : 0);
            })
            ->when(request('has_balcony') != null, function ($query) {
                $query->where('has_balcony', request('has_balcony')  ? 1 : 0);
            })
            ->when(request('has_finger_print') != null, function ($query) {
                $query->where('has_finger_print', filter_var(request('has_finger_print'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_kitchen_stuff') != null, function ($query) {
                $query->where('has_kitchen_stuff', filter_var(request('has_kitchen_stuff'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_table') != null, function ($query) {
                $query->where('has_table', filter_var(request('has_table'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_picture') != null, function ($query) {
                $query->where('has_picture', filter_var(request('has_picture'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_decorative_lights') != null, function ($query) {
                $query->where('has_decorative_lights', filter_var(request('has_decorative_lights'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_tree') != null, function ($query) {
                $query->where('has_tree', filter_var(request('has_tree'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_pillow') != null, function ($query) {
                $query->where('has_pillow', filter_var(request('has_pillow'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mattress') != null, function ($query) {
                $query->where('has_mattress', filter_var(request('has_mattress'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_shoes_rasks') != null, function ($query) {
                $query->where('has_shoes_rasks', filter_var(request('has_shoes_rasks'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_curtain') != null, function ($query) {
                $query->where('has_curtain', filter_var(request('has_curtain'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_mirror') != null, function ($query) {
                $query->where('has_mirror', filter_var(request('has_mirror'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_sofa') != null, function ($query) {
                $query->where('has_sofa', filter_var(request('has_sofa'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('has_ceiling_fans') != null, function ($query) {
                $query->where('has_ceiling_fans', filter_var(request('has_ceiling_fans'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
            })
            ->when(request('type') != null, function ($query) {
                $query->where('type', request('type'));
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when(!empty($sortBy) && Schema::hasColumn('mo_posts', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);

        //     ->get()
        //     ->each(function ($items) {
        //         $items->append('host');
        //     });

        // $moPosts = $moPosts->paginate($limit);


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPosts,
        ]);
    }


    /**
     * Thong tin bài đăng tìm 
     * 
     */
    public function getOne(Request $request)
    {

        $post_id = request("post_id");

        $moPost = MoPost::where('id', $post_id)->first();

        if ($moPost == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        $moPost->user = User::where('id', $moPost->user_id)->first();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $moPost,
        ]);
    }

    /**
     * cập nhật bài đăng
     * 
     * @queryBody status int trạng thái bài đăng
     * @queryBody admin_verified boolean xác thực bài đăng
     */
    public function update(Request $request)
    {
        $post_id = request("post_id");
        $postTitle = null;
        $oldStatus = 0;
        $status = 0;

        $modelPostExists = MoPost::where(
            'id',
            $post_id
        )->first();
        $oldStatus = $modelPostExists->status;

        if ($modelPostExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        if ($request->status != null) {
            if (StatusMoPostDefineCode::getStatusMoPostCode($request->status) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                    'msg' => MsgCode::NO_STATUS_EXISTS[1],
                ]);
            }
        }

        $contractExist = DB::table('contracts')->where([
            ['status',  StatusContractDefineCode::COMPLETED],
            ['motel_id', $modelPostExists->motel_id],
            ['user_id', $modelPostExists->user_id]
        ])->first();

        if ($contractExist != null) {
            if ($contractExist->status == StatusContractDefineCode::COMPLETED && $request->status == StatusMoPostDefineCode::PROCESSING) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[0],
                    'msg' => MsgCode::CONTRACT_IS_ACTIVE_UNABLE_REPOST[1],
                ]);
            }
        }

        $modelPostExists->update([
            // "motel_id"  => isset($request->motel_id) ? $request->motel_id : null,
            'status' => $request->status ?? $modelPostExists->status,
            'admin_verified' => isset($request->admin_verified)
                ? (filter_var($request->admin_verified, FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : $modelPostExists->admin_verified,
        ]);

        // up to host motel
        $userExist = User::where('id', $modelPostExists->user_id)
            ->first();

        if ($userExist != null) {
            if ($userExist->host_rank == HostRankDefineCode::VIP) {
                $modelPostExists->update([
                    'admin_verified' => true
                ]);
            }
        }

        if ($modelPostExists->motel_id != null) {
            $motelExist = Motel::where('id', $modelPostExists->motel_id)->first();
            if ($motelExist != null) {
                $postTitle = $motelExist->title;
                $motelExist->update([
                    'has_post' => true
                ]);
            }
        }

        // setup notifications
        if ($request->status == StatusMoPostDefineCode::COMPLETED && $request->admin_verified == true) {
            NotificationUserJob::dispatch(
                $modelPostExists->user_id,
                "Bài đăng được duyệt và xác thực",
                'Bài đăng có tiêu đề ' . $postTitle . ' được duyệt và xác thực',
                TypeFCM::POST_APPROVED_AND_VERIFIED,
                NotiUserDefineCode::USER_NORMAL,
                $modelPostExists->id,
            );
        } else if ($request->admin_verified == true) {
            NotificationUserJob::dispatch(
                $modelPostExists->user_id,
                "Bài đăng được xác thực",
                'Bài đăng có tiêu đề ' . $postTitle . ' đã được xác thực',
                TypeFCM::POST_APPROVED,
                NotiUserDefineCode::USER_NORMAL,
                $modelPostExists->id,
            );
        } else if ($request->status == StatusMoPostDefineCode::COMPLETED && $oldStatus == StatusMoPostDefineCode::CANCEL) {
            NotificationUserJob::dispatch(
                $modelPostExists->user_id,
                "Bài đăng được duyệt",
                'Bài đăng có tiêu đề ' . $postTitle . ' được duyệt',
                TypeFCM::POST_APPROVED,
                NotiUserDefineCode::USER_NORMAL,
                $modelPostExists->id,
            );
        } else if ($request->status == StatusMoPostDefineCode::COMPLETED) {
            NotificationUserJob::dispatch(
                $modelPostExists->user_id,
                "Bài đăng được duyệt",
                'Bài đăng có tiêu đề ' . $postTitle . ' được duyệt',
                TypeFCM::POST_APPROVED,
                NotiUserDefineCode::USER_NORMAL,
                $modelPostExists->id,
            );
        } else if ($request->status == StatusMoPostDefineCode::CANCEL) {
            NotificationUserJob::dispatch(
                $modelPostExists->user_id,
                "Bài đăng bị hủy",
                'Bài đăng có tiêu đề ' . $postTitle . ' bị hủy',
                TypeFCM::POST_CANCEL,
                NotiUserDefineCode::USER_NORMAL,
                $modelPostExists->id,
            );
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => MoPost::where('id', '=',   $modelPostExists->id)->first(),
        ]);
    }

    /**
     * Xóa 1 bài đăng
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {
        $post_id = request("post_id");
        $moPostExists = MoPost::where('id', $post_id)->first();

        if ($moPostExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        $motelExist = Motel::where([
            ['id', $moPostExists->motel_id],
            ['user_id', $moPostExists->user_id]
        ])->first();
        if ($motelExist != null) {
            $motelExist->update([
                'has_post' => 0
            ]);
        }

        if ($request->user->id != $moPostExists->user_id) {
            NotificationUserJob::dispatch(
                $moPostExists->user_id,
                "Bài đăng đã bị admin xóa",
                'Bài đăng có tiêu đề ' . $moPostExists->title . '  bị admin xóa',
                TypeFCM::POST_DELETE,
                NotiUserDefineCode::USER_NORMAL,
                $moPostExists->id,
            );
        }

        $idDeleted = $moPostExists->id;
        $moPostExists->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
