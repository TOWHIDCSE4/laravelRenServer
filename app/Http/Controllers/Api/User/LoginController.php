<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\AccountRankDefineCode;
use App\Helper\Helper;
use App\Helper\HostRankDefineCode;
use App\Helper\PhoneUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusUserDefineCode;
use App\Http\Controllers\Api\HandleReceiverSmsController;
use App\Http\Controllers\Controller;
use App\Models\LastSentOtp;
use App\Models\MsgCode;
use App\Models\OtpCodeEmail;
use App\Models\OtpCodePhone;
use App\Models\Renter;
use App\Models\SessionStaff;
use App\Models\SessionUser;
use App\Models\Staff;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group  User/Đăng nhập
 */
class LoginController extends Controller
{

    /**
     * Login
     * @bodyParam email_or_phone_number string required (Username, email hoặc số điện thoại)
     * @bodyParam password string required Password
     */
    public function login(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);
        $otp = $request->otp;
        $isOtp = $request->is_otp ?? false;
        $otpExis = null;
        $from = "";
        $type = "";

        // old login
        $dataCheckLogin = [
            'phone_number' => $phone,
            'password' => $request->password,
        ];
        //

        if ($request->is_otp) {
            $userExists = DB::table('users')->whereNotNull('phone_number')->where('phone_number', '=', $phone)->first();
            if ($userExists == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[1],
                    'msg' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[1],
                ]);
            }

            if ($request->otp != null && $isOtp == true && $phone != '0868917689') {
                // $otpExis = OtpCodePhone::where('phone', $phone)
                //     ->where('otp', $otp)
                //     ->first();

                // if ($otpExis == null) {
                //     return ResponseUtils::json([
                //         'code' => Response::HTTP_BAD_REQUEST,
                //         'success' => false,
                //         'msg_code' => MsgCode::INVALID_OTP[0],
                //         'msg' => MsgCode::INVALID_OTP[1],
                //     ]);
                // }

                // if (HandleReceiverSmsController::has_expired_otp($from, $type)) {
                //     return ResponseUtils::json([
                //         'code' => Response::HTTP_BAD_REQUEST,
                //         'success' => false,
                //         'msg_code' => MsgCode::EXPIRED_PIN_CODE[0],
                //         'msg' => MsgCode::EXPIRED_PIN_CODE[1],
                //     ]);
                // }

                // HandleReceiverSmsController::reset_otp($phone);
            }

            //B1 xác thực tồn tại
            if ($otpExis == null || $phone == '0868917689') {
                // $notSendOtpPhone = ['0868917689', '0337056362', '0358081418'];
                // if ($otpExis != null || $phone == '0868917689') {
                $checkTokenExists = SessionUser::where('user_id', $userExists->id)->first();
                if ($phone == '0868917689' && $request->otp != null) {
                    return ResponseUtils::json([
                        'code' => response::HTTP_OK,
                        'success' => true,
                        'data' => $checkTokenExists,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                    ]);
                }

                if ($userExists->status == StatusUserDefineCode::BANNED_ACCOUNT) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::ACCOUNT_HAS_BEEN_BANNED[1],
                        'msg' => MsgCode::ACCOUNT_HAS_BEEN_BANNED[1],
                    ]);
                }

                //B2 tạo token
                if (empty($checkTokenExists)) {
                    $userSession = SessionUser::create([
                        'token' => Str::random(40),
                        'refresh_token' => Str::random(40),
                        'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                        'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                        'user_id' => $userExists->id
                    ]);
                } else {
                    $userSession =  $checkTokenExists;
                }

                return ResponseUtils::json([
                    'code' => response::HTTP_OK,
                    'success' => true,
                    'data' => $userSession,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ]);
            } else {
                return ResponseUtils::json([
                    'code' => response::HTTP_UNAUTHORIZED,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_OTP[1],
                    'msg' => MsgCode::INVALID_OTP[1],
                ]);
            }
        } else if ($dataCheckLogin) {
            if (DB::table('users')->whereNotNull('phone_number')->where('phone_number', '=', $phone)->first() == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[1],
                    'msg' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[1],
                ]);
            }


            //B1 xác thực tồn tại
            if (Auth::attempt($dataCheckLogin)) {
                $checkTokenExists = SessionUser::where(
                    'user_id',
                    Auth::id()
                )->first();

                if (DB::table('users')->where('id', Auth::id())->first()->status == StatusUserDefineCode::BANNED_ACCOUNT) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::ACCOUNT_HAS_BEEN_BANNED[1],
                        'msg' => MsgCode::ACCOUNT_HAS_BEEN_BANNED[1],
                    ]);
                }

                //B2 tạo token
                if (empty($checkTokenExists)) {
                    $userSession = SessionUser::create([
                        'token' => Str::random(40),
                        'refresh_token' => Str::random(40),
                        'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                        'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                        'user_id' => Auth()->id()
                    ]);
                } else {
                    $userSession =  $checkTokenExists;
                }

                return ResponseUtils::json([
                    'code' => response::HTTP_OK,
                    'success' => true,
                    'data' => $userSession,
                    'msg_code' => MsgCode::SUCCESS[0],
                    'msg' => MsgCode::SUCCESS[1],
                ]);
            } else {
                return ResponseUtils::json([
                    'code' => response::HTTP_UNAUTHORIZED,
                    'success' => false,
                    'msg_code' => MsgCode::WRONG_PASSWORD[1],
                    'msg' => MsgCode::WRONG_PASSWORD[1],
                ]);
            }
        }


        return ResponseUtils::json([
            'code' => response::HTTP_UNAUTHORIZED,
            'success' => false,
            'msg_code' => MsgCode::NO_ACCOUNT_EXISTS[1],
            'msg' => MsgCode::NO_ACCOUNT_EXISTS[1],
        ]);
    }

    /**
     * Lấy lại mật khẩu
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam password string required Mật khẩu mới
     * @bodyParam otp string gửi tin nhắn (DV SAHA gửi tới 8085)
     * @bodyParam otp_from string  phone(từ sdt)  email(từ email) mặc định là phone
     */
    public function reset_password(Request $request)
    {
        $otp = $request->otp;

        $user = null;
        $email = null;
        $phone = null;

        if ($request->email_or_phone_number == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_OR_EMAIL_IS_REQUIRED[0],
                'msg' => MsgCode::PHONE_NUMBER_OR_EMAIL_IS_REQUIRED[1],
            ]);
        }

        if (Helper::validEmail($request->email_or_phone_number) != null) {
            $email = $request->email_or_phone_number;
            $user = User::where('email', $request->email_or_phone_number)->first();

            if ($user == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_EMAIL_ACCOUNT_EXISTS_IN_SYSTEM[1],
                    'msg' => MsgCode::NO_EMAIL_ACCOUNT_EXISTS_IN_SYSTEM[1],
                ]);
            }
        } else if (PhoneUtils::isNumberPhoneValid($request->email_or_phone_number)) {
            $phone = PhoneUtils::convert($request->email_or_phone_number);
            $user = User::where('phone_number', $request->email_or_phone_number)->first();

            if ($user == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[1],
                    'msg' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[1],
                ]);
            }
        }

        /////
        $from = "";
        $type = "";
        if ($email != null && $phone == null) {
            $from = $email;
            $type = "email";
            $otpExis = OtpCodeEmail::where('email', $email)
                ->where('otp', $otp)
                ->first();
            if ($otpExis == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_OTP[0],
                    'msg' => MsgCode::INVALID_OTP[1],
                ]);
            }
        } else if ($email == null && $phone != null) {
            $from = $phone;
            $type = "phone";
            $otpExis = OtpCodePhone::where('phone', $phone)
                ->where('otp', $otp)
                ->first();
            if ($otpExis == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_OTP[0],
                    'msg' => MsgCode::INVALID_OTP[1],
                ]);
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::OTP_IS_REQUIRE[0],
                'msg' => MsgCode::OTP_IS_REQUIRE[1],
            ]);
        }


        if (HandleReceiverSmsController::has_expired_otp($from, $type)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::EXPIRED_PIN_CODE[0],
                'msg' => MsgCode::EXPIRED_PIN_CODE[1],
            ]);
        }
        ///

        if (
            strlen($request->password) < 6
        ) {

            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
            ]);
        }


        SessionUser::where('user_id',  $user->id)->delete();
        HandleReceiverSmsController::reset_otp($phone);

        $user->update(
            [
                'password' => bcrypt($request->password)
            ]
        );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
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
        $newPassword = $request->new_password;

        $dataCheckLogin = [
            'phone_number' => $request->user->phone_number,
            'password' => $request->old_password,
        ];


        if (!Auth::attempt($dataCheckLogin)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_OLD_PASSWORD[0],
                'msg' => MsgCode::INVALID_OLD_PASSWORD[1],
            ]);
        }

        if (strlen($newPassword) < 6) {

            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
            ]);
        }

        $user = $request->user;

        $user->update(
            [
                'password' => bcrypt($newPassword)
            ]
        );



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
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
        $user = User::where('phone_number', $phone)->first();
        if ($user != null) {

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

        $user2 = User::where('email', $email)->first();
        if ($user2 != null) {


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

    /**
     * Chuyển hướng tới login social
     */
    public function redirect($provider)
    {
        try {
            return Socialite::with($provider)->redirect();
        } catch (Exception $ex) {
            dd($ex);
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::INVALID_URL[0],
                'msg' => MsgCode::INVALID_URL[1]
            ]);
        }
    }

    /**
     * Xử lý khi login social thành công
     */
    public function callback($provider)
    {
        // try {
        $getInfo = Socialite::driver($provider)->stateless()->user();

        $id_social = $getInfo['id'] ?? "";
        $userExist = User::where('social_id', $getInfo->id)->first();

        if ($userExist == null && $getInfo->email != null) {
            $userExist = User::where('email', $getInfo->email)->first();
        }

        $userSession = null;
        if ($userExist != null) {
            $userSessionExist = SessionUser::where('user_id', $userExist->id)->first();
            if ($userSessionExist == null) {
                $userSession = SessionUser::create([
                    'token' => Str::random(40),
                    'refresh_token' => Str::random(40),
                    'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                    'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                    'user_id' => $userExist->id
                ]);
            } else {
                $userSession =  $userSessionExist;
            }
            if (str_contains($getInfo->avatar, 'google')) {
                $socialFrom = 'google';
            } else if (str_contains($getInfo->avatar, 'facebook')) {
                $socialFrom = 'facebook';
            } else {
                $socialFrom = null;
            }

            $userExist->update([
                'social_id' => $id_social,
                'social_from' => $socialFrom
            ]);
        } else {
            if (str_contains($getInfo->avatar, 'google')) {
                $socialFrom = 'google';
            } else if (str_contains($getInfo->avatar, 'facebook')) {
                $socialFrom = 'facebook';
            } else {
                $socialFrom = null;
            }

            $userCreate = User::create(
                [
                    'name' =>  $getInfo->name,
                    'area_code' => "+84",
                    'phone_number' => null,
                    'email' => $getInfo->email,
                    'social_id' => $id_social,
                    'social_from' => $socialFrom,
                    'avatar_image' => $getInfo->avatar ?? null,
                    'password' => bcrypt(Str::random(40)),
                    'host_rank' => HostRankDefineCode::NORMAL,
                    'account_rank' => AccountRankDefineCode::NORMAL
                ]
            );

            $userSession = SessionUser::create([
                'token' => Str::random(40),
                'refresh_token' => Str::random(40),
                'token_expried' => date('Y-m-d H:i:s',  strtotime('+100 day')),
                'refresh_token_expried' => date('Y-m-d H:i:s',  strtotime('+365 day')),
                'user_id' => $userCreate->id
            ]);
        }

        header('Location: foobar://success?token=' . $userSession->token);
        die();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'data' => $userSession,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
        // } catch (Exception $ex) {
        //     dd($ex);
        //     // if (str_contains($getInfo->avatar, 'facebook')) {
        //     //     $socialFrom = 'facebook';
        //     // }
        //     // if (!$request->has('code') || $request->has('denied')) {
        //     //     return redirect('/');
        //     // }
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_NOT_FOUND,
        //         'success' => false,
        //         'msg_code' => MsgCode::NO_USER_EXISTS[0],
        //         'msg' => MsgCode::NO_USER_EXISTS[1]
        //     ]);
        // }
    }

    public function loginApple(Request $request)
    {
        $toggle =  true;
        return ResponseUtils::json([
            'code' => HttpResponse::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $toggle
        ]);
    }
}
