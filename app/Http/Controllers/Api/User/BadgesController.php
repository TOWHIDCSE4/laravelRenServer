<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Motel;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusReportProblemDefineCode;
use App\Models\MsgCode;
use App\Models\PersonChats;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group  User/Chỉ số
 */
class BadgesController extends Controller
{

    static function getBadgesByMotel(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $user_id = $request->user->id;

        if ($dateFrom != null || $dateTo != null) {
            if (($dateFrom != null && $dateTo != null) && (Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))) {
                $dateFrom = $dateFrom . ' 00:00:01';
                $dateTo = $dateTo . ' 23:59:59';
            } else if ($dateFrom != null && Helper::validateDate($dateFrom, 'Y-m-d')) {
                $dateFrom = $dateFrom . ' 00:00:01';
            } else if ($dateTo != null && Helper::validateDate($dateTo, 'Y-m-d')) {
                $dateTo = $dateTo . ' 23:59:59';
            } else {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }
        }

        $total_renter_rented = DB::table('user_contracts')
            ->where('user_id', $request->user->id)
            // ->when($dateFrom != null, function ($query) use ($dateFrom) {
            //     $query->where('created_at', '>=', $dateFrom);
            // })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->distinct('renter_phone_number')
            ->count();
        $total_renter_rented = DB::table('renters')
            ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['contracts.user_id', $request->user->id]
            ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_host)
                    $query->where('renter_phone_number', '<>', $request->user->phone_number);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('renters.created_at', '<=', $dateTo);
            })
            ->groupBy('phone_number')
            ->distinct('renters.phone_number')
            ->get()
            ->count();

        $total_renter = DB::table('renters')
            ->where('user_id', $request->user->id)
            // ->when($dateFrom != null, function ($query) use ($dateFrom) {
            //     $query->where('created_at', '>=', $dateFrom);
            // })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->distinct('phone_number')
            ->count();

        $total_motel_favorite = DB::table('motel_favorites')
            ->where('user_id', $request->user->id)
            // ->when($dateFrom != null, function ($query) use ($dateFrom) {
            //     $query->where('created_at', '>=', $dateFrom);
            // })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->count();

        $total_motel = DB::table('motels')
            ->where([
                ['user_id', $request->user->id],
                ['motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT]
            ])
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->count();


        $total_motel_rented = DB::table('motels')
            ->join('contracts', 'motels.id', '=', 'contracts.motel_id')
            ->where([
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['motels.status', StatusMotelDefineCode::MOTEL_HIRED],
                ['contracts.user_id', $user_id],
                ['motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT]
            ])
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('motels.created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('motels.created_at', '<=', $dateTo);
            })
            ->select('motels.*')
            ->distinct('motels.id')
            ->count();

        $total_motel_available = Motel::where([
            ['motels.user_id', $user_id],
            ['motels.status', StatusMotelDefineCode::MOTEL_EMPTY],
        ])
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('motels.created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('motels.created_at', '<=', $dateTo);
            })
            ->count();
        // $a = DB::table('motels')
        //     ->join('contracts', 'motels.id', '=', 'contracts.motel_id')
        //     ->where([
        //         ['contracts.status', StatusContractDefineCode::COMPLETED],
        //         ['motels.status', StatusMotelDefineCode::MOTEL_HIRED],
        //         ['contracts.user_id', $user_id],
        //         ['motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT]
        //     ])->select('motels.*')->pluck('motels.id')->toArray();

        // $b = Motel::where([
        //     ['motels.user_id', $user_id],
        //     ['motels.status', StatusMotelDefineCode::MOTEL_EMPTY],
        // ])->pluck('motels.id')->toArray();

        // $c = array_merge($a, $b);
        // dd(Motel::where('motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT)->whereNotIn('id', $c)->get(), count($c), $total_motel);

        $total_problem_done = DB::table('report_problems')->where([
            ['user_id', $request->user->id],
            ['status', StatusReportProblemDefineCode::COMPLETED]
        ])
            // ->when($dateFrom != null, function ($query) use ($dateFrom) {
            //     $query->where('report_problems.created_at', '>=', $dateFrom);
            // })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('report_problems.created_at', '<=', $dateTo);
            })
            ->count();

        $total_problem_not_done = DB::table('report_problems')->where([
            ['user_id', $request->user->id],
            ['status', StatusReportProblemDefineCode::PROGRESSING]
        ])
            // ->when($dateFrom != null, function ($query) use ($dateFrom) {
            //     $query->where('report_problems.created_at', '>=', $dateFrom);
            // })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('report_problems.created_at', '<=', $dateTo);
            })
            ->count();

        $summary = [
            'total_motel' => $total_motel,
            'total_motel_rented' => $total_motel_rented,
            'total_motel_available' => $total_motel_available,
            'total_motel_favorite' => $total_motel_favorite,
            'total_renter' => $total_renter,
            'total_renter_rented' => $total_renter_rented,
            'total_problem_done' => $total_problem_done,
            'total_problem_not_done' => $total_problem_not_done,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $summary,
        ]);
    }

    /**
     * Lấy tất cả chỉ số đếm
     * 
     * Khách hàng chat cho user
     * Nhận badges realtime
     * var socket = io("http://localhost:6441")
     * socket.on("badges:badges_user:1", function(data) {
     *   console.log(data)
     *   })
     *  1 là user_id
     */

    /**
     * chỉ số về user
     * 
     */
    public function getBadges(Request $request)
    {
        $total_motel_rented = 0;
        $total_person_chat = 0;
        $total_cart = 0;
        $total_money_need_payment = 0;
        $currentUser = null;
        $summary = null;
        $list_motel_rented = [];

        if ($request->user != null) {

            $total_motel_rented = DB::table('motels')
                ->join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                ])
                ->distinct()
                ->count();

            $total_person_chat = PersonChats::where([
                ['user_id', $request->user->id],
            ])->count();

            $currentUser = User::where('id', $request->user->id)->first();

            $total_money_need_payment = DB::table('bills')
                ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                ->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['bills.status', StatusBillDefineCode::PROGRESSING],
                    ['bills.is_init', StatusBillDefineCode::BILL_BY_MONTH],
                    ['contracts.status', StatusContractDefineCode::COMPLETED]
                ])
                ->sum('bills.total_final');

            $list_motel_rented = DB::table('motels')
                ->join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['user_contracts.renter_phone_number', $request->user->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['motels.status', StatusMotelDefineCode::MOTEL_HIRED]
                ])
                ->distinct()
                ->select('motels.motel_name', 'motels.province_name', 'motels.district_name', 'motels.wards_name', 'motels.address_detail')
                ->get();

            $currentUser->list_motel_rented = $list_motel_rented;

            $total_cart = DB::table('item_cart_service_sells')
                ->where('user_id', $request->user->id)
                ->count();

            $summary = [
                'total_motel_rented' => $total_motel_rented,
                'total_person_chat' => $total_person_chat,
                'total_money_need_payment' => $total_money_need_payment,
                'total_cart' => (int)$total_cart,
                'current_user' => $currentUser
            ];
        } else {
            $summary = [
                'total_motel_rented' => $total_motel_rented,
                'total_person_chat' => $total_person_chat,
                'total_money_need_payment' => $total_money_need_payment,
                'total_cart' => $total_cart,
                'current_user' => null
            ];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $summary,
        ]);
    }
}
