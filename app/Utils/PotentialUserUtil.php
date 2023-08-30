<?php

namespace App\Utils;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use App\Models\HistoryPotentialUser;
use App\Models\PotentialUser;
use App\Models\Product;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class PotentialUserUtil
{
    static function updatePotential($guestUserId, $hostUserId, $valueReference, $title, $typeFrom = StatusHistoryPotentialUserDefineCode::TYPE_FROM_LIKE)
    {
        $now = Helper::getTimeNowDateTime();
        try {
            $checkRenterExists = DB::table('renters')
                ->join('users', 'renters.phone_number', '=', 'users.phone_number')
                ->where([
                    ['users.id', $hostUserId],
                    ['renters.user_id', $guestUserId]
                ])->first();

            if ($guestUserId == $hostUserId) {
                return null;
            }

            if ($checkRenterExists == null) {
                $potentialExists = PotentialUser::where([
                    ['user_guest_id', $guestUserId],
                    ['user_host_id', $hostUserId],
                ])->first();
                if (!$potentialExists) {
                    $potentialExists = PotentialUser::create([
                        'user_host_id' => $hostUserId,
                        'user_guest_id' => $guestUserId,
                        'value_reference' => $valueReference,
                        'is_has_contract' => false,
                        'type_from' => $typeFrom,
                        'time_interact' => $now->format('Y-m-d H:i:s'),
                        'title' => $title
                    ]);

                    NotificationUserJob::dispatch(
                        $hostUserId,
                        'Bạn có khách hàng tiềm năng mới',
                        'Bạn có khách hàng tiềm năng mới',
                        TypeFCM::NEW_CUSTOMER_POTENTIAL,
                        NotiUserDefineCode::USER_IS_HOST,
                        $potentialExists->id
                    );
                } else {
                    $tempStatus = StatusHistoryPotentialUserDefineCode::PROGRESSING;
                    if ($potentialExists->status == StatusHistoryPotentialUserDefineCode::HIDDEN) {
                        $tempStatus = StatusHistoryPotentialUserDefineCode::PROGRESSING;
                    } else {
                        $tempStatus = $potentialExists->value_reference != $valueReference ? StatusHistoryPotentialUserDefineCode::PROGRESSING : $potentialExists->status;
                    }
                    $potentialExists->update([
                        'type_from' => $typeFrom,
                        'time_interact' => $now->format('Y-m-d H:i:s'),
                        'title' => $title,
                        'value_reference' => $valueReference,
                        'status' => $tempStatus
                    ]);
                }

                // add list history interact potential user
            }

            return $potentialExists ?? null;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}

// $videoFolder = 'videos/SHUVideos/';
// $pathFile = $videoFolder . $slug;

// $storagePath = storage_path($pathFile);

// if (!file_exists(public_path($pathFile))) {
//     return response()->json(['video_not_found'], 404);
// } else {

//     $accessExists = VideoAccess::where(
//         'path',
//         $pathFile
//     )->first();

//     if ($accessExists != null) {
//         $accessExists->update(
//             [
//                 "time_access" => now()
//             ]
//         );
//     } else {
//         VideoAccess::create([
//             "path" => $pathFile,
//             "time_access" => now()
//         ]);
//     }

//     $video = Video::where(
//         'path',
//         $pathFile
//     )->first();

//     if ($video != null) {
//         $video->update(
//             [
//                 "time_access" => now()
//             ]
//         );
//     }


//     //$img = file_get_contents(public_path($pathFile));
//     //return response($img)->header('Content-type', 'video/mp4');
    
//     $fileSize = filesize($pathFile);
//     $img = file_get_contents(public_path($pathFile));
//     return response($img)->header('Content-type', 'video/mp4')->header('Accept-Ranges', 'bytes');
