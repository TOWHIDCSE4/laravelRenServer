<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\AccountRankDefineCode;
use App\Helper\DatetimeUtils;
use App\Helper\PhoneUtils;
use App\Http\Controllers\Api\HandleReceiverSmsController;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationAdminJob;
use App\Models\User;
use App\Models\MsgCode;
use App\Models\OtpCodeEmail;
use App\Models\OtpCodePhone;
use Illuminate\Support\Facades\DB;
use App\Helper\Helper;
use App\Helper\HostRankDefineCode;
use App\Helper\ResponseUtils;
use App\Helper\ServiceUnitDefineCode;
use App\Helper\StatusUserDefineCode;
use App\Models\CollaboratorReferMotel;
use App\Models\Renter;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group  User/Đăng ký
 */
class RegisterController extends Controller
{
    /**
     * check account
     * 
     * @bodyParam email_or_phone_number string required
     * @bodyParam password string required Password
     */
    public function checkAccount(Request $request)
    {

        $email = null;
        $phone = null;

        if (Helper::validEmail($request->email_or_phone_number)) {
            $email = $request->email_or_phone_number;
        } else if (PhoneUtils::isNumberPhoneValid($request->email_or_phone_number)) {
            $phone = PhoneUtils::convert($request->email_or_phone_number);
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        if (strlen($request->password) < 6) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[0],
                'msg' => MsgCode::PASSWORD_NOT_LESS_THAN_6_CHARACTERS[1],
            ]);
        }

        if ($request->referral_code != null) {
            $referralCode = PhoneUtils::convert($request->referral_code);

            $userReferralCodeExist = User::where([
                ['phone_number', $referralCode],
                ['account_rank', AccountRankDefineCode::LOYAL]
            ])->first();

            if ($userReferralCodeExist == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }

            if ($userReferralCodeExist->is_admin == true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }

            if ($userReferralCodeExist->is_host == true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }
        }

        if ($phone != null) {
            if (DB::table('users')->where('phone_number', $phone)->exists()) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                    'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
                ]);
            }
        } else {
            if (DB::table('users')->where('email', $email)->exists()) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::EMAIL_ALREADY_EXISTS[0],
                    'msg' => MsgCode::EMAIL_ALREADY_EXISTS[1],
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
     * check username
     * 
     * @bodyParam email_or_phone_number string required
     */
    public function checkUsername(Request $request)
    {
        $email = null;
        $phone = null;
        $type = $request->type ?? StatusUserDefineCode::TYPE_REGISTER;

        if (Helper::validEmail($request->email_or_phone_number)) {
            $email = $request->email_or_phone_number;
        } else if (PhoneUtils::isNumberPhoneValid($request->email_or_phone_number)) {
            $phone = PhoneUtils::convert($request->email_or_phone_number);
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        if ($phone != null) {
            if (DB::table('users')->where('phone_number', $phone)->exists()) {
                if ($type == StatusUserDefineCode::TYPE_REGISTER) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                        'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
                    ]);
                } else {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_OK,
                        'success' => true,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                    ]);
                }
            } else {
                if ($type == StatusUserDefineCode::TYPE_LOGIN) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[0],
                        'msg' => MsgCode::NO_PHONE_NUMBER_ACCOUNT_EXISTS_IN_SYSTEM[1],
                    ]);
                }
            }
        } else {
            if (DB::table('users')->where('email', $email)->exists()) {
                if ($type == StatusUserDefineCode::TYPE_REGISTER) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::EMAIL_ALREADY_EXISTS_IN_SYSTEM[0],
                        'msg' => MsgCode::EMAIL_ALREADY_EXISTS_IN_SYSTEM[1],
                    ]);
                } else {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_OK,
                        'success' => true,
                        'msg_code' => MsgCode::SUCCESS[0],
                        'msg' => MsgCode::SUCCESS[1],
                    ]);
                }
            }
        }

        if ($request->referral_code != null) {
            $referralCode = PhoneUtils::convert($request->referral_code);

            $userReferralCodeExist = User::where([
                ['phone_number', $referralCode],
                ['account_rank', AccountRankDefineCode::LOYAL]
            ])
                ->first();

            if ($userReferralCodeExist == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }

            if ($userReferralCodeExist->is_admin == true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }

            if ($userReferralCodeExist->is_host == true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }

    /**
     * Register
     * 
     * @bodyParam name string required Tên
     * @bodyParam phone_number string required Số điện thoại
     * @bodyParam email string required Email
     * @bodyParam password string required Password
     * @bodyParam otp string gửi tin nhắn (DV SAHA gửi tới 8085)
     * @bodyParam otp_from string  phone(từ sdt)  email(từ email) mặc định là phone
     */
    public function register(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);
        $otp = $request->otp;
        $from = "";
        $type = "";

        if ($phone == null || empty($phone)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_IS_REQUIRED[0],
                'msg' => MsgCode::PHONE_NUMBER_IS_REQUIRED[1],
            ]);
        }

        if (!PhoneUtils::isNumberPhoneValid($phone)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        if (DB::table('users')->where('phone_number', $phone)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ]);
        }

        if ($request->referral_code != null) {
            $referralCode = PhoneUtils::convert($request->referral_code);

            $userReferralCodeExist = User::where([
                ['phone_number', $referralCode],
                ['account_rank', AccountRankDefineCode::LOYAL]
            ])->first();

            if ($userReferralCodeExist == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }

            if ($userReferralCodeExist->is_admin == true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }

            if ($userReferralCodeExist->is_host == true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                    'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                ]);
            }
        }

        // check otp
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

        $userCreate = User::create(
            [
                'name' =>  $request->name,
                'area_code' => "+84",
                'phone_number' => $phone,
                'self_referral_code' => $phone,
                'phone_verified_at' => DatetimeUtils::getNow(),
                'email' => $request->email,
                'avatar_image' => "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg",
                'password' => bcrypt($request->password),
                'host_rank' => HostRankDefineCode::NORMAL,
                'account_rank' => AccountRankDefineCode::NORMAL,
                'referral_code' => $request->referral_code != null ? $request->referral_code : null
            ]
        );

        if (!Service::where('user_id', $userCreate->id)->where('service_unit', 'Kwh')->where('service_name', 'Điện')->where('type_unit', ServiceUnitDefineCode::SERVICE_INDEX)->exists()) {
            Service::create([
                'user_id' => $userCreate->id,
                "service_name"  => 'Điện',
                "service_icon"  => "assets/icon_images/dien.png",
                "service_unit"  => "Kwh",
                "service_charge" => 3000,
                "type_unit" => ServiceUnitDefineCode::SERVICE_INDEX,
                "is_default" => true
            ]);
        }

        if (!Service::where('user_id', $userCreate->id)->where('service_unit', 'm3')->where('service_name', 'Nước')->where('type_unit', ServiceUnitDefineCode::SERVICE_INDEX)->exists()) {
            Service::create([
                'user_id' => $userCreate->id,
                "service_name"  => 'Nước',
                "service_icon"  => "assets/icon_images/nuoc.png",
                "service_unit"  => "m3",
                "service_charge" => 20000,
                "type_unit" => ServiceUnitDefineCode::SERVICE_INDEX,
                "is_default" => true
            ]);
        }


        if (!Service::where('user_id', $userCreate->id)->where('service_unit', 'Phòng')->where('service_name', 'Mạng')->where('type_unit', ServiceUnitDefineCode::PER_MOTEL)->exists()) {
            Service::create([
                'user_id' => $userCreate->id,
                "service_name"  => 'Mạng',
                "service_icon"  => "assets/icon_images/icon-mang.png",
                "service_unit"  => "Phòng",
                "service_charge" => 100000,
                "type_unit" => ServiceUnitDefineCode::PER_MOTEL,
                "is_default" => true
            ]);
        }

        if (!Service::where('user_id', $userCreate->id)->where('service_unit', 'Phòng')->where('service_name', 'Dịch vụ chung')->where('type_unit', ServiceUnitDefineCode::PER_MOTEL)->exists()) {
            Service::create([
                'user_id' => $userCreate->id,
                "service_name"  => 'Dịch vụ chung',
                "service_icon"  => "assets/icon_images/ve-sinh.png",
                "service_unit"  => "Phòng",
                "service_charge" => 50000,
                "type_unit" => ServiceUnitDefineCode::PER_MOTEL,
                "is_default" => true
            ]);
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_CREATED,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $userCreate
        ]);
    }
}
