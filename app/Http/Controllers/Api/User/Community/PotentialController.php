<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\ParamUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Http\Controllers\Controller;
use App\Models\HistoryPotentialUser;
use App\Models\MoPost;
use App\Models\MsgCode;
use App\Models\PotentialUser;
use App\Utils\PotentialUserUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PotentialController extends Controller
{

    public function create(Request $request)
    {
        $moPostExists = null;

        $moPostExists = MoPost::where('id', $request->mo_post_id)->first();

        if ($moPostExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ]);
        }

        if (StatusHistoryPotentialUserDefineCode::getStatusHistoryPotentialUserCode($request->type_from) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TYPE_FROM_POTENTIAL_CUSTOMER[0],
                'msg' => MsgCode::INVALID_TYPE_FROM_POTENTIAL_CUSTOMER[1],
            ]);
        }

        // handle user potential
        $potentialExists = PotentialUserUtil::updatePotential(
            $request->user->id,
            $moPostExists->user_id,
            $request->mo_post_id,
            $moPostExists->title,
            $request->type_from
        );

        HistoryPotentialUser::create([
            'user_guest_id' => $request->user->id,
            'user_host_id' => $moPostExists->user_id,
            'value_reference' => $request->mo_post_id,
            'type_from' => $request->type_from,
            'title' => $moPostExists->title
        ]);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $potentialExists
        ]);
    }
}
