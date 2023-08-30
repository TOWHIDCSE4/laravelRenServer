<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MsgCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Models\Bill;
use App\Models\Motel;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group User/Quản lý/Báo cáo thống kê
 */
class ReportStatisticController extends Controller
{

    /**
     * 
     * 
     * @bodyParam date datetime tháng năm sẽ lấy báo cáo (default current month)
     * 
     */
    public function getReportGen(Request $request)
    {
        $dateRequest = $request->date;
        $date1 = null;
        $date2 = null;


        if ($dateRequest != null && trim($dateRequest)) {
            $dateRequest = Helper::getTimeNowDateTimeOptional(false, true, true);
        }

        if (Helper::validateDate($dateRequest)) {
            $date1 = new DateTime($dateRequest . '-01');
            $date2 = new DateTime($dateRequest . '-' . date("t", strtotime($dateRequest)));
        } else {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DAY_MONTH[0],
                'msg' => MsgCode::INVALID_DAY_MONTH[1],
            ]);
        }


        $totalIncomeBill = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->join('users', 'contracts.user_id', '=', 'users.id')
            ->where([
                ['users.id', $request->user->id],
                ['users.is_host', 1]
            ])
            ->when($date1 != null && $date2 != null, function ($query) use ($date1, $date2) {
                $query->where('date_payment', '<=', $date2);
                $query->where('date_payment', '>=', $date1);
            })
            ->select('contracts.*')
            ->sum('total_final');


        // $newRoomAdded = Motel::join('users', 'motels.user_id', '=', 'users.id')
        //     ->where([
        //         ['motels.user_id', $request->user->id],
        //         ['users.is_host', true],
        //         ['motels.status', 0]
        //     ])
        //     ->when($date1 != null && $date2 != null, function ($query) use ($date1, $date2) {
        //         $query->where('motels.created_at', '<=', $date2);
        //         $query->where('motels.created_at', '>=', $date1);
        //     })
        //     ->count('motels.id');


        $data = [
            'revenue' => $totalIncomeBill,
        ];


        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $data
        ]);
    }
    public function getRevenueBill(Request $request)
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
            $dateFrom = Helper::getTimeNowDateTime()->format('Y-m-d H:i:s');
            $dateTo = Helper::getTimeNowDateTime()->format('Y-m-d H:i:s');
        }

        $charts = [];
        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $orders = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.user_id', $request->user->id],
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['bills.status', StatusBillDefineCode::COMPLETED]
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
                    'total_discount' => 0,
                    'total_money_service' => 0,
                    'total_money_motel' => 0,
                    'total_money_deposit' => 0,
                    'total_final' => 0,
                ];
            }
        }

        if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_discount' => 0,
                    'total_money_service' => 0,
                    'total_money_motel' => 0,
                    'total_money_deposit' => 0,
                    'total_final' => 0,
                ];
            }
        }

        if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'total_discount' => 0,
                    'total_money_service' => 0,
                    'total_money_motel' => 0,
                    'total_money_deposit' => 0,
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
                    $charts[$key]['total_discount'] = $charts[$key]['total_discount'] + $order->discount;
                    $charts[$key]['total_money_service'] = $charts[$key]['total_money_service'] + $order->total_money_service;
                    $charts[$key]['total_money_motel'] = $charts[$key]['total_money_motel'] + $order->total_money_motel;
                    $charts[$key]['total_money_deposit'] = $charts[$key]['total_money_deposit'] + $order->total_money_has_paid_by_deposit;
                    $charts[$key]['total_final'] = $charts[$key]['total_final'] + $order->total_final;
                } else if (
                    $type == 'hour' &&
                    ($chartDatetime->format('Y-m-d H:00:00') == $datePayment->format('Y-m-d H:00:00'))
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d H:00:00');
                    $charts[$key]['total_discount'] = $charts[$key]['total_discount'] + $order->discount;
                    $charts[$key]['total_money_service'] = $charts[$key]['total_money_service'] + $order->total_money_service;
                    $charts[$key]['total_money_motel'] = $charts[$key]['total_money_motel'] + $order->total_money_motel;
                    $charts[$key]['total_money_deposit'] = $charts[$key]['total_money_deposit'] + $order->total_money_has_paid_by_deposit;
                    $charts[$key]['total_final'] = $charts[$key]['total_final'] + $order->total_final;
                } else if (
                    $chartDatetime->format('Y-m-d') == $datePayment->format('Y-m-d') &&
                    $type == 'day'
                ) {
                    $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                    $charts[$key]['total_discount'] = $charts[$key]['total_discount'] + $order->discount;
                    $charts[$key]['total_money_service'] = $charts[$key]['total_money_service'] + $order->total_money_service;
                    $charts[$key]['total_money_motel'] = $charts[$key]['total_money_motel'] + $order->total_money_motel;
                    $charts[$key]['total_money_deposit'] = $charts[$key]['total_money_deposit'] + $order->total_money_has_paid_by_deposit;
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
            'total_discount' => 0,
            'total_money_service' => 0,
            'total_money_motel' => 0,
            'total_money_deposit' => 0,
            'total_final' => 0
        ];

        foreach ($charts as $chart) {
            $dataChart['total_discount'] += $chart['total_discount'];
            $dataChart['total_money_service'] += $chart['total_money_service'];
            $dataChart['total_money_motel'] += $chart['total_money_motel'];
            $dataChart['total_money_deposit'] += $chart['total_money_deposit'];
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
     * Lấy báo cáo doanh thu theo năm
     * 
     * @bodyParam date_from datetime mốc thời gian tính query
     * @bodyParam date_to datetime đích đến thời gian tính query
     * 
     */
    public function getRevenueByYears(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $nowTime = Helper::getTimeNowDateTime();
        $isCheck = false;

        if ($dateFrom != null || $dateTo != null) {
            if (
                !Helper::validateDate($dateFrom, 'Y-m') || !Helper::validateDate($dateTo, 'Y-m')
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

        $dateFrom = new DateTime($dateFrom . '-01' . ' 00:00:01');
        $dateTo = new DateTime($dateTo . '-' . date("t", strtotime($dateTo)) . ' 23:59:59');

        $contractDepositByMonth = DB::table('contracts')
            ->where([
                ['status', StatusContractDefineCode::COMPLETED],
                ['user_id', $request->user->id],
            ])
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('deposit_used_date', '<=', $dateTo);
                $query->where('deposit_used_date', '>=', $dateFrom);
            })
            ->select(
                DB::raw('(sum(deposit_actual_paid) - sum(deposit_amount_paid)) as `total_deposit_used`'),
                DB::raw("DATE_FORMAT(deposit_used_date, '%Y-%m-%d %h:%i:%s') deposit_used_date"),
                DB::raw('YEAR(deposit_used_date) year, MONTH(deposit_used_date) month')
            )
            ->groupby('year', 'month')
            ->get();

        $contractByMonth = DB::table('contracts')
            ->where([
                ['status', StatusContractDefineCode::COMPLETED],
                ['user_id', $request->user->id],
            ])
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('deposit_payment_date', '<=', $dateTo);
                $query->where('deposit_payment_date', '>=', $dateFrom);
            })
            ->select(
                DB::raw('sum(deposit_amount_paid) as `total_deposit`'),
                DB::raw("DATE_FORMAT(deposit_payment_date, '%Y-%m-%d %h:%i:%s') deposit_payment_date"),
                DB::raw('YEAR(deposit_payment_date) year, MONTH(deposit_payment_date) month')
            )
            ->groupby('year', 'month')
            ->get();

        $billByMonth = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->where([
                ['user_id', $request->user->id],
                ['bills.status', StatusBillDefineCode::COMPLETED]
            ])
            ->select('bills.*')
            ->select(
                DB::raw('sum(total_money_motel) as `total_money_motel`'),
                DB::raw('sum(total_money_service) as `total_money_service`'),
                DB::raw('sum(discount) as `discount`'),
                DB::raw('sum(total_money_has_paid_by_deposit) as `total_money_deposit`'),
                DB::raw('(sum(total_final) + sum(total_money_has_paid_by_deposit)) as `total_final`'),
                DB::raw("DATE_FORMAT(date_payment, '%Y-%m-%d %h:%i:%s') date_payment"),
                DB::raw('YEAR(date_payment) year, MONTH(date_payment) month')
            )
            ->when($dateFrom != null && $dateTo != null, function ($query) use ($dateFrom, $dateTo) {
                $query->where('date_payment', '<=', $dateTo);
                $query->where('date_payment', '>=', $dateFrom);
            })
            ->groupby('year', 'month')
            ->get();
        $listBillByMonth = [];
        for ($i = (int)$dateFrom->format('m'); $i <= (int)$dateTo->format('m'); $i++) {
            $isCheck = false;

            for ($y = 0; $y < count($billByMonth); $y++) {
                $totalDeposit = 0;
                $totalDepositUsed = 0;
                if ($billByMonth[$y]['month'] == $i) {
                    if (isset($contractByMonth[$y]) && $contractByMonth[$y]->month == $i) {
                        $totalDeposit = $contractByMonth[$y]->total_deposit;
                    }
                    if (isset($contractDepositByMonth[$y]) && $contractDepositByMonth[$y]->month == $i) {
                        $totalDepositUsed = $contractDepositByMonth[$y]->total_deposit_used;
                    }

                    array_push($listBillByMonth, [
                        "total_deposit" => $totalDeposit,
                        "total_money_motel" => $billByMonth[$y]['total_money_motel'],
                        "total_money_service" => $billByMonth[$y]['total_money_service'],
                        "total_money_deposit" => $totalDeposit,
                        "total_money_deposit_used" => $totalDepositUsed,
                        "discount" => $billByMonth[$y]['discount'],
                        "total_final" => ($billByMonth[$y]['total_final'] + $totalDeposit) - $totalDepositUsed,
                        // "date_payment" => $billByMonth[$y]['date_payment'],
                        "year" => $billByMonth[$y]['year'],
                        "month" => $billByMonth[$y]['month']
                    ]);
                    $isCheck = true;
                }
            }

            if (!$isCheck) {
                $totalDeposit = 0;
                $totalDepositUsed = 0;
                if (isset($contractByMonth[$y]) && $contractByMonth[$y]->month == $i) {
                    $totalDeposit = $contractByMonth[$y]->total_deposit;
                }
                if (isset($contractDepositByMonth[$y]) && $contractDepositByMonth[$y]->month == $i) {
                    $totalDepositUsed = $contractDepositByMonth[$y]->total_deposit_used;
                }

                array_push($listBillByMonth, [
                    "total_money_motel" => 0,
                    "total_money_service" => 0,
                    "total_money_deposit" => $totalDeposit,
                    "total_money_deposit_used" => $totalDepositUsed,
                    "discount" => 0,
                    "total_final" => $totalDeposit - $totalDepositUsed,
                    // "date_payment" => null,
                    "year" => (int)$dateTo->format('Y'),
                    "month" => $i
                ]);
            }
        }

        $dataChart = [
            'charts' => $listBillByMonth,
            'type_chart' => 'month',
            'total_discount' => 0,
            'total_money_service' => 0,
            'total_money_deposit' => 0,
            'total_money_deposit_used' => 0,
            'total_money_motel' => 0,
            'total_final' => 0
        ];

        foreach ($billByMonth as $bill) {
            $dataChart['total_discount'] += $bill->discount;
            $dataChart['total_money_service'] += $bill->total_money_service;
            $dataChart['total_money_motel'] += $bill->total_money_motel;
            $dataChart['total_final'] = $dataChart['total_final'] + $bill->total_final;
        }

        foreach ($contractByMonth as $contract) {
            $dataChart['total_money_deposit'] += $contract->total_deposit;
            $dataChart['total_final'] = $dataChart['total_final'] + $contract->total_deposit;
        }

        foreach ($contractDepositByMonth as $contract) {
            $dataChart['total_money_deposit_used'] +=  $contract->total_deposit_used;
            $dataChart['total_final'] = $dataChart['total_final'] - $contract->total_deposit_used;
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $dataChart
        ]);
    }
}
