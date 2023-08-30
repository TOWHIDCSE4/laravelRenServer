<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\NotificationUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NotificationUserController extends Controller
{
    public function getAll(Request $request)
    {
        $totalUnread = 0;

        $queryNotification = NotificationUser::where(function ($query) {
            if (request('user')->is_admin == true) {
                if (request('user')->is_host == true) {
                    $query->where('user_id', request('user')->id)
                        ->orWhereNull('user_id');
                    $query->whereIn('role', [
                        NotiUserDefineCode::USER_IS_HOST,
                        NotiUserDefineCode::USER_IS_ADMIN,
                        NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                    ]);
                } else {
                    $query->where('user_id', request('user')->id)
                        ->orWhereNull('user_id');
                    $query->whereIn('role', [
                        NotiUserDefineCode::USER_IS_ADMIN,
                        NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                    ]);
                }
            } else if (request('user')->is_host == true) {
                $query->where('user_id', request('user')->id)
                    ->orWhereNull('user_id');
                $query->whereIn('role', [
                    NotiUserDefineCode::USER_IS_HOST,
                    NotiUserDefineCode::ALL_USER_IN_SYSTEM
                ]);
            } else {
                $query->where('user_id', request('user')->id)
                    ->orWhereNull('user_id');
                $query->whereIn('role', [
                    NotiUserDefineCode::USER_NORMAL,
                    NotiUserDefineCode::ALL_USER_IN_SYSTEM
                ]);
            }
        })
            ->where('notification_users.created_at', '>=', request('user')->created_at);
        $listNotification = $queryNotification->orderBy('created_at', 'desc')
            ->distinct()
            ->paginate(20);

        $ReadNotiAdmins =  DB::table('read_noti_admins')
            ->where('user_id', $request->user->id)
            ->pluck('noti_user_id');
        $totalUnread = $queryNotification->where('unread', false)->whereNotIN('id', $ReadNotiAdmins)->count();



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => [
                "total_unread" => $totalUnread,
                "list_notification" => $listNotification
            ],
        ]);
    }

    /**
     * Đã đọc tất cả
     * 
     */
    public function readAll(Request $request)
    {
        $now = Helper::getTimeNowDateTime();
        $listNotiReadAll = NotificationUser::where('user_id', $request->user->id)->orWhereNull('user_id');

        $listNotiReadAll->update(['unread' => false]);

        $ReadNotiAdmins =  DB::table('read_noti_admins')
            ->where('user_id', $request->user->id)
            ->pluck('noti_user_id');

        $getListNotIn = NotificationUser::where('role', NotiUserDefineCode::ALL_USER_IN_SYSTEM)
            ->where(function ($query) {
                if (request('user')->is_admin == true) {
                    if (request('user')->is_host == true) {
                        $query->where('user_id', request('user')->id)
                            ->orWhereNull('user_id');
                        $query->whereIn('role', [
                            NotiUserDefineCode::USER_IS_HOST,
                            NotiUserDefineCode::USER_IS_ADMIN,
                            NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                        ]);
                    } else {
                        $query->where('user_id', request('user')->id)
                            ->orWhereNull('user_id');
                        $query->whereIn('role', [
                            NotiUserDefineCode::USER_IS_ADMIN,
                            NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                        ]);
                    }
                } else if (request('user')->is_host == true) {
                    $query->where('user_id', request('user')->id)
                        ->orWhereNull('user_id');
                    $query->whereIn('role', [
                        NotiUserDefineCode::USER_IS_HOST,
                        NotiUserDefineCode::ALL_USER_IN_SYSTEM
                    ]);
                } else {
                    $query->where('user_id', request('user')->id)
                        ->orWhereNull('user_id');
                    $query->whereIn('role', [
                        NotiUserDefineCode::USER_NORMAL,
                        NotiUserDefineCode::ALL_USER_IN_SYSTEM
                    ]);
                }
            })
            ->whereNotIn('id', $ReadNotiAdmins)
            ->pluck('id');

        $listArr = [];
        foreach ($getListNotIn as $readNotiId) {
            array_push($listArr, [
                'user_id' => $request->user->id,
                'noti_user_id' => $readNotiId,
                'created_at' => $now->format('y-m-d H:i:s'),
                'updated_at' => $now->format('y-m-d H:i:s')
            ]);
        }

        if ($listArr != null) {
            DB::table('read_noti_admins')->insert($listArr);
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }

    public function readANoti(Request $request, $id)
    {
        $now = Helper::getTimeNowDateTime();
        if ($request->user != null) {
            NotificationUser::where('id', $id)
                ->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user->id)
                        ->orWhereNull('user_id');
                })
                ->update(['unread' => false]);

            $notiNotIn = NotificationUser::where('id', $id)
                ->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user->id)
                        ->orWhereNull('user_id');
                })
                ->first();

            $noTiExist = DB::table('read_noti_admins')->where([
                ['noti_user_id', $id],
                ['user_id', $request->user->id],
            ])->exists();

            if ($notiNotIn != null && !$noTiExist) {
                DB::table('read_noti_admins')->insert([
                    'user_id' => $request->user->id,
                    'noti_user_id' => $notiNotIn->id,
                    'created_at' => $now->format('y-m-d H:i:s'),
                    'updated_at' => $now->format('y-m-d H:i:s')
                ]);
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }

    /**
     * Lấy tổng số lượng thông báo chưa đọc 
     */
    function getNotiUnread(Request $request)
    {
        $dataRes = [];
        $reqUserIds = explode(',', $request->user_ids);
        if (count($reqUserIds) > 0) {
            $userIds = DB::table('users')->whereIn('id', $reqUserIds)->get();
            foreach ($userIds as $userId) {
                $totalUnread = 0;

                $ReadNotiAdmins =  DB::table('read_noti_admins')
                    ->where('user_id', $userId->id)
                    ->pluck('noti_user_id');

                $totalUnread = NotificationUser::where(function ($query) use ($userId) {
                    if ($userId->is_admin == true) {
                        if ($userId->is_host == true) {
                            $query->where('user_id', $userId->id)
                                ->orWhereNull('user_id');
                            $query->whereIn('role', [
                                NotiUserDefineCode::USER_IS_HOST,
                                NotiUserDefineCode::USER_IS_ADMIN,
                                NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                            ]);
                        } else {
                            $query->where('user_id', $userId->id)
                                ->orWhereNull('user_id');
                            $query->whereIn('role', [
                                NotiUserDefineCode::USER_IS_ADMIN,
                                NotiUserDefineCode::ALL_USER_IN_SYSTEM,
                            ]);
                        }
                    } else if ($userId->is_host == true) {
                        $query->where('user_id', $userId->id)
                            ->orWhereNull('user_id');
                        $query->whereIn('role', [
                            NotiUserDefineCode::USER_IS_HOST,
                            NotiUserDefineCode::ALL_USER_IN_SYSTEM
                        ]);
                    } else {
                        $query->where('user_id', $userId->id)
                            ->orWhereNull('user_id');
                        $query->whereIn('role', [
                            NotiUserDefineCode::USER_NORMAL,
                            NotiUserDefineCode::ALL_USER_IN_SYSTEM
                        ]);
                    }
                })
                    ->where('notification_users.created_at', '>=', $userId->created_at)
                    ->where('unread', true)
                    ->whereNotIN('id', $ReadNotiAdmins)
                    ->count();

                array_push($dataRes, [
                    'user_id' => $userId->id,
                    'noti_unread' => $totalUnread
                ]);
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }
}
