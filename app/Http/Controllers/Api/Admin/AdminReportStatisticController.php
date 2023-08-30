<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use App\Helper\Helper;
use Illuminate\Support\Facades\DB;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusCollaboratorReferMotelDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusFindFastMotelDefineCode;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Helper\StatusMoPostDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\StatusOrderServicesSellDefineCode;
use App\Helper\StatusReportProblemDefineCode;
use App\Helper\StatusReservationMotelDefineCode;
use App\Models\Bill;
use App\Models\CollaboratorReferMotel;
use App\Models\Contract;
use App\Models\findFastMotel;
use App\Models\LineItemServiceSell;
use App\Models\MoPost;
use App\Models\MoPostFavorite;
use App\Models\MoPostFindMotel;
use App\Models\MoPostRoommate;
use App\Models\Motel;
use App\Models\MotelFavorite;
use App\Models\OrderServiceSell;
use App\Models\PostFindMotelFavorite;
use App\Models\PostRoommateFavorite;
use App\Models\PotentialUser;
use App\Models\Renter;
use App\Models\ReservationMotel;
use App\Models\User;
use App\Models\ViewerPost;
use App\Models\ViewerPostFindMotel;
use App\Models\ViewerPostRoommate;
use Carbon\Carbon;
use Datetime;
use Exception;
use Illuminate\Http\Response;

/**
 * @group  Admin/Báo cáo 
 *
 * APIs Báo cáo 
 */
class AdminReportStatisticController extends Controller
{

    /**
     * 
     * Doanh thu theo kì
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getBillStatistics(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $nowTime = Helper::getTimeNowDateTime();

        $totalMotelReturn = 0;
        $totalNewMotel = 0;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !Helper::validateDate($dateFrom, 'Y-m-d') || !Helper::validateDate($dateTo, 'Y-m-d')
            ) {
                return ResponseUtils::json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }
        } else {
            $dateTo = $nowTime->format('Y-m-d');
            $dateFrom = $nowTime->format('Y') . '-01' . '-01';
        }

        $dateFrom = new DateTime($dateFrom . '-01');
        $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)));

        $billByMonth = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->where([
                ['bills.status', StatusBillDefineCode::COMPLETED]
            ])
            ->select('bills.*')
            ->select(
                DB::raw('sum(total_money_motel) as `total_money_motel`'),
                DB::raw('sum(total_money_service) as `total_money_service`'),
                DB::raw('sum(discount) as `discount`'),
                DB::raw('sum(total_final) as `total_final`'),
                DB::raw("DATE_FORMAT(date_payment, '%m-%Y') date_payment"),
                DB::raw('YEAR(date_payment) year, MONTH(date_payment) month')
            )
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                if ($dateFrom != null && $dateTo != null) {
                    $query->where('date_payment', '<=', $dateTo);
                    $query->where('date_payment', '>=', $dateFrom);
                } else if ($dateFrom != null && !empty($dateFrom)) {
                    $query->where('date_payment', '>=', $dateFrom);
                } else if ($dateTo != null && !empty($dateTo)) {
                    $query->where('date_payment', '<=', $dateTo);
                }
            })
            ->groupBy('year', 'month')
            ->get();

        $dataChart = [
            'totalMotelReturn' => 0,
            'totalNewMotel' => 0,
            'charts' => $billByMonth,
            'type_chart' => 'months',
            'total_discount' => 0,
            'total_money_service' => 0,
            'total_money_motel' => 0,
            'total_final' => 0
        ];

        $totalMotelReturn = Contract::where('status', StatusContractDefineCode::TERMINATION)
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('updated_at', '<=', $dateTo);
                $query->where('updated_at', '>=', $dateFrom);
            })
            ->count();

        $totalNewMotel = Motel::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('updated_at', '<=', $dateTo);
            $query->where('updated_at', '>=', $dateFrom);
        })
            ->where('motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT)
            ->count();

        $dataChart['totalMotelReturn'] = $totalMotelReturn;
        $dataChart['totalNewMotel'] = $totalNewMotel;

        foreach ($billByMonth as $bill) {
            $dataChart['total_discount'] += $bill->discount;
            $dataChart['total_money_service'] += $bill->total_money_service;
            $dataChart['total_money_motel'] += $bill->total_money_motel;
            $dataChart['total_final'] += $bill->total_final;
        }


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    public function badges(Request $request)
    {
        $totalUser = DB::table('users')->count();

        $totalMotel = DB::table('motels')->count();

        $totalContractActive = DB::table('contracts')->where('status', StatusContractDefineCode::COMPLETED)->count();

        $totalContractPending = DB::table('contracts')->where('status', StatusContractDefineCode::PROGRESSING)->count();

        $totalRenter = DB::table('renters')->distinct('renters.phone_number')->count();

        $totalRenterHasMotel = DB::table('renters')
            ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where('contracts.status', StatusContractDefineCode::COMPLETED)
            ->select('renters.*')
            ->distinct('renters.phone_number')
            ->count();

        $totalRenterUnconfirmedMotel = DB::table('renters')
            ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where('contracts.status', StatusContractDefineCode::PROGRESSING)
            ->select('renters.*')
            ->distinct('renters.phone_number')
            ->count();

        $totalRenterHasNotMotel = DB::table('renters')
            ->whereNotIn('phone_number', function ($q) {
                $q->select('renter_phone_number')->from('user_contracts');
            })
            ->count();

        $badges = [
            'total_user' => $totalUser,
            'total_motel' => $totalMotel,
            'total_contract_active' => $totalContractActive,
            'total_contract_pending' => $totalContractPending,
            'total_renter' => $totalRenter,
            'total_renter_has_motel' => $totalRenterHasMotel,
            'total_renter_has_not_motel' => $totalRenterHasNotMotel,
            'total_renter_unconfirmed_motel' => $totalRenterUnconfirmedMotel
        ];



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $badges
        ]);
    }

    /**
     * 
     * Thống kê thời gian giải quyết vấn đề
     * 
     * @queryParam type_period 
     * 
     */
    public function statisticProblem(Request $request)
    {
        $list =  DB::table('users')
            ->join('contracts', 'users.id', '=', 'contracts.user_id')
            ->join('report_problems', 'contracts.motel_id', '=', 'report_problems.motel_id')
            ->select('users.*', DB::raw('AVG(TIME_TO_SEC(TIMEDIFF(report_problems.time_done, report_problems.created_at))) AS timediff'))
            ->where([
                ['users.is_host', true],
                ['report_problems.status', StatusReportProblemDefineCode::COMPLETED]
            ])
            ->groupBy('users.id')->get();
    }

