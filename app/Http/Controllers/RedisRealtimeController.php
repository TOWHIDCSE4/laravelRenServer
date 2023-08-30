<?php

namespace App\Http\Controllers;

use App\Events\RedisChatEvent;
use App\Events\RedisRealtimeBadgesEvent;
use App\Helper\Helper;
use App\Helper\ResponseUtils;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use App\Jobs\PushNotificationCustomerJob;
use App\Jobs\PushNotificationJob;
use App\Jobs\PushNotificationUserJob;
use App\Models\User;
use App\Models\Message;
use App\Models\MsgCode;
use App\Models\RoomChat;
use App\Models\UserDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Chat
 */
class RedisRealtimeController extends Controller
{


    /**
     * Chat đến khách hàng
     * Khách nhận tin nhắn khai báo io socket port 6441 nhận 
     * var socket = io("http://localhost:6441")
     * socket.on("chat:message_from_user:1", function(data) {
     *   console.log(data)
     *   })
     * chat:message:1   với 1 là user_id

     */
    public function sendMessageUser(Request $request)
    {

        $user_id = $request->route()->parameter('user_id');

        $user = User::where('id', $user_id)->first();
        if (empty($user)) {
            return ResponseUtils::json([
                'code' => 400,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
                'success' => false,
            ]);
        }

        if ($request->content == null && $request->image == null) {
            return ResponseUtils::json([
                'code' => 400,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[0],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
                'success' => false,
            ]);
        }

        $messages = Message::create([
            'user_id' => $user_id,
            'content' => $request->content,
            'image' => $request->image,
            'device_id' => $request->device_id,
            'is_user' => true,
        ]);

        $lastRoom = RoomChat::where('user_id', $user_id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'user_unread' => $lastRoom->user_unread + 1,
            ]);
        } else {
            $lastRoom = RoomChat::create([
                'user_id' => $user_id,
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'created_at' => Helper::getTimeNowDateTime(),
                'user_unread' => 1,
            ]);
        }

        PushNotificationCustomerJob::dispatch(
            $user_id,
            "Bạn có tin nhắn mới",
            $request->content,
            TypeFCM::NEW_MESSAGE,
            null
        );

        event($e = new RedisChatEvent($messages, $lastRoom->user_unread));

        return ResponseUtils::json([
            'code' => 200,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => Message::where('id', $messages->id)->first(),
        ]);
    }


    /**
     * Khách hàng chat cho user
     * Khách nhận tin nhắn reatime khai báo io socket port 6441 nhận 
     * var socket = io("http://localhost:6441")
     * socket.on("chat:message_from:user:1", function(data) {
     *   console.log(data)
     *   })
     * chat:message:1   với 1 là user_id
     * Lấy tin nhắn chưa đọc realtime
     *  socket.on("chat:message_from_customer",    
     */
    public function customerSendToUser(Request $request)
    {


        if ($request->content == null  && $request->image == null) {
            return ResponseUtils::json([
                'code' => 400,
                'msg_code' => MsgCode::CONTENT_IS_REQUIRED[0],
                'msg' => MsgCode::CONTENT_IS_REQUIRED[1],
                'success' => false,
            ]);
        }

        $messages = Message::create([
            'user_id' => $request->user->id,
            'content' => $request->content,
            'image' => $request->image,
            'device_id' => $request->device_id,
            'is_user' => false,
        ]);




        $name = $request->user->name == null ? $request->user->phone_number : $request->user->name;


        NotificationUserJob::dispatch(
            $request->store->user_id,
            'Shop ' . $request->store->name . ' tin nhắn từ ' . $name,
            substr($messages->content, 0, 80),
            TypeFCM::NEW_MESSAGE,
            $request->user->id,
            null
        );


        $lastRoom = RoomChat::where('user_id', $request->user->id)->where('store_id', $request->store->id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'user_unread' => $lastRoom->user_unread + 1,
            ]);
        } else {
            $lastRoom = RoomChat::create([
                'user_id' => $request->user->id,
                'messages_id' => $messages->id,
                'updated_at' => Helper::getTimeNowDateTime(),
                'created_at' => Helper::getTimeNowDateTime(),
                'user_unread' => 1,
            ]);
        }

        $unread = RoomChat::where('store_id', $request->store->id)->sum('user_unread');
        event($e = new RedisChatEvent($messages, $unread));


        event($e = new RedisRealtimeBadgesEvent($request->store->user_id, $request->staff == null ? null : $request->staff->id, null));

        return ResponseUtils::json([
            'code' => 200,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'success' => true,
            'data' => Message::where('id', $messages->id)->first(),
        ]);
    }

    /**
     * Danh sách tổng quan tin nhắn
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     **/

    public function getAll(Request $request, $id)
    {

        $posts = RoomChat::orderBy('updated_at', 'desc')
            ->paginate(20);


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $posts,
        ]);
    }


    /**
     * Danh sách tin nhắn với 1 khách
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     **/

    public function getAllOneUser(Request $request)
    {
        $user_id = $request->route()->parameter('user_id');

        $user = User::where('id', $user_id)->first();
        if (empty($user)) {
            return ResponseUtils::json([
                'code' => 400,
                'msg_code' => MsgCode::NO_USER_EXISTS[0],
                'msg' => MsgCode::NO_USER_EXISTS[1],
                'success' => false,
            ]);
        }

        $messages = Message::where(
            'user_id',
            $user_id
        )->orderBy('created_at', 'desc')
            ->paginate(20);

        $lastRoom = RoomChat::where('user_id', $user_id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'user_unread' => 0,
            ]);
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $messages,
        ]);
    }

    /**
     * Danh sách tin nhắn với user
     * @queryParam  page Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
     **/

    public function getAllMessageOfUser(Request $request, $id)
    {

        $messages = Message::where(
            'user_id',
            $request->user->id
        )->orderBy('created_at', 'desc')
            ->paginate(20);

        $lastRoom = RoomChat::where('user_id', $request->user->id)
            ->first();
        if (!empty($lastRoom)) {
            $lastRoom->update([
                'user_unread' => 0,
            ]);
        }
        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $messages,
        ]);
    }
}
