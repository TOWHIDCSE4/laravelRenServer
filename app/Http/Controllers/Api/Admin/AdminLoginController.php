<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\PhoneUtils;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Api\HandleReceiverSmsController;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\MsgCode;
use App\Models\OtpCodePhone;
use App\Models\SessionAdmin;
use App\Models\SessionEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @group  Admin/Đăng nhập
 */
class AdminLoginController extends Controller
{
    /**
     * Login
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam password string required Password
     */
    public function login(Request $request)
    {
        $checkAdminExists = Admin::where('phone_number', $request->phone_number)->first();


        //B1 xác thực tồn tại
        if ($checkAdminExists != null && Hash::check($request->password, $checkAdminExists->password)) {

            $checkTokenExists = SessionAdmin::where(
                'admin_id',
                $checkAdminExists->id
            )->first();

            //B2 tạo token
            if (empty($checkTokenExists)) {
                $adminSession = SessionAdmin::create([
                    'token' => Str::random(40),
                    'refresh_token' => Str::random(40),
                    'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                    'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                    'admin_id' => $checkAdminExists->id
                ]);
            } else {
                $adminSession =  $checkTokenExists;
            }

            return ResponseUtils::json([
                'code' => 200,
                'success' => true,
                'data' => $adminSession,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ]);
        }

        //Nếu không phải admin thì là employee
        $checkEmployeeExists = null;

        if ($request->phone_number != null) {
            $checkEmployeeExists = Employee::where('username', $request->phone_number)
                ->first();
        }

        //B1 xác thực tồn tại
        if ($checkEmployeeExists != null && Hash::check($request->password, $checkEmployeeExists->password)) {

            $checkTokenExists = SessionEmployee::where(
                'employee_id',
                $checkEmployeeExists->id
            )->first();


            //B2 tạo token
            if (empty($checkTokenExists)) {


                $userSession = SessionEmployee::create([
                    'token' => Str::random(40),
                    'refresh_token' => Str::random(40),
                    'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                    'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                    'employee_id' => $checkEmployeeExists->id
                ]);
            } else {
                $userSession =  $checkTokenExists;
            }

            return ResponseUtils::json([
                'code' => 200,
                'success' => true,
                'data' => $userSession,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
            ]);
        }


        return ResponseUtils::json([
            'code' => 401,
            'success' => false,
            'msg_code' => MsgCode::WRONG_ACCOUNT_OR_PASSWORD[0],
            'msg' => MsgCode::WRONG_ACCOUNT_OR_PASSWORD[1],
        ]);
    }


    /**
     * Lấy lại mật khẩu
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam password string required Mật khẩu mới
     * @bodyParam otp string gửi tin nhắn (DV SAHA gửi tới 8085)
     */
    public function reset_password(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);

        if (
            $phone == null &&
            PhoneUtils::isNumberPhoneValid($phone) == false
        ) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        $admin = Admin::where('phone_number', $phone)
            ->first();

        if ($admin == null) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_PHONE_NUMBER_EXISTS[0],
                'msg' => MsgCode::NO_PHONE_NUMBER_EXISTS[1],
            ]);
        }

        $otpExis = OtpCodePhone::where('phone', $phone)
            ->where('otp', $request->otp)
            ->first();
        if ($otpExis == null) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_OTP[0],
                'msg' => MsgCode::INVALID_OTP[1],
            ]);
        }

        if (HandleReceiverSmsController::has_expired_otp($phone)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::EXPIRED_PIN_CODE[0],
                'msg' => MsgCode::EXPIRED_PIN_CODE[1],
            ]);
        }

        if (
            strlen($request->password) < 6
        ) {

            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
            ]);
        }

        SessionAdmin::where('admin_id',  $admin->id)->delete();

        $admin->update(
            [
                'password' => bcrypt($request->password)
            ]
        );

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }


    /**
     * Thay đổi mật khẩu
     * @bodyParam password string required Mật khẩu mới
     */
    public function change_password(Request $request)
    {


        $oldPassword = $request->old_password;
        $newPassword = $request->new_password;


        $admin = $request->admin;

        if ($admin != null) {
            if (!Hash::check($oldPassword, $admin->password)) {
                return ResponseUtils::json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_OLD_PASSWORD[0],
                    'msg' => MsgCode::INVALID_OLD_PASSWORD[1],
                ]);
            }

            if (
                strlen($newPassword) < 6
            ) {

                return ResponseUtils::json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                    'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
                ]);
            }

            $admin->update(
                [
                    'password' => bcrypt($newPassword)
                ]
            );
        } else {

            $checkEmployeeExists = Employee::where('id', $request->employee->id)
                ->first();

            if ($checkEmployeeExists != null && Hash::check($request->old_password, $checkEmployeeExists->password)) {
                $checkEmployeeExists->update(
                    [
                        'password' => bcrypt($newPassword)
                    ]
                );
            } else {

                return ResponseUtils::json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                    'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
                ]);
            }
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }

    /**
     * Kiểm tra email,phone_number đã tồn tại
     * Sẽ ưu tiên kiểm tra phone_number (kết quả true tồn tại, false không tồn tại)
     * @bodyParam phone_number required phone_number
     * @bodyParam email string required email
     */
    public function check_exists(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);
        $email = $request->email;

        $list_check = [];
        $admin = Admin::where('store_id', $request->store->id)->where('phone_number', $phone)->first();
        if ($admin != null) {

            array_push($list_check, [
                "name" => "phone_number",
                "value" => true
            ]);
        } else {
            array_push($list_check, [
                "name" => "phone_number",
                "value" => false
            ]);
        }

        $admin2 = Admin::where('store_id', $request->store->id)->where('email', $email)->first();
        if ($admin2 != null) {


            array_push($list_check, [
                "name" => "email",
                "value" => true
            ]);
        } else {
            array_push($list_check, [
                "name" => "email",
                "value" => false
            ]);
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $list_check
        ]);
    }
}
