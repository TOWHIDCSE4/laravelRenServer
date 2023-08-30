<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\RedisChatEventUserToUser;
use App\Helper\NotiUserDefineCode;
use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\MsgCode;
use App\Models\PersonChats;
use App\Models\User;
use App\Models\UToUMessages;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminMessageController extends Controller
{
    /**
     * Danh sách người chat với user
     * 
     * 
     * @urlParam search string tìm kiếm user chat (số điện thoại, tên) 
     */
    public function getAllPerson(Request $request, $user_id)
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

        $listPerson = PersonChats::orderBy('updated_at', 'desc')
            ->where('user_id', $user_id)
            ->when(isset($request->search), function ($query) use ($request) {
                $query->join('users', 'person_chats.to_user_id', '=', 'users.id');
                $query->where(function ($q) use ($request) {
                    $q->where('users.phone_number', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('users.name', 'LIKE', '%' . $request->search . '%');
                });
                $query->distinct();
                $query->select('person_chats.*');
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
     * Danh sách tin nhắn với 1 người
     * 
     * 
     * @urlParam search string tìm kiếm tin nhắn
     */
    public function getAllMessage(Request $request)
    {
        $to_user_id = request('user_id');
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
            ['user_id', $to_user_id],
        ])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $listMess
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
        $to_user_id = (int)request('to_user_id');
        $user = User::where('id', $to_user_id)->first();

        if (empty($user)) {
            return ResponseUtils::json([
                'code' => 400,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
                'success' => false,
            ]);
        }

        if ($request->images == null && empty($request->content)) {
            return ResponseUtils::json([
                'code' => 400,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[0],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
                'success' => false,
            ]);
        }

        // tạo mess cho ng gửi
        $mess = UToUMessages::create([
            'user_id' => $request->user->id,
            'vs_user_id' => $to_user_id,
            'content' => $request->content,
            'is_sender' => true,
            'images' => json_encode($request->images)
        ]);

        event($e = new RedisChatEventUserToUser($mess, 1));
        $personChat = PersonChats::where([
            ['user_id', $request->user->id],
            ['to_user_id', $to_user_id]
        ])->first();

        if ($personChat != null) {
            $personChat->update([
                "last_mess" => $request->content,
                "is_my_last_message" => $request->content,
                'seen' => true
            ]);
        } else {
            PersonChats::create([
                'user_id' => $request->user->id,
                'to_user_id' => $to_user_id,
                "last_mess" => $request->content,
                "is_my_last_message" => $request->content,
                'seen' => true,
            ]);
        }

        if ($request->user->id != $to_user_id) {
            $mess2 = UToUMessages::create([
                'vs_user_id' => $request->user->id,
                'user_id' => $to_user_id,
                'content' => $request->content,
                'is_sender' => false,
                'images' => json_encode($request->images),
            ]);
        }

        $personChat2 =  PersonChats::where([
            ['user_id', $to_user_id],
            ['to_user_id', $request->user->id]
        ])->first();

        if ($personChat2  != null) {
            $personChat2->update([
                "last_mess" => $request->content,
                "is_my_last_message" => $request->content,
                'seen' => true,
            ]);
        } else {
            PersonChats::create([
                "user_id" =>  $to_user_id,
                "to_user_id" => $request->user->id,
                "last_mess" => $request->content,
                "is_my_last_message" => $request->content,
                'seen' => false,
            ]);
        }

        // setup notifications
        NotificationUserJob::dispatch(
            $to_user_id,
            "Bạn có tin nhắn mới từ " . $request->user->name,
            $request->content,
            TypeFCM::NEW_MESSAGE,
            NotiUserDefineCode::USER_NORMAL,
            $mess->id,
        );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $mess
        ]);
    }
    // is my last message 
    public function getLatestMessage(){
        $mess = PersonChats::where('is_my_last_message', true)->get();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => $mess
        ]);
    }
}
