<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\AccountRankDefineCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\Helper;
use App\Models\User;
use App\Helper\PhoneUtils;
use App\Models\MsgCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\StatusUserDefineCode;
use App\Helper\StatusWithdrawalDefineCode;
use App\Models\CollaboratorReferMotel;
use App\Models\PersonChats;
use App\Models\Renter;
use App\Models\Withdrawal;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group  profile user
 */
class UserController extends Controller
{
    /**
     * Cập nhật số điện thoại 1 user
     * 
     * @bodyParam phone_number string
     * 
     */
    public function updatePhoneNumber(Request $request)
    {
        $phone = PhoneUtils::convert($request->phone_number);

        $user = User::where('id', $request->user->id);

        if ($request->user->phone_number != null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::ACCOUNT_HAS_PHONE_NUMBER[0],
                'msg' => MsgCode::ACCOUNT_HAS_PHONE_NUMBER[1],
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

        if (User::where([['phone_number', $phone], ['id', '!=', $request->user->id]])->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
            ]);
        }

        $user->update([
            'phone_number' => $phone != null ? $phone : $request->user->phone_number,
            'self_referral_code' => $phone != null ? $phone : $request->user->phone_number,
        ]);

        $userData = $user->first();

        Renter::create(
            [
                'user_id' => $userData->id,
                'name' =>  $userData->name,
                'phone_number' => $phone ? $phone : $request->user->phone_number,
                'email' => $userData->email,
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
     * Cập nhật số điện thoại 1 user
     * 
     * @bodyParam phone_number string
     * 
     */
    public function updateReferralCode(Request $request)
    {
        $referralCode = PhoneUtils::convert($request->referral_code);

        $userReferralCodeExist = User::where([
            ['phone_number', $referralCode],
            ['account_rank', AccountRankDefineCode::LOYAL]
        ])->first();

        if ($userReferralCodeExist == null || $userReferralCodeExist->is_host == true || $userReferralCodeExist->is_admin == true) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
            ]);
        }

        // if (DB::table('collaborator_refer_motels')->where([['user_id', $request->user->id], ['user_referral_id', $userReferralCodeExist->id]])->exists()) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::REFERRAL_CODE_USED_IN_ACCOUNT[0],
        //         'msg' => MsgCode::REFERRAL_CODE_USED_IN_ACCOUNT[1],
        //     ]);
        // }

        // $collaboratorReferMotel = CollaboratorReferMotel::create([
        //     'user_id' => $request->user->id,
        //     'user_referral_id' => $userReferralCodeExist->id
        // ]);

        $userReferralCodeExist->update([
            'has_referral_code' => true
        ]);

