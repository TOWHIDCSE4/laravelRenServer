<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Helper\ResponseUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Mail\SendMailOTP;
use App\Models\MsgCode;
use App\Models\OtpCodeEmail;
use Carbon\Carbon;
use DateTime;
use Mail;


class SendMailController extends Controller
{
	public function send_email_otp(Request $request)
	{

		$email = $request->email ?? "x@x";

		$otp = Helper::generateRandomNum(6);
		$now = Helper::getTimeNowString();

		$otpExis = OtpCodeEmail::where('email', $email)->first();
		if ($otpExis == null) {
			OtpCodeEmail::create([
				"otp" =>  $otp,
				"email" => $email,
				"time_generate" => $now,
			]);
		} else {
			$otpExis->update([
				"otp" =>  $otp,
				"time_generate" => $now,
			]);
		}

		$data = $request->all();
		$emails = [$email];
		//Gửi mail

		Mail::to($emails)
			->send(new \App\Mail\SendMailOTP($otp));

		return ResponseUtils::json([
			'code' => 200,
			'success' => true,
			'msg_code' => MsgCode::SUCCESS[0],
			'msg' => MsgCode::SUCCESS[1],
		]);
	}
}