    /**
     * 
     * Thống kê đơn hàng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getOrders(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $nowTime = Helper::getTimeNowDateTime();
        $isCheck = false;
        $format_chart = 'Y';
        $list_type_chart = ['year', 'month', 'day'];

        if ($request->type_chart == null) {
            if (in_array($request->type_chart, $list_type_chart)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_TYPE_CHART[0],
                    'msg' => MsgCode::INVALID_TYPE_CHART[1],
                ]);
            }
        }

        // set format chart
        if ($request->type_chart == $list_type_chart[0]) {
            $format_chart = 'Y';
        } else if ($request->type_chart == $list_type_chart[1]) {
            $format_chart = 'm';
        } else if ($request->type_chart == $list_type_chart[2]) {
            $format_chart = 'd';
        }

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $orders = OrderServiceSell::where([
            ['order_status', StatusOrderServicesSellDefineCode::COMPLETED]
        ])
            ->select(
                DB::raw('sum(total_final) as `total_final`'),
                DB::raw('sum(total_before_discount) as `total_before_discount`'),
                DB::raw('sum(total_shipping_fee) as `total_shipping_fee`'),
                DB::raw("DATE_FORMAT(date_payment, '%Y-%m-%d %h:%i:%s') date_payment"),
                DB::raw('YEAR(date_payment) year, MONTH(date_payment) month, DAY(date_payment) as day, HOUR(date_payment) as hour')
            )
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('date_payment', '<=', $dateTo);
                $query->where('date_payment', '>=', $dateFrom);
            })
            ->when($request->type_chart != null, function ($query) use ($request) {
                $query->groupBy($request->type_chart);
            })
            ->get();

        $listOrder = [];

        for ($i = (int)$dateFrom->format($format_chart); $i <= (int)$dateTo->format($format_chart); $i++) {
            $isCheck = false;
            for ($y = 0; $y < count($orders); $y++) {
                if ($orders[$y][$request->type_chart] == $i) {
                    array_push($listOrder, [
                        "total_before_discount" => $orders[$y]['total_before_discount'],
                        "total_shipping_fee" => $orders[$y]['total_shipping_fee'],
                        "discount" => $orders[$y]['discount'],
                        "total_final" => $orders[$y]['total_final'],
                        // "date_payment" => $orders[$y]['date_payment'],
                        "year" => $orders[$y]['year'],
                        "month" => $orders[$y]['month'],
                        "day" => $orders[$y]['day'],
                    ]);
                    $isCheck = true;
                }
            }
            if (!$isCheck) {
                array_push($listOrder, [
                    "total_before_discount" => 0,
                    "total_shipping_fee" => 0,
                    "discount" => 0,
                    "total_final" => 0,
                    // "date_payment" => null,
                    "year" => (int)$dateTo->format('Y'),
                    "month" => $format_chart == 'm' ? $i : (int)$dateTo->format('m'),
                    "day" => $format_chart == 'd' ? $i : 1
                ]);
            }
        }

        $dataChart = [
            'charts' => $listOrder,
            'type_chart' => $request->type_chart,
            'total_discount' => 0,
            'total_shipping_fee' => 0,
            'total_before_discount' => 0,
            'total_final' => 0
        ];

        foreach ($orders as $order) {
            $dataChart['total_discount'] += $order->discount;
            $dataChart['total_before_discount'] += $order->total_before_discount;
            $dataChart['total_shipping_fee'] += $order->total_shipping_fee;
            $dataChart['total_final'] += $order->total_final;
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê dịch vụ đơn hàng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getOrdersService(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $orders = OrderServiceSell::where([
            ['order_status', StatusOrderServicesSellDefineCode::COMPLETED]
        ])
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('date_payment', '<=', $dateTo);
                $query->where('date_payment', '>=', $dateFrom);
            })
            ->get();

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {

            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_order_count' => 0,
                    'total_shipping_fee' => 0,
                    'total_before_discount' => 0,
                    'total_final' => 0,
                ];
            }
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_order_count' => 0,
                    'total_shipping_fee' => 0,
                    'total_before_discount' => 0,
                    'total_final' => 0,
                ];
            }
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_order_count' => 0,
                    'total_shipping_fee' => 0,
                    'total_before_discount' => 0,
                    'total_final' => 0,
                ];
            }
        }
        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($orders as $order) {
                $datePayment = new Datetime($order->date_payment);

                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $datePayment->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    $charts[$key]['total_order_count'] += 1;
                    $charts[$key]['total_shipping_fee'] = $charts[$key]['total_shipping_fee'] + $order->total_shipping_fee;
                    $charts[$key]['total_before_discount'] = $charts[$key]['total_before_discount'] + $order->total_before_discount;
                    $charts[$key]['total_final'] = $charts[$key]['total_final'] + $order->total_final;
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $datePayment->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d H:00:00');
                    $charts[$key]['total_order_count'] += 1;
                    $charts[$key]['total_shipping_fee'] = $charts[$key]['total_shipping_fee'] + $order->total_shipping_fee;
                    $charts[$key]['total_before_discount'] = $charts[$key]['total_before_discount'] + $order->total_before_discount;
                    $charts[$key]['total_final'] = $charts[$key]['total_final'] + $order->total_final;
                } else if (
                    $chartDatetime->format('Y-m-d') == $datePayment->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    $charts[$key]['total_order_count'] += 1;
                    $charts[$key]['total_shipping_fee'] = $charts[$key]['total_shipping_fee'] + $order->total_shipping_fee;
                    $charts[$key]['total_before_discount'] = $charts[$key]['total_before_discount'] + $order->total_before_discount;
                    $charts[$key]['total_final'] = $charts[$key]['total_final'] + $order->total_final;
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_order_count' => 0,
            'total_shipping_fee' => 0,
            'total_before_discount' => 0,
            'total_final' => 0
        ];

        foreach ($charts as $chart) {
            $dataChart['total_order_count'] += $chart['total_order_count'];
            $dataChart['total_shipping_fee'] += $chart['total_shipping_fee'];
            $dataChart['total_before_discount'] += $chart['total_before_discount'];
            $dataChart['total_final'] += $chart['total_final'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê chỉ số dịch vụ đơn hàng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getOrdersServiceBadges(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $quantityServiceSell = LineItemServiceSell::join('order_service_sells', 'line_item_service_sells.order_service_sell_id', '=', 'order_service_sells.id')
            ->select(
                'line_item_service_sells.service_sell_id',
                DB::raw('CAST(sum(line_item_service_sells.quantity) AS INT) as total_quantity_service_sold'),
            )
            ->where([
                ['line_item_service_sells.created_at', '<=', $dateTo],
                ['line_item_service_sells.created_at', '>=', $dateFrom],
                ['order_service_sells.order_status', StatusOrderServicesSellDefineCode::COMPLETED]
            ])
            ->groupBy('line_item_service_sells.service_sell_id')
            ->orderBy('total_quantity_service_sold', 'desc')
            ->get();

        $revenueServiceSell = LineItemServiceSell::join('order_service_sells', 'line_item_service_sells.order_service_sell_id', '=', 'order_service_sells.id')
            ->select(
                'line_item_service_sells.service_sell_id',
                DB::raw('sum(line_item_service_sells.total_price) as total_money_revenue')
            )
            ->where([
                ['line_item_service_sells.created_at', '<=', $dateTo],
                ['line_item_service_sells.created_at', '>=', $dateFrom],
                ['order_service_sells.order_status', StatusOrderServicesSellDefineCode::COMPLETED]
            ])
            ->groupBy('line_item_service_sells.service_sell_id')
            ->orderBy('total_money_revenue', 'desc')
            ->get();

        $dataRes = [
            'top_selling_services' => $quantityServiceSell,
            'top_revenue_services' => $revenueServiceSell,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê bài đăng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMoPosts(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        $moPosts = MoPost::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        });

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }

        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_mo_post' => 0,
                    'total_mo_post_approved' => 0,
                    'total_mo_post_pending' => 0,
                    'total_mo_post_cancel' => 0,
                    'total_mo_post_verified' => 0,
                    'total_mo_post_unverified' => 0,
                ];
            }

            $moPosts = $moPosts->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('HOUR(created_at) as hour'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day', 'hour', 'status');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_mo_post' => 0,
                    'total_mo_post_approved' => 0,
                    'total_mo_post_pending' => 0,
                    'total_mo_post_cancel' => 0,
                    'total_mo_post_verified' => 0,
                    'total_mo_post_unverified' => 0,
                ];
            }

            $moPosts = $moPosts->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day', 'status');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_mo_post' => 0,
                    'total_mo_post_approved' => 0,
                    'total_mo_post_pending' => 0,
                    'total_mo_post_cancel' => 0,
                    'total_mo_post_verified' => 0,
                    'total_mo_post_unverified' => 0,
                ];
            }
            $moPosts = $moPosts->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'status');
        }

        $moPosts = $moPosts->get();

        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($moPosts as $post) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month . '-' . $post->day . ' ' . $post->hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month . '-' . $post->day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month);
                }

                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['total_mo_post'] = ($charts[$key]["total_mo_post"]) + ($post->total);
                    $charts[$key]['total_mo_post_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_mo_post_approved"]) + ($post->total) : ($charts[$key]["total_mo_post_approved"]);
                    $charts[$key]['total_mo_post_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_mo_post_pending"]) + ($post->total) : ($charts[$key]["total_mo_post_pending"]);
                    $charts[$key]['total_mo_post_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_mo_post_cancel"]) + ($post->total) : ($charts[$key]["total_mo_post_cancel"]);
                    $charts[$key]['total_mo_post_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_mo_post_verified"]) + ($post->total) : ($charts[$key]["total_mo_post_verified"]);
                    $charts[$key]['total_mo_post_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_mo_post_unverified"]) + ($post->total) : ($charts[$key]["total_mo_post_unverified"]);
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['total_mo_post'] = ($charts[$key]["total_mo_post"]);
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['total_mo_post'] = ($charts[$key]["total_mo_post"]) + ($post->total);
                    $charts[$key]['total_mo_post_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_mo_post_approved"]) + ($post->total) : ($charts[$key]["total_mo_post_approved"]);
                    $charts[$key]['total_mo_post_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_mo_post_pending"]) + ($post->total) : ($charts[$key]["total_mo_post_pending"]);
                    $charts[$key]['total_mo_post_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_mo_post_cancel"]) + ($post->total) : ($charts[$key]["total_mo_post_cancel"]);
                    $charts[$key]['total_mo_post_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_mo_post_verified"]) + ($post->total) : ($charts[$key]["total_mo_post_verified"]);
                    $charts[$key]['total_mo_post_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_mo_post_unverified"]) + ($post->total) : ($charts[$key]["total_mo_post_unverified"]);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['total_mo_post'] = ($charts[$key]["total_mo_post"]) + ($post->total);
                    $charts[$key]['total_mo_post_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_mo_post_approved"]) + ($post->total) : ($charts[$key]["total_mo_post_approved"]);
                    $charts[$key]['total_mo_post_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_mo_post_pending"]) + ($post->total) : ($charts[$key]["total_mo_post_pending"]);
                    $charts[$key]['total_mo_post_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_mo_post_cancel"]) + ($post->total) : ($charts[$key]["total_mo_post_cancel"]);
                    $charts[$key]['total_mo_post_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_mo_post_verified"]) + ($post->total) : ($charts[$key]["total_mo_post_verified"]);
                    $charts[$key]['total_mo_post_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_mo_post_unverified"]) + ($post->total) : ($charts[$key]["total_mo_post_unverified"]);
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_mo_post' => 0,
            'total_mo_post_approved' => 0,
            'total_mo_post_pending' => 0,
            'total_mo_post_cancel' => 0,
            'total_mo_post_verified' => 0,
            'total_mo_post_unverified' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_mo_post'] += $chart['total_mo_post'];
            $dataChart['total_mo_post_approved'] += $chart['total_mo_post_approved'];
            $dataChart['total_mo_post_pending'] += $chart['total_mo_post_pending'];
            $dataChart['total_mo_post_cancel'] += $chart['total_mo_post_cancel'];
            $dataChart['total_mo_post_verified'] += $chart['total_mo_post_verified'];
            $dataChart['total_mo_post_unverified'] += $chart['total_mo_post_unverified'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê chỉ số bài đăng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMoPostBadges(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $topViewMoPosts = ViewerPost::select('mo_post_id', DB::raw('count(mo_post_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $topFavoriteMoPosts = MoPostFavorite::select('mo_post_id', DB::raw('count(mo_post_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $dataRes = [
            'top_view_mo_post' => $topViewMoPosts,
            'top_favorites_mo_post' => $topFavoriteMoPosts,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê tìm phòng nhanh
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMoPostFindMotels(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $moPostFindMotels = MoPostFindMotel::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        });

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_post_find_motel' => 0,
                    'total_post_find_motel_approved' => 0,
                    'total_post_find_motel_pending' => 0,
                    'total_post_find_motel_cancel' => 0,
                    'total_post_find_motel_verified' => 0,
                    'total_post_find_motel_unverified' => 0,
                ];
            }

            $moPostFindMotels = $moPostFindMotels->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('HOUR(created_at) as hour'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day', 'hour', 'status');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_post_find_motel' => 0,
                    'total_post_find_motel_approved' => 0,
                    'total_post_find_motel_pending' => 0,
                    'total_post_find_motel_cancel' => 0,
                    'total_post_find_motel_verified' => 0,
                    'total_post_find_motel_unverified' => 0,
                ];
            }

            $moPostFindMotels = $moPostFindMotels->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day', 'status');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_post_find_motel' => 0,
                    'total_post_find_motel_approved' => 0,
                    'total_post_find_motel_pending' => 0,
                    'total_post_find_motel_cancel' => 0,
                    'total_post_find_motel_verified' => 0,
                    'total_post_find_motel_unverified' => 0,
                ];
            }
            $moPostFindMotels = $moPostFindMotels->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'status');
        }

        $moPostFindMotels = $moPostFindMotels->get();

        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($moPostFindMotels as $post) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month . '-' . $post->day . ' ' . $post->hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month . '-' . $post->day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month);
                }
                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['total_post_find_motel'] = ($charts[$key]["total_post_find_motel"] ?? 0) + ($post->total);
                    $charts[$key]['total_post_find_motel_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_post_find_motel_approved"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_approved"] ?? 0);
                    $charts[$key]['total_post_find_motel_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_post_find_motel_pending"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_pending"] ?? 0);
                    $charts[$key]['total_post_find_motel_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_post_find_motel_cancel"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_cancel"] ?? 0);
                    $charts[$key]['total_post_find_motel_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_post_find_motel_verified"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_verified"] ?? 0);
                    $charts[$key]['total_post_find_motel_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_post_find_motel_unverified"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_unverified"] ?? 0);
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['total_post_find_motel'] = ($charts[$key]["total_post_find_motel"] ?? 0);
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['total_post_find_motel'] = ($charts[$key]["total_post_find_motel"] ?? 0) + ($post->total);
                    $charts[$key]['total_post_find_motel_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_post_find_motel_approved"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_approved"] ?? 0);
                    $charts[$key]['total_post_find_motel_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_post_find_motel_pending"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_pending"] ?? 0);
                    $charts[$key]['total_post_find_motel_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_post_find_motel_cancel"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_cancel"] ?? 0);
                    $charts[$key]['total_post_find_motel_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_post_find_motel_verified"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_verified"] ?? 0);
                    $charts[$key]['total_post_find_motel_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_post_find_motel_unverified"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_unverified"] ?? 0);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['total_post_find_motel'] = ($charts[$key]["total_post_find_motel"] ?? 0) + ($post->total);
                    $charts[$key]['total_post_find_motel_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_post_find_motel_approved"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_approved"] ?? 0);
                    $charts[$key]['total_post_find_motel_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_post_find_motel_pending"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_pending"] ?? 0);
                    $charts[$key]['total_post_find_motel_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_post_find_motel_cancel"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_cancel"] ?? 0);
                    $charts[$key]['total_post_find_motel_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_post_find_motel_verified"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_verified"] ?? 0);
                    $charts[$key]['total_post_find_motel_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_post_find_motel_unverified"] ?? 0) + ($post->total) : ($charts[$key]["total_post_find_motel_unverified"] ?? 0);
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }
        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_post_find_motel' => 0,
            'total_post_find_motel_approved' => 0,
            'total_post_find_motel_pending' => 0,
            'total_post_find_motel_cancel' => 0,
            'total_post_find_motel_verified' => 0,
            'total_post_find_motel_unverified' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_post_find_motel'] += $chart['total_post_find_motel'];
            $dataChart['total_post_find_motel_approved'] += $chart['total_post_find_motel_approved'];
            $dataChart['total_post_find_motel_pending'] += $chart['total_post_find_motel_pending'];
            $dataChart['total_post_find_motel_cancel'] += $chart['total_post_find_motel_cancel'];
            $dataChart['total_post_find_motel_verified'] += $chart['total_post_find_motel_verified'];
            $dataChart['total_post_find_motel_unverified'] += $chart['total_post_find_motel_unverified'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê chỉ số tìm phòng nhanh
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMoPostFindMotelBadges(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $topViewMoPostFindMotels = ViewerPostFindMotel::select('mo_post_find_motel_id', DB::raw('count(mo_post_find_motel_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_find_motel_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $topFavoriteMoPostFindMotels = PostFindMotelFavorite::select('mo_post_find_motel_id', DB::raw('count(mo_post_find_motel_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_find_motel_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $dataRes = [
            'top_view_post_find_motels' => $topViewMoPostFindMotels,
            'top_favorites_post_find_motels' => $topFavoriteMoPostFindMotels,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê tìm ở ghép
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMoPostFindRoommates(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $postRoommates = MoPostRoommate::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        })
            ->get();

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_post_roommate' => 0,
                    'total_post_roommate_approved' => 0,
                    'total_post_roommate_pending' => 0,
                    'total_post_roommate_cancel' => 0,
                    'total_post_roommate_verified' => 0,
                    'total_post_roommate_unverified' => 0,
                ];
            }
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_post_roommate' => 0,
                    'total_post_roommate_approved' => 0,
                    'total_post_roommate_pending' => 0,
                    'total_post_roommate_cancel' => 0,
                    'total_post_roommate_verified' => 0,
                    'total_post_roommate_unverified' => 0,
                ];
            }
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_post_roommate' => 0,
                    'total_post_roommate_approved' => 0,
                    'total_post_roommate_pending' => 0,
                    'total_post_roommate_cancel' => 0,
                    'total_post_roommate_verified' => 0,
                    'total_post_roommate_unverified' => 0,
                ];
            }
        }

        foreach ($postRoommates as $post) {
            foreach ($charts as $key => $chart) {
                $chartDatetime = new Datetime($chart['time']);
                $dateCreatedAt = new Datetime($post->created_at);
                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m'))
                ) {
                    // $charts[$key]['time'] = $charts[$key]['time'] . '-01';
                    $charts[$key]['total_post_roommate'] = ($charts[$key]["total_post_roommate"] ?? 0) + 1;
                    $charts[$key]['total_post_roommate_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_post_roommate_approved"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_approved"] ?? 0);
                    $charts[$key]['total_post_roommate_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_post_roommate_pending"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_pending"] ?? 0);
                    $charts[$key]['total_post_roommate_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_post_roommate_cancel"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_cancel"] ?? 0);
                    $charts[$key]['total_post_roommate_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_post_roommate_verified"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_verified"] ?? 0);
                    $charts[$key]['total_post_roommate_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_post_roommate_unverified"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_unverified"] ?? 0);
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m'))
                ) {
                    // $charts[$key]['time'] = $charts[$key]['time'] . '-01';
                    $charts[$key]['total_post_roommate'] = ($charts[$key]["total_post_roommate"] ?? 0);
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['total_post_roommate'] = ($charts[$key]["total_post_roommate"] ?? 0) + 1;
                    $charts[$key]['total_post_roommate_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_post_roommate_approved"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_approved"] ?? 0);
                    $charts[$key]['total_post_roommate_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_post_roommate_pending"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_pending"] ?? 0);
                    $charts[$key]['total_post_roommate_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_post_roommate_cancel"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_cancel"] ?? 0);
                    $charts[$key]['total_post_roommate_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_post_roommate_verified"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_verified"] ?? 0);
                    $charts[$key]['total_post_roommate_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_post_roommate_unverified"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_unverified"] ?? 0);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['total_post_roommate'] = ($charts[$key]["total_post_roommate"] ?? 0) + 1;
                    $charts[$key]['total_post_roommate_approved'] = $post->status == StatusMoPostDefineCode::COMPLETED ? ($charts[$key]["total_post_roommate_approved"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_approved"] ?? 0);
                    $charts[$key]['total_post_roommate_pending'] = $post->status == StatusMoPostDefineCode::PROCESSING ? ($charts[$key]["total_post_roommate_pending"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_pending"] ?? 0);
                    $charts[$key]['total_post_roommate_cancel'] = $post->status == StatusMoPostDefineCode::CANCEL ? ($charts[$key]["total_post_roommate_cancel"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_cancel"] ?? 0);
                    $charts[$key]['total_post_roommate_verified'] = $post->admin_verified == StatusMoPostDefineCode::VERIFIED_ADMIN ? ($charts[$key]["total_post_roommate_verified"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_verified"] ?? 0);
                    $charts[$key]['total_post_roommate_unverified'] = $post->admin_verified == StatusMoPostDefineCode::UNVERIFIED_ADMIN ? ($charts[$key]["total_post_roommate_unverified"] ?? 0) + 1 : ($charts[$key]["total_post_roommate_unverified"] ?? 0);
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }
        // dd($charts, $postRoommates);
        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_post_roommate' => 0,
            'total_post_roommate_approved' => 0,
            'total_post_roommate_pending' => 0,
            'total_post_roommate_cancel' => 0,
            'total_post_roommate_verified' => 0,
            'total_post_roommate_unverified' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_post_roommate'] += $chart['total_post_roommate'];
            $dataChart['total_post_roommate_approved'] += $chart['total_post_roommate_approved'];
            $dataChart['total_post_roommate_pending'] += $chart['total_post_roommate_pending'];
            $dataChart['total_post_roommate_cancel'] += $chart['total_post_roommate_cancel'];
            $dataChart['total_post_roommate_verified'] += $chart['total_post_roommate_verified'];
            $dataChart['total_post_roommate_unverified'] += $chart['total_post_roommate_unverified'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê chỉ số tìm ở ghép
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMoPostFindRoommateBadges(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $topViewMoPostRoommates = ViewerPostRoommate::select('mo_post_roommate_id', DB::raw('count(mo_post_roommate_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_roommate_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $topFavoriteMoPostRoommates = PostRoommateFavorite::select('mo_post_roommate_id', DB::raw('count(mo_post_roommate_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_roommate_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $dataRes = [
            'top_view_post_roommate' => $topViewMoPostRoommates,
            'top_favorites_post_roommate' => $topFavoriteMoPostRoommates,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê chỉ số tìm phòng nhanh
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getFindFastMotelBadges(Request $request)
    {
        $totalFindFastMotel = 0;
        $totalFindFastMotelPending = 0;
        $totalFindFastMotelCompleted = 0;

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $queryFindFastMotel = DB::table('find_fast_motels')
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('created_at', '<=', $dateTo);
                $query->where('created_at', '>=', $dateFrom);
            });

        $totalFindFastMotel = $queryFindFastMotel->count();

        $totalFindFastMotelPending = $queryFindFastMotel->where('status', StatusFindFastMotelDefineCode::NOT_CONSULT)->count();

        $totalFindFastMotelCompleted = DB::table('find_fast_motels')
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('created_at', '<=', $dateTo);
                $query->where('created_at', '>=', $dateFrom);
            })->where('status', StatusFindFastMotelDefineCode::CONSULTED)->count();


        $dataRes = [
            'total_find_fast_motel' => $totalFindFastMotel,
            'total_find_fast_motel_not_consult' => $totalFindFastMotelPending,
            'total_find_fast_motel_consulted' => $totalFindFastMotelCompleted
        ];
        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê người dùng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getUsers(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $moPosts = User::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        })
            ->get();

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {

            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_user' => 0,
                    'total_user_is_host' => 0,
                    'total_user_is_host' => 0,
                ];
            }
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_user' => 0,
                ];
            }
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_user' => 0,
                ];
            }
        }
        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($moPosts as $post) {
                $dateCreatedAt = new Datetime($post->created_at);

                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    $charts[$key]['total_user'] = ($charts[$key]["total_user"] ?? 0) + 1;
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['total_user'] = ($charts[$key]["total_user"] ?? 0) + 1;
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['total_user'] = ($charts[$key]["total_user"] ?? 0) + 1;
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_user' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_user'] += $chart['total_user'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê người thuê
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getRenters(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $renters = Renter::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        });

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_renter' => 0,
                ];
            }

            $renters = $renters->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day', 'hour');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_renter' => 0,
                ];
            }

            $renters = $renters->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_renter' => 0,
                ];
            }

            $renters = $renters->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month');
        }

        $renters = $renters->get();

        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($renters as $renter) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($renter->year . '-' . $renter->month . '-' . $renter->day . ' ' . $renter->hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($renter->year . '-' . $renter->month . '-' . $renter->day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($renter->year . '-' . $renter->month);
                }

                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    $charts[$key]['total_renter'] = ($charts[$key]["total_renter"] ?? 0) + ($renter->total);
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['total_renter'] = ($charts[$key]["total_renter"] ?? 0) + ($renter->total);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['total_renter'] = ($charts[$key]["total_renter"] ?? 0) + ($renter->total);
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_renter' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_renter'] += $chart['total_renter'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê chỉ số chủ nhà
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getHostBadges(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $topHostRenter = Renter::select('user_id', DB::raw('count(user_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('user_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $topFavoriteMotel = MotelFavorite::select('motel_id', 'user_id', DB::raw('count(motel_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('motel_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $topFavoriteMoPost = MoPostFavorite::select('mo_post_id', 'user_id', DB::raw('count(mo_post_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        foreach ($topHostRenter as $perRenter) {
            $perRenter->user = User::where('id', $perRenter->user_id)->first();
        }

        $dataRes = [
            'top_quantity_renter_of_host' => $topHostRenter,
            'top_quantity_favorite_motel_of_host' => $topFavoriteMotel,
            'top_quantity_favorite_mo_post_of_host' => $topFavoriteMoPost,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê phòng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMotels(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $motels = Motel::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('used_at', '<=', $dateTo);
            $query->where('used_at', '>=', $dateFrom);
        })
            ->where('motels.status', '<>', StatusMotelDefineCode::MOTEL_DRAFT);


        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_motels' => 0,
                    'total_quantity_motel_empty' => 0,
                    'total_quantity_motel_hired' => 0,
                ];
            }

            $motels = $motels->select(
                DB::raw('YEAR(used_at) as used_year'),
                DB::raw('MONTH(used_at) as used_month'),
                DB::raw('DAY(used_at) as used_day'),
                DB::raw('HOUR(used_at) as used_hour'),
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                DB::raw('DAY(created_at) as created_day'),
                DB::raw('HOUR(created_at) as created_hour'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('used_year', 'used_month', 'used_day', 'used_hour', 'created_year', 'created_month', 'created_day', 'created_hour', 'status');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_motels' => 0,
                    'total_quantity_motel_empty' => 0,
                    'total_quantity_motel_hired' => 0,
                ];
            }

            $motels = $motels->select(
                DB::raw('YEAR(used_at) as used_year'),
                DB::raw('MONTH(used_at) as used_month'),
                DB::raw('DAY(used_at) as used_day'),
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                DB::raw('DAY(created_at) as created_day'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('used_year', 'used_month', 'used_day', 'created_year', 'created_month', 'created_day', 'status');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_motels' => 0,
                    'total_quantity_motel_empty' => 0,
                    'total_quantity_motel_hired' => 0,
                ];
            }

            $motels = $motels->select(
                DB::raw('YEAR(used_at) as used_year'),
                DB::raw('MONTH(used_at) as used_month'),
                DB::raw('DAY(used_at) as used_day'),
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('used_year', 'used_month', 'created_year', 'created_month', 'status');
        }

        $motels = $motels->get();
        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($motels as $motel) {
                if ($type == 'hour') {
                    $dateUsedAt = new Datetime($motel->used_year . '-' . $motel->used_month . '-' . $motel->used_day . ' ' . $motel->used_hour . ':00:00');
                    $dateCreatedAt = new Datetime($motel->created_year . '-' . $motel->created_month . '-' . $motel->created_day . ' ' . $motel->created_hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateUsedAt = new Datetime($motel->used_year . '-' . $motel->used_month . '-' . $motel->used_day);
                    $dateCreatedAt = new Datetime($motel->created_year . '-' . $motel->created_month . '-' . $motel->created_day);
                }
                if ($type == 'month') {
                    $dateUsedAt = new Datetime($motel->used_year . '-' . $motel->used_month);
                    $dateCreatedAt = new Datetime($motel->created_year . '-' . $motel->created_month);
                }

                if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') == $dateUsedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');

                    if ($dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m') || $dateUsedAt->format('Y-m') == $chartDatetime->format('Y-m')) {
                        $charts[$key]['total_motels'] = ($charts[$key]["total_motels"] ?? 0) + ($motel->total);
                    }
                    $charts[$key]['total_quantity_motel_empty'] = $motel->status == StatusMotelDefineCode::MOTEL_EMPTY ? ($charts[$key]["total_quantity_motel_empty"] ?? 0) + ($motel->total) : ($charts[$key]["total_quantity_motel_empty"] ?? 0);
                    $charts[$key]['total_quantity_motel_hired'] = $motel->status == StatusMotelDefineCode::MOTEL_HIRED ? ($charts[$key]["total_quantity_motel_hired"] ?? 0) + ($motel->total) : ($charts[$key]["total_quantity_motel_hired"] ?? 0);
                } else if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') != $dateUsedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    $chartDatetime->format('Y-m-d H:00:00') == $dateUsedAt->format('Y-m-d H:00:00')
                ) {
                    if ($dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00') || $dateUsedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00')) {
                        $charts[$key]['total_motels'] = ($charts[$key]["total_motels"] ?? 0) + ($motel->total);
                    }

                    $charts[$key]['total_quantity_motel_empty'] = $motel->status == StatusMotelDefineCode::MOTEL_EMPTY ? ($charts[$key]["total_quantity_motel_empty"] ?? 0) + ($motel->total) : ($charts[$key]["total_quantity_motel_empty"] ?? 0);
                    $charts[$key]['total_quantity_motel_hired'] = $motel->status == StatusMotelDefineCode::MOTEL_HIRED ? ($charts[$key]["total_quantity_motel_hired"] ?? 0) + ($motel->total) : ($charts[$key]["total_quantity_motel_hired"] ?? 0);
                } else if (
                    $key == $dateUsedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateUsedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_motels'] = ($charts[$key]["total_motels"] ?? 0) + ($motel->total);
                    }
                    $charts[$key]['total_quantity_motel_empty'] = $motel->status == StatusMotelDefineCode::MOTEL_EMPTY ? ($charts[$key]["total_quantity_motel_empty"] ?? 0) + ($motel->total) : ($charts[$key]["total_quantity_motel_empty"] ?? 0);
                    $charts[$key]['total_quantity_motel_hired'] = $motel->status == StatusMotelDefineCode::MOTEL_HIRED ? ($charts[$key]["total_quantity_motel_hired"] ?? 0) + ($motel->total) : ($charts[$key]["total_quantity_motel_hired"] ?? 0);
                } else if ($key == $dateCreatedAt->format('Y-m-d') && $type == 'day') {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateUsedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_motels'] = ($charts[$key]["total_motels"] ?? 0) + ($motel->total);
                    }
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }
        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_motels' => 0,
            'total_quantity_motel_empty' => 0,
            'total_quantity_motel_hired' => 0,
        ];
        foreach ($charts as $chart) {
            $dataChart['total_motels'] += $chart['total_motels'];
            $dataChart['total_quantity_motel_empty'] += $chart['total_quantity_motel_empty'];
            $dataChart['total_quantity_motel_hired'] += $chart['total_quantity_motel_hired'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê chỉ số phòng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMotelBadges(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $topFavoriteMotels = MotelFavorite::select('motel_id', DB::raw('count(motel_id) as quantity'))
            ->where('used_at', '<=', $dateTo)
            ->where('used_at', '>=', $dateFrom)
            ->groupBy('motel_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();

        $dataRes = [
            'top_favorites_motels' => $topFavoriteMotels,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê tìm phòng nhanh
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getFindFastMotels(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $moPosts = findFastMotel::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        })
            ->get();

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {

            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_find_fast_motel' => 0,
                    'total_find_fast_motel_consulted' => 0,
                    'total_find_fast_motel_not_consult' => 0,
                ];
            }
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_find_fast_motel' => 0,
                    'total_find_fast_motel_consulted' => 0,
                    'total_find_fast_motel_not_consult' => 0,
                ];
            }
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_find_fast_motel' => 0,
                    'total_find_fast_motel_consulted' => 0,
                    'total_find_fast_motel_not_consult' => 0,
                ];
            }
        }
        foreach ($moPosts as $post) {
            $dateCreatedAt = new Datetime($post->created_at);
            foreach ($charts as $key => $chart) {
                $chartDatetime = new Datetime($chart['time']);

                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    $charts[$key]['total_find_fast_motel'] = ($charts[$key]["total_find_fast_motel"] ?? 0) + 1;
                    $charts[$key]['total_find_fast_motel_consulted'] = $post->status == StatusFindFastMotelDefineCode::CONSULTED ? ($charts[$key]["total_find_fast_motel_consulted"] ?? 0) + 1 : ($charts[$key]["total_find_fast_motel_consulted"] ?? 0);
                    $charts[$key]['total_find_fast_motel_not_consult'] = $post->status == StatusFindFastMotelDefineCode::NOT_CONSULT ? ($charts[$key]["total_find_fast_motel_not_consult"] ?? 0) + 1 : ($charts[$key]["total_find_fast_motel_not_consult"] ?? 0);
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['total_find_fast_motel'] = ($charts[$key]["total_find_fast_motel"] ?? 0) + 1;
                    $charts[$key]['total_find_fast_motel_consulted'] = $post->status == StatusFindFastMotelDefineCode::CONSULTED ? ($charts[$key]["total_find_fast_motel_consulted"] ?? 0) + 1 : ($charts[$key]["total_find_fast_motel_consulted"] ?? 0);
                    $charts[$key]['total_find_fast_motel_not_consult'] = $post->status == StatusFindFastMotelDefineCode::NOT_CONSULT ? ($charts[$key]["total_find_fast_motel_not_consult"] ?? 0) + 1 : ($charts[$key]["total_find_fast_motel_not_consult"] ?? 0);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['total_find_fast_motel'] = ($charts[$key]["total_find_fast_motel"] ?? 0) + 1;
                    $charts[$key]['total_find_fast_motel_consulted'] = $post->status == StatusFindFastMotelDefineCode::CONSULTED ? ($charts[$key]["total_find_fast_motel_consulted"] ?? 0) + 1 : ($charts[$key]["total_find_fast_motel_consulted"] ?? 0);
                    $charts[$key]['total_find_fast_motel_not_consult'] = $post->status == StatusFindFastMotelDefineCode::NOT_CONSULT ? ($charts[$key]["total_find_fast_motel_not_consult"] ?? 0) + 1 : ($charts[$key]["total_find_fast_motel_not_consult"] ?? 0);
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_find_fast_motel' => 0,
            'total_find_fast_motel_consulted' => 0,
            'total_find_fast_motel_not_consult' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_find_fast_motel'] += $chart['total_find_fast_motel'];
            $dataChart['total_find_fast_motel_consulted'] += $chart['total_find_fast_motel_consulted'];
            $dataChart['total_find_fast_motel_not_consult'] += $chart['total_find_fast_motel_not_consult'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê giữ chỗ
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getReservationMotels(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $moPosts = ReservationMotel::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        });

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {

            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_reservation_motel' => 0,
                    'total_reservation_motel_consulted' => 0,
                    'total_reservation_motel_not_consult' => 0,
                ];
            }

            $moPosts = $moPosts->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('HOUR(created_at) as hour'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day', 'hour', 'status');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_reservation_motel' => 0,
                    'total_reservation_motel_consulted' => 0,
                    'total_reservation_motel_not_consult' => 0,
                ];
            }

            $moPosts = $moPosts->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'day', 'status');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_reservation_motel' => 0,
                    'total_reservation_motel_consulted' => 0,
                    'total_reservation_motel_not_consult' => 0,
                ];
            }

            $moPosts = $moPosts->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('year', 'month', 'status');
        }

        $moPosts = $moPosts->get();

        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($moPosts as $post) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month . '-' . $post->day . ' ' . $post->hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month . '-' . $post->day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($post->year . '-' . $post->month);
                }

                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    $charts[$key]['total_reservation_motel'] = ($charts[$key]["total_reservation_motel"] ?? 0) + ($post->total);
                    $charts[$key]['total_reservation_motel_consulted'] = $post->status == StatusReservationMotelDefineCode::CONSULTED ? ($charts[$key]["total_reservation_motel_consulted"] ?? 0) + ($post->total) : ($charts[$key]["total_reservation_motel_consulted"] ?? 0);
                    $charts[$key]['total_reservation_motel_not_consult'] = $post->status == StatusReservationMotelDefineCode::NOT_CONSULT ? ($charts[$key]["total_reservation_motel_not_consult"] ?? 0) + ($post->total) : ($charts[$key]["total_reservation_motel_not_consult"] ?? 0);
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['total_reservation_motel'] = ($charts[$key]["total_reservation_motel"] ?? 0) + ($post->total);
                    $charts[$key]['total_reservation_motel_consulted'] = $post->status == StatusReservationMotelDefineCode::CONSULTED ? ($charts[$key]["total_reservation_motel_consulted"] ?? 0) + ($post->total) : ($charts[$key]["total_reservation_motel_consulted"] ?? 0);
                    $charts[$key]['total_reservation_motel_not_consult'] = $post->status == StatusReservationMotelDefineCode::NOT_CONSULT ? ($charts[$key]["total_reservation_motel_not_consult"] ?? 0) + ($post->total) : ($charts[$key]["total_reservation_motel_not_consult"] ?? 0);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['total_reservation_motel'] = ($charts[$key]["total_reservation_motel"] ?? 0) + ($post->total);
                    $charts[$key]['total_reservation_motel_consulted'] = $post->status == StatusReservationMotelDefineCode::CONSULTED ? ($charts[$key]["total_reservation_motel_consulted"] ?? 0) + ($post->total) : ($charts[$key]["total_reservation_motel_consulted"] ?? 0);
                    $charts[$key]['total_reservation_motel_not_consult'] = $post->status == StatusReservationMotelDefineCode::NOT_CONSULT ? ($charts[$key]["total_reservation_motel_not_consult"] ?? 0) + ($post->total) : ($charts[$key]["total_reservation_motel_not_consult"] ?? 0);
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_reservation_motel' => 0,
            'total_reservation_motel_consulted' => 0,
            'total_reservation_motel_not_consult' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_reservation_motel'] += $chart['total_reservation_motel'];
            $dataChart['total_reservation_motel_consulted'] += $chart['total_reservation_motel_consulted'];
            $dataChart['total_reservation_motel_not_consult'] += $chart['total_reservation_motel_not_consult'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê chỉ số giữ chỗ
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getReservationMotelBadges(Request $request)
    {
        $topReservationMotel = 0;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $topReservationMotel = ReservationMotel::select('mo_post_id', DB::raw('count(mo_post_id) as quantity'))
            ->where('created_at', '<=', $dateTo)
            ->where('created_at', '>=', $dateFrom)
            ->groupBy('mo_post_id')
            ->orderBy('quantity', 'desc')
            ->take(10)
            ->get();


        $dataRes = [
            'top_reservation_motel' => $topReservationMotel,
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataRes
        ]);
    }

    /**
     * 
     * Thống kê chỉ số giải quyết vấn đề
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getMinutesResolvedProblemHost(Request $request)
    {
        $users =  DB::table('users')
            ->where('users.is_host', true)
            ->get();

        foreach ($users as $user) {
            $years = 0;
            $months = 0;
            $days = 0;
            $hours = 0;
            $minutes = 0;
            $seconds = 0;
            $timediff = DB::table('report_problems')
                ->join('motels', 'report_problems.motel_id', '=', 'motels.id')
                ->select(DB::raw('DATE(report_problems.created_at) AS start_date, AVG(TIME_TO_SEC(TIMEDIFF(report_problems.time_done, report_problems.created_at))) AS timediff, MINUTE(AVG(TIME_TO_SEC(TIMEDIFF(report_problems.time_done, report_problems.created_at)))) AS minute_avg'))
                ->where('report_problems.status', StatusReportProblemDefineCode::COMPLETED)
                ->where('report_problems.user_id', $user->id)
                ->groupBy('start_date')
                ->first();

            $dt = Carbon::now();
            if ($timediff != null) {
                $years = $dt->diffInYears($dt->copy()->addSeconds($timediff->timediff));
                $months = $dt->diffInMonths($dt->copy()->addSeconds($timediff->timediff));
                $days = $dt->diffInDays($dt->copy()->addSeconds($timediff->timediff));
                $hours = $dt->diffInHours($dt->copy()->addSeconds($timediff->timediff)->subDays($days));
                $minutes = $dt->diffInMinutes($dt->copy()->addSeconds($timediff->timediff)->subDays($days)->subHours($hours));
                $seconds = $dt->diffInSeconds($dt->copy()->addSeconds($timediff->timediff)->subDays($days)->subHours($hours)->subMinutes($minutes));
            }
            $user->avg_minutes_resolved_problem = $timediff ? $dt->diffInMinutes($dt->copy()->addSeconds($timediff->timediff)) : 0;
        }
        $users = $users->sortByDesc('avg_minutes_resolved_problem');

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $users
        ]);
    }

    /**
     * 
     * Thống kê admin
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getStatisticAdmin(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $nowTime = Helper::getTimeNowDateTime();
        $isCheck = false;
        $format_chart = 'Y';
        $list_type_chart = ['year', 'month', 'day'];

        if ($request->type_chart == null) {
            if (in_array($request->type_chart, $list_type_chart)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_TYPE_CHART[0],
                    'msg' => MsgCode::INVALID_TYPE_CHART[1],
                ]);
            }
        }

        // set format chart
        if ($request->type_chart == $list_type_chart[0]) {
            $format_chart = 'Y';
        } else if ($request->type_chart == $list_type_chart[1]) {
            $format_chart = 'm';
        } else if ($request->type_chart == $list_type_chart[2]) {
            $format_chart = 'd';
        }

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }

        $orders = [];

        $listOrder = [];

        for ($i = (int)$dateFrom->format($format_chart); $i <= (int)$dateTo->format($format_chart); $i++) {
            $isCheck = false;
            for ($y = 0; $y < count($orders); $y++) {
                if ($orders[$y][$request->type_chart] == $i) {
                    array_push($listOrder, [
                        "total_before_discount" => $orders[$y]['total_before_discount'],
                        "total_shipping_fee" => $orders[$y]['total_shipping_fee'],
                        "discount" => $orders[$y]['discount'],
                        "total_final" => $orders[$y]['total_final'],
                        // "date_payment" => $orders[$y]['date_payment'],
                        "year" => $orders[$y]['year'],
                        "month" => $orders[$y]['month'],
                        "day" => $orders[$y]['day'],
                    ]);
                    $isCheck = true;
                }
            }
            if (!$isCheck) {
                array_push($listOrder, [
                    "total_before_discount" => 0,
                    "total_shipping_fee" => 0,
                    "discount" => 0,
                    "total_final" => 0,
                    // "date_payment" => null,
                    "year" => (int)$dateTo->format('Y'),
                    "month" => $format_chart == 'm' ? $i : (int)$dateTo->format('m'),
                    "day" => $format_chart == 'd' ? $i : 1
                ]);
            }
        }

        $dataChart = [
            'charts' => $listOrder,
            'type_chart' => $request->type_chart,
            'total_discount' => 0,
            'total_shipping_fee' => 0,
            'total_before_discount' => 0,
            'total_final' => 0
        ];

        foreach ($orders as $order) {
            $dataChart['total_discount'] += $order->discount;
            $dataChart['total_before_discount'] += $order->total_before_discount;
            $dataChart['total_shipping_fee'] += $order->total_shipping_fee;
            $dataChart['total_final'] += $order->total_final;
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê hoa hồng admin
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getCommissionAdmin(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $collaboratorReferMotel = CollaboratorReferMotel::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('date_refer_success', '<=', $dateTo);
            $query->where('date_refer_success', '>=', $dateFrom);
        })
            ->where([
                // ['status', StatusCollaboratorReferMotelDefineCode::COMPLETED],
                // ['status_commission_collaborator', StatusCollaboratorReferMotelDefineCode::COMPLETED]
            ])
            ->get();

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }

        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }

        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_money_commission_admin_received' => 0,
                    'total_money_commission_admin_paid_collaborator' => 0,
                    'total_money_commission_admin_revenue' => 0,
                ];
            }
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_money_commission_admin_received' => 0,
                    'total_money_commission_admin_paid_collaborator' => 0,
                    'total_money_commission_admin_revenue' => 0,
                ];
            }
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_money_commission_admin_received' => 0,
                    'total_money_commission_admin_paid_collaborator' => 0,
                    'total_money_commission_admin_revenue' => 0,
                ];
            }
        }

        foreach ($collaboratorReferMotel as $itemCollaboratorReferMotel) {
            $dateReferSuccess = new Datetime($itemCollaboratorReferMotel->date_refer_success);
            foreach ($charts as $key => $chart) {
                $chartDatetime = new Datetime($chart['time']);

                if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') == $dateReferSuccess->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    if ($itemCollaboratorReferMotel->status == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
                        $charts[$key]['total_money_commission_admin_received'] = $charts[$key]["total_money_commission_admin_received"] + $itemCollaboratorReferMotel->money_commission_admin;
                    }
                    if ($itemCollaboratorReferMotel->first_receive_commission == true) {
                        if ($itemCollaboratorReferMotel->status_commission_collaborator == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
                            $charts[$key]['total_money_commission_admin_paid_collaborator'] = $charts[$key]["total_money_commission_admin_paid_collaborator"] +  $itemCollaboratorReferMotel->money_commission_user * 2;
                        }
                    }
                    $charts[$key]['total_money_commission_admin_revenue'] = $charts[$key]["total_money_commission_admin_received"] - $charts[$key]["total_money_commission_admin_paid_collaborator"];
                } else if (
                    $type == 'month' &&
                    ($chartDatetime->format('Y-m') != $dateReferSuccess->format('Y-m'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $dateReferSuccess->format('Y-m-d H:00:00'))
                ) {
                    if ($itemCollaboratorReferMotel->status == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
                        $charts[$key]['total_money_commission_admin_received'] = $charts[$key]["total_money_commission_admin_received"] + $itemCollaboratorReferMotel->money_commission_admin;
                    }
                    if ($itemCollaboratorReferMotel->first_receive_commission == true) {
                        if ($itemCollaboratorReferMotel->status_commission_collaborator == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
                            $charts[$key]['total_money_commission_admin_paid_collaborator'] = $charts[$key]["total_money_commission_admin_paid_collaborator"] +  $itemCollaboratorReferMotel->money_commission_user * 2;
                        }
                    }
                    $charts[$key]['total_money_commission_admin_revenue'] = $charts[$key]["total_money_commission_admin_received"] - $charts[$key]["total_money_commission_admin_paid_collaborator"];
                } else if (
                    $key == $dateReferSuccess->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    if ($itemCollaboratorReferMotel->status == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
                        $charts[$key]['total_money_commission_admin_received'] = $charts[$key]["total_money_commission_admin_received"] + $itemCollaboratorReferMotel->money_commission_admin;
                    }
                    if ($itemCollaboratorReferMotel->first_receive_commission == true) {
                        if ($itemCollaboratorReferMotel->status_commission_collaborator == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
                            $charts[$key]['total_money_commission_admin_paid_collaborator'] = $charts[$key]["total_money_commission_admin_paid_collaborator"] +  $itemCollaboratorReferMotel->money_commission_user * 2;
                        }
                    }
                    $charts[$key]['total_money_commission_admin_revenue'] = $charts[$key]["total_money_commission_admin_received"] - $charts[$key]["total_money_commission_admin_paid_collaborator"];
                }
            }
        }

        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_money_commission_admin_received' => 0,
            'total_money_commission_admin_paid_collaborator' => 0,
            'total_money_commission_admin_revenue' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_money_commission_admin_received'] += $chart['total_money_commission_admin_received'];
            $dataChart['total_money_commission_admin_paid_collaborator'] += $chart['total_money_commission_admin_paid_collaborator'];
            $dataChart['total_money_commission_admin_revenue'] += $chart['total_money_commission_admin_revenue'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê khách hàng tiềm năng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getPotential(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $potentials = PotentialUser::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        })
            ->whereNotIn('status', [StatusHistoryPotentialUserDefineCode::HIDDEN, StatusHistoryPotentialUserDefineCode::CONSULTING]);

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_potentials' => 0,
                    'total_potential_not_consultant' => 0,
                    'total_potential_consulting' => 0,
                    'total_potential_rejected' => 0,
                ];
            }

            $potentials = $potentials->select(
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                DB::raw('DAY(created_at) as created_day'),
                DB::raw('HOUR(created_at) as created_hour'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day', 'created_hour', 'status');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_potentials' => 0,
                    'total_potential_not_consultant' => 0,
                    'total_potential_consulting'     => 0,
                    'total_potential_rejected' => 0,
                ];
            }

            $potentials = $potentials->select(
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                DB::raw('DAY(created_at) as created_day'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day', 'status');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_potentials' => 0,
                    'total_potential_not_consultant' => 0,
                    'total_potential_consulting' => 0,
                    'total_potential_rejected' => 0,
                ];
            }

            $potentials = $potentials->select(
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'status');
        }
        $potentials = $potentials->get();
        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($potentials as $potential) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($potential->created_year . '-' . $potential->created_month . '-' . $potential->created_day . ' ' . $potential->created_hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($potential->created_year . '-' . $potential->created_month . '-' . $potential->created_day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($potential->created_year . '-' . $potential->created_month);
                }
                if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');

                    if ($dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m') || $dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m')) {
                        $charts[$key]['total_potentials'] = ($charts[$key]["total_potentials"] ?? 0) + ($potential->total);
                    }
                    $charts[$key]['total_potential_not_consultant'] = $potential->status == StatusHistoryPotentialUserDefineCode::PROGRESSING ? ($charts[$key]["total_potential_not_consultant"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_not_consultant"] ?? 0);
                    $charts[$key]['total_potential_consulting'] = $potential->status == StatusHistoryPotentialUserDefineCode::COMPLETED ? ($charts[$key]["total_potential_consulting"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_consulting"] ?? 0);
                    $charts[$key]['total_potential_rejected'] = $potential->status == StatusHistoryPotentialUserDefineCode::CANCELED ? ($charts[$key]["total_potential_rejected"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_rejected"] ?? 0);
                } else if (
                    $type == 'hour' &&
                    $chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00')
                ) {
                    if ($dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00') || $dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00')) {
                        $charts[$key]['total_potentials'] = ($charts[$key]["total_potentials"] ?? 0) + ($potential->total);
                    }

                    $charts[$key]['total_potential_not_consultant'] = $potential->status == StatusHistoryPotentialUserDefineCode::PROGRESSING ? ($charts[$key]["total_potential_not_consultant"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_not_consultant"] ?? 0);
                    $charts[$key]['total_potential_consulting'] = $potential->status == StatusHistoryPotentialUserDefineCode::COMPLETED ? ($charts[$key]["total_potential_consulting"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_consulting"] ?? 0);
                    $charts[$key]['total_potential_rejected'] = $potential->status == StatusHistoryPotentialUserDefineCode::CANCELED ? ($charts[$key]["total_potential_rejected"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_rejected"] ?? 0);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_potentials'] = ($charts[$key]["total_potentials"] ?? 0) + ($potential->total);
                    }
                    $charts[$key]['total_potential_not_consultant'] = $potential->status == StatusHistoryPotentialUserDefineCode::PROGRESSING ? ($charts[$key]["total_potential_not_consultant"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_not_consultant"] ?? 0);
                    $charts[$key]['total_potential_consulting'] = $potential->status == StatusHistoryPotentialUserDefineCode::COMPLETED ? ($charts[$key]["total_potential_consulting"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_consulting"] ?? 0);
                    $charts[$key]['total_potential_rejected'] = $potential->status == StatusHistoryPotentialUserDefineCode::CANCELED ? ($charts[$key]["total_potential_rejected"] ?? 0) + ($potential->total) : ($charts[$key]["total_potential_rejected"] ?? 0);
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_potentials' => 0,
            'total_potential_not_consultant' => 0,
            'total_potential_consulting' => 0,
            'total_potential_rejected' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_potentials'] += $chart['total_potentials'];
            $dataChart['total_potential_not_consultant'] += $chart['total_potential_not_consultant'];
            $dataChart['total_potential_consulting'] += $chart['total_potential_consulting'];
            $dataChart['total_potential_rejected'] += $chart['total_potential_rejected'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Thống kê hợp đồng
     * 
     * @queryParam date_from bắt đầu từ 
     * @queryParam date_to bắt đầu từ 
     * 
     */
    public function getContract(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $contracts = Contract::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        });

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_contract' => 0,
                    'total_contract_pending' => 0,
                    'total_contract_active' => 0,
                    'total_contract_termination' => 0,
                ];
            }

            $contracts = $contracts->select(
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                DB::raw('DAY(created_at) as created_day'),
                DB::raw('HOUR(created_at) as created_hour'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day', 'created_hour', 'status');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_contract' => 0,
                    'total_contract_pending' => 0,
                    'total_contract_active' => 0,
                    'total_contract_termination' => 0,
                ];
            }
            $contracts = $contracts->select(
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                DB::raw('DAY(created_at) as created_day'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day', 'status');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_contract' => 0,
                    'total_contract_pending' => 0,
                    'total_contract_active' => 0,
                    'total_contract_termination' => 0,
                ];
            }
            $contracts = $contracts->select(
                DB::raw('YEAR(created_at) as created_year'),
                DB::raw('MONTH(created_at) as created_month'),
                'status',
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'status');
        }

        $contracts = $contracts->get();
        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($contracts as $contract) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($contract->created_year . '-' . $contract->created_month . '-' . $contract->created_day . ' ' . $contract->created_hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($contract->created_year . '-' . $contract->created_month . '-' . $contract->created_day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($contract->created_year . '-' . $contract->created_month);
                }

                if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');

                    if ($dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m') || $dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m')) {
                        $charts[$key]['total_contract'] = ($charts[$key]["total_contract"] ?? 0) + ($contract->total);
                    }
                    $charts[$key]['total_contract_pending'] = $contract->status == StatusContractDefineCode::PROGRESSING || $contract->status == StatusContractDefineCode::WAITING_CONFIRM ? ($charts[$key]["total_contract_pending"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_pending"] ?? 0);
                    $charts[$key]['total_contract_active'] = $contract->status == StatusContractDefineCode::COMPLETED ? ($charts[$key]["total_contract_active"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_active"] ?? 0);
                    $charts[$key]['total_contract_termination'] = $contract->status == StatusContractDefineCode::TERMINATION ? ($charts[$key]["total_contract_termination"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_termination"] ?? 0);
                } else if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    $chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00')
                ) {
                    if ($dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00') || $dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00')) {
                        $charts[$key]['total_contract'] = ($charts[$key]["total_contract"] ?? 0) + ($contract->total);
                    }

                    $charts[$key]['total_contract_pending'] = $contract->status == StatusContractDefineCode::PROGRESSING || $contract->status == StatusContractDefineCode::WAITING_CONFIRM ? ($charts[$key]["total_contract_pending"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_pending"] ?? 0);
                    $charts[$key]['total_contract_active'] = $contract->status == StatusContractDefineCode::COMPLETED ? ($charts[$key]["total_contract_active"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_active"] ?? 0);
                    $charts[$key]['total_contract_termination'] = $contract->status == StatusContractDefineCode::TERMINATION ? ($charts[$key]["total_contract_termination"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_termination"] ?? 0);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_contract'] = ($charts[$key]["total_contract"] ?? 0) + ($contract->total);
                    }
                    $charts[$key]['total_contract_pending'] = $contract->status == StatusContractDefineCode::PROGRESSING || $contract->status == StatusContractDefineCode::WAITING_CONFIRM ? ($charts[$key]["total_contract_pending"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_pending"] ?? 0);
                    $charts[$key]['total_contract_active'] = $contract->status == StatusContractDefineCode::COMPLETED ? ($charts[$key]["total_contract_active"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_active"] ?? 0);
                    $charts[$key]['total_contract_termination'] = $contract->status == StatusContractDefineCode::TERMINATION ? ($charts[$key]["total_contract_termination"] ?? 0) + ($contract->total) : ($charts[$key]["total_contract_termination"] ?? 0);
                } else if ($key == $dateCreatedAt->format('Y-m-d') && $type == 'day') {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_contract'] = ($charts[$key]["total_contract"] ?? 0) + ($contract->total);
                    }
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_contract' => 0,
            'total_contract_pending' => 0,
            'total_contract_active' => 0,
            'total_contract_termination' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_contract'] += $chart['total_contract'];
            $dataChart['total_contract_pending'] += $chart['total_contract_pending'];
            $dataChart['total_contract_active'] += $chart['total_contract_active'];
            $dataChart['total_contract_termination'] += $chart['total_contract_termination'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Doanh thu theo kì
     * 
     * @queryParam type_period 
     * 
     */
    public function getBills(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $bills = Bill::when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
            $query->where('created_at', '<=', $dateTo);
            $query->where('created_at', '>=', $dateFrom);
        })
            ->get();

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_bill' => 0,
                    'total_bill_pending' => 0,
                    'total_bill_pending_confirm' => 0,
                    'total_bill_completed' => 0,
                ];
            }
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_bill' => 0,
                    'total_bill_pending' => 0,
                    'total_bill_pending_confirm' => 0,
                    'total_bill_completed' => 0,
                ];
            }
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_bill' => 0,
                    'total_bill_pending' => 0,
                    'total_bill_pending_confirm' => 0,
                    'total_bill_completed' => 0,
                ];
            }
        }

        foreach ($bills as $bill) {
            $dateCreatedAt = new Datetime($bill->created_at);
            foreach ($charts as $key => $chart) {
                $chartDatetime = new Datetime($chart['time']);
                if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');

                    if ($dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m') || $dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m')) {
                        $charts[$key]['total_bill'] = ($charts[$key]["total_bill"] ?? 0) + 1;
                    }
                    $charts[$key]['total_bill_pending'] = $bill->status == StatusBillDefineCode::PROGRESSING ? ($charts[$key]["total_bill_pending"] ?? 0) + 1 : ($charts[$key]["total_bill_pending"] ?? 0);
                    $charts[$key]['total_bill_pending_confirm'] = $bill->status == StatusBillDefineCode::WAIT_FOR_CONFIRM ? ($charts[$key]["total_bill_pending_confirm"] ?? 0) + 1 : ($charts[$key]["total_bill_pending_confirm"] ?? 0);
                    $charts[$key]['total_bill_completed'] = $bill->status == StatusBillDefineCode::COMPLETED ? ($charts[$key]["total_bill_completed"] ?? 0) + 1 : ($charts[$key]["total_bill_completed"] ?? 0);
                } else if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    $chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00')
                ) {
                    if ($dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00') || $dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00')) {
                        $charts[$key]['total_bill'] = ($charts[$key]["total_bill"] ?? 0) + 1;
                    }

                    $charts[$key]['total_bill_pending'] = $bill->status == StatusBillDefineCode::PROGRESSING ? ($charts[$key]["total_bill_pending"] ?? 0) + 1 : ($charts[$key]["total_bill_pending"] ?? 0);
                    $charts[$key]['total_bill_pending_confirm'] = $bill->status == StatusBillDefineCode::WAIT_FOR_CONFIRM ? ($charts[$key]["total_bill_pending_confirm"] ?? 0) + 1 : ($charts[$key]["total_bill_pending_confirm"] ?? 0);
                    $charts[$key]['total_bill_completed'] = $bill->status == StatusBillDefineCode::COMPLETED ? ($charts[$key]["total_bill_completed"] ?? 0) + 1 : ($charts[$key]["total_bill_completed"] ?? 0);
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_bill'] = ($charts[$key]["total_bill"] ?? 0) + 1;
                    }
                    $charts[$key]['total_bill_pending'] = $bill->status == StatusBillDefineCode::PROGRESSING ? ($charts[$key]["total_bill_pending"] ?? 0) + 1 : ($charts[$key]["total_bill_pending"] ?? 0);
                    $charts[$key]['total_bill_pending_confirm'] = $bill->status == StatusBillDefineCode::WAIT_FOR_CONFIRM ? ($charts[$key]["total_bill_pending_confirm"] ?? 0) + 1 : ($charts[$key]["total_bill_pending_confirm"] ?? 0);
                    $charts[$key]['total_bill_completed'] = $bill->status == StatusBillDefineCode::COMPLETED ? ($charts[$key]["total_bill_completed"] ?? 0) + 1 : ($charts[$key]["total_bill_completed"] ?? 0);
                } else if ($key == $dateCreatedAt->format('Y-m-d') && $type == 'day') {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_bill'] = ($charts[$key]["total_bill"] ?? 0) + 1;
                    }
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_bill' => 0,
            'total_bill_pending' => 0,
            'total_bill_pending_confirm' => 0,
            'total_bill_completed' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_bill'] += $chart['total_bill'];
            $dataChart['total_bill_pending'] += $chart['total_bill_pending'];
            $dataChart['total_bill_pending_confirm'] += $chart['total_bill_pending_confirm'];
            $dataChart['total_bill_completed'] += $chart['total_bill_completed'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Số ng thuê tiềm năng thành người thuê
     * 
     * @queryParam type_period 
     * 
     */
    public function getPotentialToRenters(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $renters = Renter::join('users', 'renters.phone_number', '=', 'users.phone_number')
            ->join('potential_users', 'users.id', '=', 'potential_users.user_guest_id')
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('renters.created_at', '<=', $dateTo);
                $query->where('renters.created_at', '>=', $dateFrom);
            })
            ->select('renters.*');

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_potential_to_renter' => 0,
                ];
            }

            $renters = $renters->select(
                DB::raw('YEAR(renters.created_at) as created_year'),
                DB::raw('MONTH(renters.created_at) as created_month'),
                DB::raw('DAY(renters.created_at) as created_day'),
                DB::raw('HOUR(renters.created_at) as created_hour'),
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day', 'created_hour');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_potential_to_renter' => 0,
                ];
            }

            $renters = $renters->select(
                DB::raw('YEAR(renters.created_at) as created_year'),
                DB::raw('MONTH(renters.created_at) as created_month'),
                DB::raw('DAY(renters.created_at) as created_day'),
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_potential_to_renter' => 0,
                ];
            }
            $renters = $renters->select(
                DB::raw('YEAR(renters.created_at) as created_year'),
                DB::raw('MONTH(renters.created_at) as created_month'),
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month');
        }
        $renters = $renters->get();
        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($renters as $renter) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($renter->created_year . '-' . $renter->created_month . '-' . $renter->created_day . ' ' . $renter->created_hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($renter->created_year . '-' . $renter->created_month . '-' . $renter->created_day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($renter->created_year . '-' . $renter->created_month);
                }
                if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');

                    if ($dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m') || $dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m')) {
                        $charts[$key]['total_potential_to_renter'] = ($charts[$key]["total_potential_to_renter"] ?? 0) + ($renter->total);
                    }
                } else if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    $chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00')
                ) {
                    if ($dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00') || $dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00')) {
                        $charts[$key]['total_potential_to_renter'] = ($charts[$key]["total_potential_to_renter"] ?? 0) + ($renter->total);
                    }
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_potential_to_renter'] = ($charts[$key]["total_potential_to_renter"] ?? 0) + ($renter->total);
                    }
                } else if ($key == $dateCreatedAt->format('Y-m-d') && $type == 'day') {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_potential_to_renter'] = ($charts[$key]["total_potential_to_renter"] ?? 0) + ($renter->total);
                    }
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_potential_to_renter' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_potential_to_renter'] += $chart['total_potential_to_renter'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }

    /**
     * 
     * Số ng thuê tiềm năng gán được phòng thành công
     * 
     * @queryParam type_period 
     * 
     */
    public function getPotentialHasMotel(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !(Helper::validateDate($dateFrom, 'Y-m') && Helper::validateDate($dateTo, 'Y-m')) &&
                !(Helper::validateDate($dateFrom, 'Y-m-d') && Helper::validateDate($dateTo, 'Y-m-d'))
            ) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            } else {
                if (Helper::validateDate($dateFrom, 'Y-m')) {
                    $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');
                } else {
                    $dateFrom = new DateTime($dateFrom . ' 00:00:01');
                    $dateTo = new DateTime($dateTo . ' 23:59:59');
                }
            }
        } else {
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d 00:00:01');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d 23:59:59');
        }


        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $renters = Renter::join('users', 'renters.phone_number', '=', 'users.phone_number')
            ->join('potential_users', 'users.id', '=', 'potential_users.user_guest_id')
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('renters.created_at', '<=', $dateTo);
                $query->where('renters.created_at', '>=', $dateFrom);
            })
            ->select('renters.*');

        //Đặt time charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
        if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
        if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }
        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }
        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'total_potential_has_contract' => 0,
                ];
            }
            $renters = $renters->select(
                DB::raw('YEAR(renters.created_at) as created_year'),
                DB::raw('MONTH(renters.created_at) as created_month'),
                DB::raw('DAY(renters.created_at) as created_day'),
                DB::raw('HOUR(renters.created_at) as created_hour'),
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day', 'created_hour');
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_potential_has_contract' => 0,
                ];
            }
            $renters = $renters->select(
                DB::raw('YEAR(renters.created_at) as created_year'),
                DB::raw('MONTH(renters.created_at) as created_month'),
                DB::raw('DAY(renters.created_at) as created_day'),
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month', 'created_day');
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_potential_has_contract' => 0,
                ];
            }
            $renters = $renters->select(
                DB::raw('YEAR(renters.created_at) as created_year'),
                DB::raw('MONTH(renters.created_at) as created_month'),
                DB::raw('count(*) as total')
            )
                ->groupBy('created_year', 'created_month');
        }
        $renters = $renters->get();
        foreach ($charts as $key => $chart) {
            $chartDatetime = new Datetime($chart['time']);
            foreach ($renters as $renter) {
                if ($type == 'hour') {
                    $dateCreatedAt = new Datetime($renter->created_year . '-' . $renter->created_month . '-' . $renter->created_day . ' ' . $renter->created_hour . ':00:00');
                }
                if ($type == 'day') {
                    $dateCreatedAt = new Datetime($renter->created_year . '-' . $renter->created_month . '-' . $renter->created_day);
                }
                if ($type == 'month') {
                    $dateCreatedAt = new Datetime($renter->created_year . '-' . $renter->created_month);
                }
                if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');

                    if ($dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m') || $dateCreatedAt->format('Y-m') == $chartDatetime->format('Y-m')) {
                        $charts[$key]['total_potential_has_contract'] = ($charts[$key]["total_potential_has_contract"] ?? 0) + ($renter->total);
                    }
                } else if (
                    $type == 'month' &&
                    $chartDatetime->format('Y-m') != $dateCreatedAt->format('Y-m')
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                } else if (
                    $type == 'hour' &&
                    $chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00')
                ) {
                    if ($dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00') || $dateCreatedAt->format('Y-m-d H:00:00') == $chartDatetime->format('Y-m-d H:00:00')) {
                        $charts[$key]['total_potential_has_contract'] = ($charts[$key]["total_potential_has_contract"] ?? 0) + ($renter->total);
                    }
                } else if (
                    $key == $dateCreatedAt->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_potential_has_contract'] = ($charts[$key]["total_potential_has_contract"] ?? 0) + ($renter->total);
                    }
                } else if ($key == $dateCreatedAt->format('Y-m-d') && $type == 'day') {
                    if ($dateCreatedAt->format('Y-m-d') == $key || $dateCreatedAt->format('Y-m-d') == $key) {
                        $charts[$key]['total_potential_has_contract'] = ($charts[$key]["total_potential_has_contract"] ?? 0) + ($renter->total);
                    }
                }
            }
        }
        $newArr = [];
        foreach ($charts as $chart) {
            array_push($newArr, $chart);
        }

        $dataChart = [
            'charts' => $newArr,
            'type_chart' => $type,
            'total_potential_has_contract' => 0,
        ];

        foreach ($charts as $chart) {
            $dataChart['total_potential_has_contract'] += $chart['total_potential_has_contract'];
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }
}