        if ($request->referral_code != null) {
            if ($request->user->has_referral_code == true) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::REFERRAL_CODE_USED_IN_ACCOUNT[0],
                    'msg' => MsgCode::REFERRAL_CODE_USED_IN_ACCOUNT[1],
                ]);
            }

            $request->user->update([
                'referral_code' => $request->referral_code
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $userReferralCodeExist
        ]);
    }

    /**
     * Cập nhật chủ nhà
     * 
     * @bodyParam is_host boolean
     * 
     */
    public function updateHost(Request $request)
    {

        $user = User::where('id', $request->user->id)->first();

        $user->update([
            'is_host' => filter_var($request->is_host ?: false, FILTER_VALIDATE_BOOLEAN) ?? $user->is_host,
            'initial_account_type' => filter_var($request->is_host, FILTER_VALIDATE_BOOLEAN) ? StatusUserDefineCode::USER_IS_HOST : StatusUserDefineCode::USER_IS_NORMAL,
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $user
        ]);
    }

    /**
     * Cập nhật 1 user
     * 
     * @bodyParam name string
     * @bodyParam phone_number string
     * @bodyParam email string 
     * @bodyParam date_of_birth datetime 
     * @bodyParam avatar_image string 
     * @bodyParam sex boolean 
     * 
     */
    public function update(Request $request)
    {
        $email = $request->email;
        $name = $request->name;
        $timeNow = Helper::getTimeNowDateTime();

        if (
            Helper::validateDate($request->date_of_birth, 'Y-m-h')
            || date("Y", strtotime($request->date_of_birth)) >= $timeNow->format('Y')
        ) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATE_OF_BIRTH[0],
                'msg' => MsgCode::INVALID_DATE_OF_BIRTH[1],
            ]);
        }

        if (isset($email) && !Helper::validEmail($email)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_EMAIL[0],
                'msg' => MsgCode::INVALID_EMAIL[1],
            ]);
        }

        if (isset($email) && DB::table('users')->where([['email', $email], ['id', '!=', $request->user->id]])->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::EMAIL_ALREADY_EXISTS[0],
                'msg' => MsgCode::EMAIL_ALREADY_EXISTS[1],
            ]);
        }

        // if ($request->referral_code != null) {
        //     if (DB::table('collaborator_refer_motels')->where('user_id', $request->user->id)->exists()) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_BAD_REQUEST,
        //             'success' => false,
        //             'msg_code' => MsgCode::REFERRAL_CODE_USED_IN_ACCOUNT[0],
        //             'msg' => MsgCode::REFERRAL_CODE_USED_IN_ACCOUNT[1],
        //         ]);
        //     }

        //     $referralCode = PhoneUtils::convert($request->referral_code);

        //     $userReferralCodeExist = User::where('phone_number', $referralCode)->first();

        //     if ($userReferralCodeExist == null) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_BAD_REQUEST,
        //             'success' => false,
        //             'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
        //             'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
        //         ]);
        //     }

        //     CollaboratorReferMotel::create([
        //         'user_id' => $request->user->id,
        //         'user_referral_id' => $userReferralCodeExist->id
        //     ]);
        // }

        if ($request->referral_code != null) {
            if ($request->user->has_referral_code == false) {
                $userReferralCodeExist = User::where([
                    ['phone_number', $request->referral_code],
                    ['account_rank', AccountRankDefineCode::LOYAL]
                ])->first();

                if ($userReferralCodeExist == null || $userReferralCodeExist->is_host == true || $userReferralCodeExist->is_admin == true) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_REFERRAL_CODE[0],
                        'msg' => MsgCode::INVALID_REFERRAL_CODE[1],
                    ]);
                }

                User::where('id', $request->user->id)->update([
                    'referral_code' => $request->referral_code,
                    'has_referral_code' => true
                ]);
            }
        }

        User::where('id', $request->user->id)->update([
            'name' => ($name != null && trim($name) != '') ? $name : $request->user->name,
            'email' => $email,
            'sex' => $request->sex != null ? $request->sex : $request->user->sex,
            'avatar_image' => $request->avatar_image != null ? $request->avatar_image : $request->user->avatar_image,
            'date_of_birth' => $request->date_of_birth != null ? $request->date_of_birth : $request->user->date_of_birth,
            'cmnd_number' => $request->cmnd_number != null ? $request->cmnd_number : $request->user->cmnd_number,
            'cmnd_front_image_url' => $request->cmnd_front_image_url != null ? $request->cmnd_front_image_url : $request->user->cmnd_front_image_url,
            'cmnd_back_image_url' => $request->cmnd_back_image_url != null ? $request->cmnd_back_image_url : $request->user->cmnd_back_image_url,
            'bank_account_number' => $request->bank_account_number != null ? $request->bank_account_number : $request->user->bank_account_number,
            'bank_account_name' => $request->bank_account_name != null ? $request->bank_account_name : $request->user->bank_account_name,
            'bank_name' => $request->bank_name != null ? $request->bank_name : $request->user->bank_name,
        ]);



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => User::where('id', $request->user->id)->first(),
        ]);
    }

    /**
     * Lấy thông tin user
     */
    public function getProfile(Request $request)
    {

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => User::where('id', $request->user->id)->first(),
        ]);
    }
}
