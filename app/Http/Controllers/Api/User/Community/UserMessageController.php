<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Events\RedisChatEventUserToUser;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\PaginateArr;
use App\Http\Controllers\Controller;
use App\Models\PersonChats;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use App\Models\User;
use App\Models\UToUMessages;
use App\Helper\ParamUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use App\Models\NotificationUser;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use League\CommonMark\Util\ArrayCollection;

/**
 * @group  User/Chat
 */

class UserMessageController extends Controller
{
    /** 
     * Danh sách người chat với user
     * 
     * 
     * @urlParam search string tìm kiếm user chat (số điện thoại, tên) 
     */
    public function getBoxAdminHost(Request $request)
    {
        $myHost = [];
        $adminHelper = PersonChats::where([
            ['person_chats.user_id', null],
            ['person_chats.is_helper', true],
        ])
            ->orderBy('person_chats.lasted_at', 'desc')
            ->when(isset($request->search), function ($query) use ($request) {
                $query->search($request->search);
            })
            ->distinct();

        if (!$request->user->is_host || !$request->user->is_admin) {
            $myHostId = User::join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                ])->distinct()->pluck('user_contracts.user_id');

            $myHost = PersonChats::whereIn('person_chats.to_user_id', $myHostId)
                ->where('person_chats.user_id', $request->user->id)
                ->whereNotIn('person_chats.to_user_id', (clone $adminHelper)->pluck('person_chats.to_user_id'))
                ->orderBy('person_chats.lasted_at', 'desc')
                ->select('person_chats.*')
                ->when(isset($request->search), function ($query) use ($request) {
                    $query->search($request->search);
                })
                ->get();
        }
        $adminHelper = array_values($adminHelper->get()->sortByDesc('updated_at')->toArray());

        $response = [
            'admin_helper' => $adminHelper,
            'my_host' => $myHost
        ];

        return ResponseUtils::json([
            'code' => 200,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $response
        ]);
    }
    /** 
     * Danh sách người chat với user
     * 
     * 
     * @urlParam search string tìm kiếm user chat (số điện thoại, tên) 
     */
    public function getAllPerson(Request $request)
    {
        $limit = $request->limit ?: 20;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $adminHelper = PersonChats::where([
            ['person_chats.user_id', null],
            ['person_chats.is_helper', true],
        ])
            ->pluck('person_chats.to_user_id')->toArray();

        $arrMyHost = PersonChats::join('users', 'person_chats.to_user_id', '=', 'users.id')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['user_contracts.renter_phone_number', $request->user->phone_number],
                ['person_chats.to_user_id', $request->user->id],
            ])
            ->distinct()
            ->pluck('person_chats.user_id')->toArray();

        $listPerson = PersonChats::where('user_id', $request->user->id)
            ->where('is_helper', false)
            ->whereNotIn('person_chats.to_user_id', $arrMyHost)
            ->whereNotIn('person_chats.to_user_id', $adminHelper)
            ->orderBy('person_chats.lasted_at', 'desc')
            ->select('person_chats.*')
            ->when(isset($request->search), function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => 200,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $listPerson
        ]);
    }
    /** 
     * Lấy 1 người chat với user
     * 
     */
    public function getOnePerson(Request $request)
    {
        $limit = $request->limit ?: 20;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }


        $person = PersonChats::where('id', $request->person_chat_id)
            ->first();

        if (!$person) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PERSON_CHAT_EXISTS[0],
                'msg' => MsgCode::NO_PERSON_CHAT_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => 200,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $person
        ]);
    }

    /**
     * Danh sách tin nhắn với 1 người
     * 
     * @urlParam search string tìm kiếm tin nhắn
     */
    public function getAllMessage(Request $request)
    {
        $to_user_id = request('to_user_id');
        $user = User::where('id', $to_user_id)->first();

        if (empty($user)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
                'success' => false,
            ]);
        }

        $listMess = UToUMessages::where([
            ['vs_user_id', $to_user_id],
            ['user_id', $request->user->id],
        ])
            ->orderBy('created_at', 'desc')
            ->paginate();

        $personChatExist = PersonChats::where([
            ['to_user_id', $to_user_id],
            ['user_id', $request->user->id]
        ])
            ->orderByDesc('created_at')
            ->first();
        $personChatReceiveExist = PersonChats::where([
            ['to_user_id', $request->user->id],
            ['user_id', $to_user_id]
        ])
            ->orderByDesc('created_at')
            ->first();

        if ($personChatExist != null) {
            $personChatExist->update([
                'seen' => true
            ]);
        }
        if ($personChatReceiveExist != null) {
            $personChatReceiveExist->update([
                'seen' => true
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $listMess,
            'to_user' => User::where('id', $to_user_id)->select('id', 'phone_number', 'name', 'is_admin', 'account_rank', 'is_authorized', 'avatar_image', 'is_host')->first()
        ]);
    }

    /**
     * Gửi tin nhắn
     * 
     * @bodyParam content required Nội dung
     * @bodyParam images required List danh sách ảnh sp (VD: ["linl1", "link2"])
     * Khách nhận tin nhắn reatime khai báo io socket port 6441 nhận 
     * var socket = io("http://localhost:6441")
     * socket.on("chat:message_from_customer_to_customer:1:2", function(data) {   (1:2   1 là từ customer nào gửi tới cusotmer nào nếu đang cần nhận thì 1 là người cần nhận 2 là id của bạn)
     *   console.log(data)
     *   })
     * chat:message:1   với 1 là customer_id
     * 
     */
    public function sendMessage(Request $request)
    {
        try {

            $to_user_id = (int)request('to_user_id');
            $now = Helper::getTimeNowDateTime();
            $user = User::where('id', $to_user_id)->first();

            if (empty($user)) {
                return ResponseUtils::json([
                    'code' => 400,
                    'msg_code' => MsgCode::NO_USER_EXISTS[0],
                    'msg' => MsgCode::NO_USER_EXISTS[1],
                    'success' => false,
                ]);
            }

            // tạo mess cho ng gửi

            $mess = UToUMessages::create([
                'user_id' => $request->user->id,
                'vs_user_id' => $to_user_id,
                'content' => $request->content,
                'is_sender' => true,
                'images' => json_encode($request->images),
                'list_mo_post_id' => json_encode($request->list_mo_post_id ?? [])
            ]);

            $personChat = PersonChats::where([
                ['user_id', $request->user->id],
                ['to_user_id', $to_user_id]
            ])->first();

            if ($personChat != null) {
                $personChat->update([
                    "last_mess" => $request->content,
                    'seen' => true, //
                    'lasted_at' => $now->format('Y-m-d H:i:s'),
                    'last_list_mo_post_id' => json_encode($request->list_mo_post_id ?? [])
                ]);
            } else {
                PersonChats::create([
                    'user_id' => $request->user->id,
                    'to_user_id' => $to_user_id,
                    "last_mess" => $request->content,
                    'seen' => false, //
                    'lasted_at' => $now->format('Y-m-d H:i:s'),
                    'last_list_mo_post_id' => json_encode($request->list_mo_post_id ?? [])
                ]);
            }

            if ($request->user->id != $to_user_id) {
                $mess2 = UToUMessages::create([
                    'vs_user_id' => $request->user->id,
                    'user_id' => $to_user_id,
                    'content' => $request->content,
                    'is_sender' => false,
                    'images' => json_encode($request->images),
                    'list_mo_post_id' => json_encode($request->list_mo_post_id ?? [])
                ]);
            }

            $personChat2 =  PersonChats::where([
                ['user_id', $to_user_id],
                ['to_user_id', $request->user->id]
            ])->first();

            if ($personChat2 != null) {

                $personChat2->update([
                    "last_mess" => $request->content,
                    'seen' => false, //
                    'lasted_at' => $now->format('Y-m-d H:i:s'),
                    'last_list_mo_post_id' => json_encode($request->list_mo_post_id ?? [])
                ]);
                DB::commit();
            } else {
                // for	($currentAttempt = 0; $currentAttempt < 3; $currentAttempt++) {
                // DB::beginTransaction();
                // try {
                PersonChats::create([
                    "user_id" =>  $to_user_id,
                    "to_user_id" => $request->user->id,
                    "last_mess" => $request->content,
                    'seen' => false,
                    'lasted_at' => $now->format('Y-m-d H:i:s'),
                    'last_list_mo_post_id' => json_encode($request->list_mo_post_id ?? [])
                ]);
                //     DB::commit();
                //     // return $currentAttempt;
                // } catch (Exception $e) {
                //     DB::rollBack();
                //     // Nếu exception là DeadLock thì mới retry!!
                //     // if ($this->causedByDeadlock($e) && $currentAttempt < 3) {
                //     //     // ToDo SomeThing (Log,...)
                //     //     continue;
                //     // }
                //     throw $e;
                // }
                // // }
            }

            event($e = new RedisChatEventUserToUser($mess, 1));

            //setup notifications
            NotificationUserJob::dispatch(
                $to_user_id,
                "Bạn có tin nhắn mới từ " . $request->user->name,
                $request->content,
                TypeFCM::NEW_MESSAGE,
                NotiUserDefineCode::USER_NORMAL,
                $request->user->id,
            );
        } catch (\Throwable $th) {
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $mess,
        ]);
    }
}
