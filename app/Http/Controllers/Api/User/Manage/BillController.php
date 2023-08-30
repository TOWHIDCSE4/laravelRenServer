<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\DatetimeUtils;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Renter;
use App\Models\UserContract;
use Illuminate\Http\Request;
use App\Helper\ResponseUtils;
use App\Helper\serviceUtils;
use App\Helper\StatusContractDefineCode;
use App\Jobs\NotificationUserJob;
use App\Models\Bill;
use App\Models\MsgCode;
use App\Models\ServiceClose;
use App\Models\ServiceCloseItem;
use Carbon\Carbon;
use App\Helper\ParamUtils;
use App\Helper\ServiceUnitDefineCode;
use App\Helper\StatusBillDefineCode;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\PhoneUtils;
use App\Helper\StatusMotelDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Api\TingTingSmsController;
use App\Models\Motel;
use App\Models\NotificationUser;
use App\Models\ServiceCloseChild;
use DateTime;
use Illuminate\Http\Response;

/**
 * @group Bill/Chủ trọ/quản lý hóa đơn (1 phòng)
 */
class BillController extends Controller
{

    /**
     * 
     * Thêm 1 hóa đơn 
     * 
     * @bodyParam contract_id int require mã hợp đồng
     * @bodyParam service_closes_id int require mã ngày chốt số dịch vụ
     * @bodyParam mo_services array danh sách dịch vụ
     * @bodyParam date_payment datetime ngày thanh toán
     * @bodyParam total_money_motel double tổng tiền phòng
     * @bodyParam discount double giảm giá tiền phòng
     * @bodyParam deposit_money double tiền cọc
     * @bodyParam images array danh sách ảnh hóa đơn
     * @bodyParam services array listServiceClose [{"id": "45","service_unit": "kwh", "type_unit": 0, "old_quantity": 2000, "quantity": "2050"},{"id": "47","service_unit": "every_use","type_unit": 3, "quantity": "3"}...]
     * @bodyParam total_final double (total_money_motel + total_money_service) - discount
     * 
     */
    public function create(Request $request)
    {
        $totalMoneyMotel = $request->total_money_motel;
        $totalMoneyService = 0;
        $servicesRequest = $request->mo_services ?: [];
        $images = $request->images ?: [];
        $serviceClose = null;
        $depositMoney = 0;
        $totalMoney = 0;
        $discount = $request->discount;
        $total_money_has_paid_by_deposit = 0;
        $total_money_before_paid_by_deposit = 0;
        $total_deposit_in_contract = 0;


        $contractExisted = Contract::where('id', $request->contract_id)
            ->where('user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                $supporterManageTowerIds = DB::table('supporter_manage_towers')
                    ->where('supporter_id', $request->user->id)
                    ->pluck('id');
                $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                    ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                    ->pluck('motels.id');
                $q->whereIn('contracts.motel_id', $motelIds);
            })
            ->first();

        if ($contractExisted == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1]
            ]);
        }

        if ($contractExisted->rent_to != null) {
            $timeRentToContract = Carbon::parse($contractExisted->rent_to);
            if ($timeRentToContract->lt(Helper::getTimeNowDateTime())) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::CONTRACT_HAS_EXPIRED_UNABLE_CREATE_BILL[0],
                    'msg' => MsgCode::CONTRACT_HAS_EXPIRED_UNABLE_CREATE_BILL[1]
                ]);
            }
        }
        if ($request->content != null) {
            $dateBill = Helper::createAndValidateFormatDate($request->content, 'm-Y')->format('Y-m');
            $timeRentToContract = Carbon::parse($contractExisted->rent_to)->format('Y-m');
            $timeRentFromContract = Carbon::parse($contractExisted->rent_from)->format('Y-m');
            if ($dateBill == false) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_BILL_PAYMENT[0],
                    'msg' => MsgCode::INVALID_DATETIME_BILL_PAYMENT[1]
                ]);
            }

            if ($dateBill > $timeRentToContract || $dateBill < $timeRentFromContract) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::UNABLE_CREATE_BILL_OUTSIDE_CONTRACT_PERIOD[0],
                    'msg' => MsgCode::UNABLE_CREATE_BILL_OUTSIDE_CONTRACT_PERIOD[1]
                ]);
            }
        }

        $billsNotPaid = DB::table('bills')
            ->where([
                ['contract_id', $request->contract_id],
                ['is_init', '<>', true],
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($billsNotPaid != null) {
            if ($billsNotPaid->status == StatusBillDefineCode::PROGRESSING) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::BILL_PREVIOUS_NOT_PAID[0],
                    'msg' => MsgCode::BILL_PREVIOUS_NOT_PAID[1]
                ]);
            }
        }

        if (!empty($servicesRequest) && !is_array($servicesRequest)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_SERVICE[0],
                'msg' => MsgCode::INVALID_LIST_SERVICE[1]
            ]);
        }


        if (!is_array($images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        }

        if (count($images) > 0) {
            foreach ($images as $imageItem) {
                if ($imageItem == null || empty($imageItem)) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_IMAGES[0],
                        'msg' => MsgCode::INVALID_IMAGES[1],
                    ]);
                }
            }
        }

        if ($contractExisted->status == StatusContractDefineCode::TERMINATION) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::CONTRACT_HAS_BEEN_TERMINATED[0],
                'msg' => MsgCode::CONTRACT_HAS_BEEN_TERMINATED[1],
            ]);
        }

        if ($contractExisted->status != StatusContractDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_CONTRACT_STATUS[0],
                'msg' => MsgCode::INVALID_CONTRACT_STATUS[1],
            ]);
        }

        if (
            $contractExisted->status == StatusContractDefineCode::PROGRESSING
        ) {
            $depositMoney = $request->deposit_money;
        } else {
            $depositMoney = 0;
        }

        if ($request->service_close_id != null) {
            $serviceClose = ServiceClose::where([
                ['id', $request->service_close_id],
                ['motel_id', $contractExisted->motel_id]
            ]);

            if (!$serviceClose->exists()) {
                return ResponseUtils::json([
                    'code' => RESPONSE::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_SERVICE_BY_ROOM_CLOSEST_EXISTS[0],
                    'msg' => MsgCode::NO_SERVICE_BY_ROOM_CLOSEST_EXISTS[1]
                ]);
            }

            $serviceClose = $serviceClose->first();
        } else {
            $totalMoneyService = 0;
            $serviceClose = ServiceClose::create([
                'motel_id' => $contractExisted->motel_id,
                'close_date' => DatetimeUtils::getNow()
            ]);

            foreach ($servicesRequest as $serviceItem) {
                // check relate number
                if (!empty($serviceItem['quantity']) && ($serviceItem['quantity'] < 1)) {
                    return ResponseUtils::json([
                        'code' => RESPONSE::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_QUANTITY[0],
                        'msg' => MsgCode::INVALID_QUANTITY[1]
                    ]);
                }

                if (isset($serviceItem['type_unit']) && $serviceItem['type_unit'] == ServiceUnitDefineCode::SERVICE_INDEX) {
                    if (!empty($serviceItem['old_quantity']) && ($serviceItem['old_quantity'] > $serviceItem['quantity'])) {
                        return ResponseUtils::json([
                            'code' => RESPONSE::HTTP_BAD_REQUEST,
                            'success' => false,
                            'msg_code' => MsgCode::OLD_QUANTITY_CANNOT_GREATER_THAN_NEW_QUANTITY[0],
                            'msg' => MsgCode::OLD_QUANTITY_CANNOT_GREATER_THAN_NEW_QUANTITY[1]
                        ]);
                    }
                }

                if (!empty($serviceItem['old_quantity']) && ($serviceItem['service_charge'] < 1)) {
                    return ResponseUtils::json([
                        'code' => RESPONSE::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_SERVICE_CHARGE[0],
                        'msg' => MsgCode::INVALID_SERVICE_CHARGE[1]
                    ]);
                }

                if (!empty($serviceItem['images']) && !is_array($serviceItem['images'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_IMAGES[0],
                        'msg' => MsgCode::INVALID_IMAGES[1],
                    ]);
                }

                if (ServiceUnitDefineCode::getServiceUnitCode($serviceItem['type_unit'] ?? null, false) == null) {
                    DB::table('service_close_children')->where('service_close_id', $serviceClose->id)->delete();
                    $serviceClose->delete();
                    return ResponseUtils::json([
                        'code' => RESPONSE::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                        'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1],
                    ]);
                }

                $total = serviceUtils::calculateServiceCloseItem(
                    $serviceItem['type_unit'],
                    $serviceItem['service_charge'],
                    $serviceItem['quantity'],
                    $serviceItem['old_quantity'] ?? 0
                );

                $serviceCloseItemCreate = ServiceCloseChild::create([
                    'service_close_id' => $serviceClose->id,
                    'service_name' => $serviceItem['service_name'],
                    'service_icon' => $serviceItem['service_icon'],
                    'service_unit' => $serviceItem['service_unit'],
                    'service_charge' => $serviceItem['service_charge'],
                    "images" => json_encode($serviceItem['images'] ?? []),
                    "type_unit" => $serviceItem['type_unit'],
                    'quantity' => $serviceItem['quantity'],
                    'old_quantity' => isset($serviceItem['old_quantity']) ? $serviceItem['old_quantity'] : 0,
                    'total' => $total
                ]);
                $totalMoneyService += $serviceCloseItemCreate->total;
            }
        }


        $total_money_before_paid_by_deposit = ($totalMoneyMotel + $totalMoneyService) - $discount;

        if ($request->has_use_deposit == true) {
            $tempTotalMoneyHasPaidByDeposit = $contractExisted->deposit_amount_paid - (($totalMoneyMotel + $totalMoneyService) - $discount);
            $tempTotalMoney = -$contractExisted->deposit_amount_paid + (($totalMoneyMotel + $totalMoneyService) - $discount);
            if ($tempTotalMoneyHasPaidByDeposit <= 0) {
                $tempTotalMoneyHasPaidByDeposit = $contractExisted->deposit_amount_paid;
            } else if ($total_money_before_paid_by_deposit < $contractExisted->deposit_amount_paid) {
                $tempTotalMoneyHasPaidByDeposit = $total_money_before_paid_by_deposit;
            }
            $total_money_has_paid_by_deposit = $tempTotalMoneyHasPaidByDeposit;
            if ($tempTotalMoney <= 0) {
                $tempTotalMoney = 0;
            }
            $totalMoney = $tempTotalMoney;
        } else {
            $totalMoney = ($totalMoneyMotel + $totalMoneyService) - $discount;
        }

        $createdBill = Bill::create([
            'user_maker_id' => $request->user->id,
            'contract_id' => $contractExisted->id,
            'service_close_id' => $serviceClose->id,
            'status' => $request->has_use_deposit && $totalMoney == 0 ? StatusBillDefineCode::COMPLETED : StatusBillDefineCode::PROGRESSING,
            'total_money_has_paid_by_deposit' => $request->has_use_deposit ? $total_money_has_paid_by_deposit : 0,
            'total_money_before_paid_by_deposit' => $total_money_before_paid_by_deposit,
            'has_use_deposit' => $request->has_use_deposit ? true : false,
            'date_payment' => $request->has_use_deposit && $totalMoney == 0 ? Helper::getTimeNowDateTime() : $request->date_payment,
            'type_bill' => $request->type_bill ?? 0,
            'content' => ($request->has_use_deposit == true && $totalMoney == 0) ? "Hóa đơn đã được chủ nhà thanh toán bằng tiền cọc" : $request->content,
            'note' => $request->note,
            'total_money_motel' => $totalMoneyMotel,
            'total_money_service' => $totalMoneyService,
            'total_final' => $totalMoney,
            'content' => $request->content,
            "images"  => json_encode($images),
            'discount' => $discount ?? 0
        ]);


        $listUser = DB::table('users')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['user_contracts.contract_id', $contractExisted->id]
            ])
            ->select('users.*')
            ->get();

        // setup notifications
        $timeMY = DateTime::createFromFormat('m-Y', $request->content);
        $month = null;

        if ($timeMY) {
            $month = $timeMY->format('m');
        }

        if ($request->has_use_deposit != true) {
            foreach ($listUser as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    "Hóa đơn mới",
                    'Bạn có hóa đơn mới tháng ' . $month . ' cần thanh toán ' . Helper::currency_money_format($totalMoney),
                    TypeFCM::NEW_BILL,
                    NotiUserDefineCode::USER_NORMAL,
                    $createdBill->id,
                );
            }
        } else if ($request->has_use_deposit == true) {
            // update contract

            if ($totalMoney == 0) {
                $contractExisted->update([
                    'deposit_amount_paid' => $contractExisted->deposit_amount_paid - $total_money_has_paid_by_deposit,
                    'deposit_used_date' => Helper::getTimeNowDateTime()
                ]);

                foreach ($listUser as $user) {
                    NotificationUserJob::dispatch(
                        $user->id,
                        "Hóa đơn đã được thanh toán",
                        'Bạn có hóa đơn mới tháng ' . $month . ' đã được thanh thanh toán ' . Helper::currency_money_format($totalMoney) . ' bởi chủ nhà thông qua tiền đặt cọc',
                        TypeFCM::NEW_BILL,
                        NotiUserDefineCode::USER_NORMAL,
                        $createdBill->id,
                    );
                }
            } else {
                foreach ($listUser as $user) {
                    NotificationUserJob::dispatch(
                        $user->id,
                        "Hóa đơn mới",
                        'Bạn có hóa đơn mới tháng ' . $month . ' cần thanh toán ' . Helper::currency_money_format($totalMoney),
                        TypeFCM::NEW_BILL,
                        NotiUserDefineCode::USER_NORMAL,
                        $createdBill->id,
                    );
                }
            }
        }

        if ($totalMoney > 0) {
            try {
                $userRepresentContractExists = DB::table('users')
                    ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                    ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['user_contracts.contract_id', $contractExisted->id]
                    ])
                    ->select('users.*')
                    ->get();

                if ($userRepresentContractExists != null) {
                    foreach ($userRepresentContractExists as $renter) {
                        if (PhoneUtils::isNumberPhoneValid($renter->phone_number)) {
                            TingTingSmsController::sendMotelBill($renter->phone_number, $timeMY->format('m/Y'), $totalMoney, $contractExisted->id, $contractExisted->user_id, $renter->id);
                        }
                    }
                }
            } catch (\Throwable $th) {
            }
        }



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Bill::where('id', $createdBill->id)->first()
        ]);
    }

    /**
     * 
     * Sửa 1 hóa đơn
     * 
     * @bodyParam service_close_id int require mã dịch vụ chốt số 
     * @bodyParam date_payment datetime ngày thanh toán
     * @bodyParam total_money_motel double tổng tiền phòng
     * @bodyParam images array ảnh hóa đơn
     * @bodyParam discount double giảm giá tiền phòng
     * @bodyParam mo_services array listServiceClose [{"id": "45","service_unit": "kwh", "type_unit": 0, "old_quantity": 2000, "quantity": "2050"},{"id": "47","service_unit": "every_use","type_unit": 3, "quantity": "3"}...]
     * @bodyParam total_final double (total_money_motel + total_money_service) - discount
     * 
     */
    public function update(Request $request, $id)
    {
        $totalMoneyMotel = $request->total_money_motel;
        $totalMoneyService = 0;
        $servicesRequest = $request->mo_services ?: [];
        $depositMoney = 0;
        $images = $request->images ?: [];
        $totalMoney = 0;
        $totalOldMoney = 0;
        $discount = $request->discount;
        $billExistedData = null;
        $serviceClose = null;
        $depositMoney = 0;
        $total_money_has_paid_by_deposit = 0;
        $total_money_before_paid_by_deposit = 0;
        $total_deposit_in_contract = 0;

        $billExisted = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->where([
                ['bills.id', $id],
                ['contracts.id', $request->contract_id],
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ['bills.service_close_id', $request->service_close_id],
            ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->pluck('id');
                        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('motels.id');
                        $q->whereIn('contracts.motel_id', $motelIds);
                    });
                }
            })->select('bills.*')->first();

        if ($billExisted == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_BILL_EXISTS[0],
                'msg' => MsgCode::NO_BILL_EXISTS[1]
            ]);
        }

        $contractExisted = Contract::where('id', $request->contract_id)
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('contracts.user_id', $request->user->id);
                }
            })
            ->first();

        $totalOldMoney = $billExisted->total_final;

        if ($request->user->is_admin == false) {
            if ($contractExisted->status != StatusContractDefineCode::COMPLETED) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_CONTRACT_STATUS[0],
                    'msg' => MsgCode::INVALID_CONTRACT_STATUS[1],
                ]);
            }
        }

        if (!empty($images) && !is_array($images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        }

        if (!empty($servicesRequest)) {
            if (!is_array($servicesRequest)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_LIST_SERVICE[0],
                    'msg' => MsgCode::INVALID_LIST_SERVICE[1]
                ]);
            }
        }

        if ($request->user->is_admin == false) {
            if ($billExisted->status != StatusBillDefineCode::PROGRESSING) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::BILL_HAS_PAID[0],
                    'msg' => MsgCode::BILL_HAS_PAID[1]
                ]);
            }
        }

        $totalMoneyService = 0;

        $serviceClose = ServiceClose::where([
            ['id', $request->service_close_id],
            ['motel_id', $contractExisted->motel_id]
        ]);

        if (!$serviceClose->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_BY_ROOM_CLOSEST_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_BY_ROOM_CLOSEST_EXISTS[1]
            ]);
        }

        $totalMoneyService = 0;

        // foreach check data request
        foreach ($servicesRequest as $serviceItem) {
            if (ServiceUnitDefineCode::getServiceUnitCode($serviceItem['type_unit'] ?? null, false) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                    'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1],
                ]);
            }

            if (!empty($serviceItem['quantity']) && ($serviceItem['quantity'] < 1)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_QUANTITY[0],
                    'msg' => MsgCode::INVALID_QUANTITY[1]
                ]);
            }

            if (isset($serviceItem['type_unit']) && $serviceItem['type_unit'] == ServiceUnitDefineCode::SERVICE_INDEX) {
                if (!empty($serviceItem['old_quantity']) && ($serviceItem['old_quantity'] > $serviceItem['quantity'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::OLD_QUANTITY_CANNOT_GREATER_THAN_NEW_QUANTITY[0],
                        'msg' => MsgCode::OLD_QUANTITY_CANNOT_GREATER_THAN_NEW_QUANTITY[1]
                    ]);
                }
            }

            if (!empty($serviceItem['old_quantity']) && ($serviceItem['service_charge'] < 1)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_SERVICE_CHARGE[0],
                    'msg' => MsgCode::INVALID_SERVICE_CHARGE[1]
                ]);
            }
        }

        DB::table('service_close_children')->where('service_close_id',  $request->service_close_id)->delete();

        // calculate and create services close 
        foreach ($servicesRequest as $serviceItem) {
            $total = serviceUtils::calculateServiceCloseItem(
                $serviceItem['type_unit'],
                $serviceItem['service_charge'],
                $serviceItem['quantity'],
                $serviceItem['old_quantity'] ?? 0
            );

            $serviceCloseItemCreate = ServiceCloseChild::create([
                'service_close_id' => $request->service_close_id,
                'service_name' => $serviceItem['service_name'] ?? '',
                'service_icon' => $serviceItem['service_icon'] ?? '',
                'service_unit' => $serviceItem['service_unit'],
                'service_charge' => $serviceItem['service_charge'],
                "images" => json_encode($serviceItem['images'] ?? []),
                "type_unit" => $serviceItem['type_unit'],
                'quantity' => $serviceItem['quantity'],
                'old_quantity' => isset($serviceItem['old_quantity']) ? $serviceItem['old_quantity'] : 0,
                'total' => $total
            ]);

            $totalMoneyService += $serviceCloseItemCreate->total;
        }

        $total_money_before_paid_by_deposit = ($totalMoneyMotel + $totalMoneyService) - $discount;

        if ($request->has_use_deposit == true) {
            $tempTotalMoneyHasPaidByDeposit = $contractExisted->deposit_amount_paid - (($totalMoneyMotel + $totalMoneyService) - $discount);
            $tempTotalMoney = -$contractExisted->deposit_amount_paid + (($totalMoneyMotel + $totalMoneyService) - $discount);
            if ($tempTotalMoneyHasPaidByDeposit <= 0) {
                $tempTotalMoneyHasPaidByDeposit = $contractExisted->deposit_amount_paid;
            } else if ($total_money_before_paid_by_deposit < $contractExisted->deposit_amount_paid) {
                $tempTotalMoneyHasPaidByDeposit = $total_money_before_paid_by_deposit;
            }
            $total_money_has_paid_by_deposit = $tempTotalMoneyHasPaidByDeposit;
            if ($tempTotalMoney <= 0) {
                $tempTotalMoney = 0;
            }
            $totalMoney = $tempTotalMoney;
        } else {
            $totalMoney = ($totalMoneyMotel + $totalMoneyService) - $discount;
        }

        $billExisted->update([
            'contract_id' => $contractExisted->id,
            'status' => $request->user->is_admin == true ? $billExisted->status  : StatusBillDefineCode::PROGRESSING,
            'total_money_has_paid_by_deposit' => $request->has_use_deposit ? $total_money_has_paid_by_deposit : 0,
            'total_money_before_paid_by_deposit' => $total_money_before_paid_by_deposit,
            'has_use_deposit' => $request->has_use_deposit ? true : false,
            'type_bill' => isset($request->type_bill) ? $request->type_bill : StatusBillDefineCode::BILL_BY_MONTH,
            'content' => ($request->has_use_deposit == true && $totalMoney == 0) ? "Hóa đơn đã được chủ nhà thanh toán bằng tiền cọc" : $request->content ?? $billExisted->content,
            'note' => $request->note,
            'date_payment' => $request->date_payment ?? ($request->status == StatusBillDefineCode::COMPLETED ? Helper::getTimeNowDateTime() : $billExisted->date_payment),
            'total_money_motel' => $totalMoneyMotel,
            'total_money_service' => $totalMoneyService,
            'total_final' => $totalMoney,
            'discount' => $discount != null ? $discount : $billExisted->discount,
            'images'  => json_encode($images)
        ]);

        $listNoti = NotificationUser::where([
            ['references_value', $contractExisted->id],
            ['type', TypeFCM::NEW_BILL],
            ['role', NotiUserDefineCode::USER_NORMAL]
        ]);

        $timeMY = DateTime::createFromFormat('m-Y', $request->content);
        $month = null;
        if ($timeMY) {
            $month = $timeMY->format('m');
        }
        if ($listNoti != null) {
            if ($request->has_use_deposit != true) {
                $listNoti->update([
                    'title' => "Hóa đơn mới",
                    'content' => 'Bạn có hóa đơn mới tháng ' . $month . ' cần thanh toán ' . Helper::currency_money_format($totalMoney),
                ]);
            } else if ($request->has_use_deposit == true) {
                // update contract
                // $contractExisted->update([
                //     'deposit_amount_paid' => $contractExisted->deposit_amount_paid - $total_money_has_paid_by_deposit,
                // ]);
                if ($totalMoney == 0) {
                    $listNoti->update([
                        'title' => "Hóa đơn đã được thanh toán",
                        'content' => 'Bạn có hóa đơn mới tháng ' . $month . ' đã được thanh thanh toán ' . Helper::currency_money_format($totalMoney) . ' bởi chủ nhà thông qua tiền đặt cọc',
                    ]);
                } else {
                    $listNoti->update([
                        'title' => "Hóa đơn mới",
                        'content' => 'Bạn có hóa đơn mới tháng ' . $month . ' cần thanh toán ' . Helper::currency_money_format($totalMoney),
                    ]);
                }
            }
        }

        if ($request->user->is_admin == true && $request->user->is_admin != $contractExisted->user_id) {
            // setup notifications
            $contractExist = Contract::where('id', $billExisted->contract_id)->first();
            $listUser = DB::table('users')
                ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['user_contracts.contract_id', $contractExist->id]
                ])
                ->select('users.*')
                ->get();
            $motelExist = DB::table('motels')->where('id', $contractExist->motel_id)->first();

            NotificationUserJob::dispatch(
                $contractExisted->user_id,
                "Hóa đơn đã được thay đổi bởi admin",
                'Hóa đơn phòng ' . $motelExist->motel_name . ' đã được thay đổi bởi admin',
                TypeFCM::BILL_CHANGED,
                NotiUserDefineCode::USER_IS_HOST,
                $billExisted->id,
            );

            foreach ($listUser as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    "Hóa đơn đã được thay đổi bởi admin",
                    'Hóa đơn phòng ' . $motelExist->motel_name . ' đã được thay đổi bởi admin',
                    TypeFCM::BILL_CHANGED,
                    NotiUserDefineCode::USER_NORMAL,
                    $billExisted->id,
                );
            }
        }


        if ($totalMoney > 1000 && $totalMoney != $totalOldMoney) {
            try {
                $userRepresentContractExists = DB::table('users')
                    ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                    ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    ->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['user_contracts.contract_id', $contractExisted->id]
                    ])
                    ->select('users.*')
                    ->get();

                if ($userRepresentContractExists != null) {
                    foreach ($userRepresentContractExists as $renter) {
                        if (PhoneUtils::isNumberPhoneValid($renter->phone_number)) {
                            TingTingSmsController::sendMotelBill($renter->phone_number, $timeMY->format('m/Y'), $totalMoney, $contractExisted->id, $contractExisted->user_id, $renter->id);
                        }
                    }
                }
            } catch (\Throwable $th) {
            }
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Bill::where('id', $id)->first()
        ]);
    }

    /**
     * 
     * Cập nhật trạng thái hóa đơn 
     * 
     * @bodyParam status
     * 
     */
    public function updateStatus(Request $request, $id)
    {
        $billExisted = Bill::where([
            ['bills.id', $id],
            ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL]
        ])->first();

        if ($billExisted == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_BILL_EXISTS[0],
                'msg' => MsgCode::NO_BILL_EXISTS[1]
            ]);
        }


        if (strlen($billExisted->status) > 0) {
            if ($billExisted->status == StatusBillDefineCode::CANCEL_BY_HOST) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::CANCEL_BY_HOST[0],
                    'msg' => MsgCode::CANCEL_BY_HOST[1]
                ]);
            }
            if ($billExisted->status == StatusBillDefineCode::CANCEL_BY_RENTER) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::CANCEL_BY_RENTER[0],
                    'msg' => MsgCode::CANCEL_BY_RENTER[1]
                ]);
            }
            if ($billExisted->status == StatusBillDefineCode::COMPLETED) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::BILL_HAS_PAID[0],
                    'msg' => MsgCode::BILL_HAS_PAID[1]
                ]);
            }
            if (StatusBillDefineCode::getStatusBillCode($request->status, false) == false) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_BILL_STATUS[0],
                    'msg' => MsgCode::INVALID_BILL_STATUS[1]
                ]);
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => MsgCode::ERROR[1]
            ]);
        }

        $billExisted->update([
            'status' => $request->status,
            'date_payment' => $request->status == StatusBillDefineCode::COMPLETED ? Helper::getTimeNowDateTime() : $billExisted->date_payment,
        ]);


        // setup notifications
        $contractExist = Contract::where('id', $billExisted->contract_id)->first();
        $listUser = DB::table('users')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['user_contracts.contract_id', $contractExist->id]
            ])
            ->select('users.*')
            ->get();
        $motelExist = DB::table('motels')->where('id', $contractExist->motel_id)->first();

        if ($request->status == StatusBillDefineCode::COMPLETED) {
            if ($billExisted->has_use_deposit == true) {
                $contractExist->update([
                    'deposit_amount_paid' => $contractExist->deposit_amount_paid - $billExisted->total_money_has_paid_by_deposit,
                ]);
            }

            foreach ($listUser as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    "Hóa đơn đã được chủ nhà xác nhận",
                    'Hóa đơn phòng ' . $motelExist->motel_name . ' đã được chủ nhà xác nhận thanh toán vào ngày ' . Helper::getTimeNowDateTime()->format('Y-m-d h:i:s'),
                    TypeFCM::NEW_BILL,
                    NotiUserDefineCode::USER_NORMAL,
                    $billExisted->id,
                );
            }
        } else if ($request->status == StatusBillDefineCode::CANCEL_BY_HOST) {
            foreach ($listUser as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    "Xác nhận hóa đơn đã bị hủy bởi chủ nhà",
                    'Xác nhận hóa đơn phòng ' . $motelExist->motel_name . ' đã bị hủy bởi chủ nhà vào ngày ' . Helper::getTimeNowDateTime()->format('Y-m-d h:i:s'),
                    TypeFCM::NEW_BILL,
                    NotiUserDefineCode::USER_NORMAL,
                    $billExisted->id,
                );
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Bill::where('id', $id)->first()
        ]);
    }


    /*
     * 
     * Danh sách hóa đơn 
     * 
     * @queryParam motel_id int tìm hóa đơn theo mã phòng
     * @queryParam motel_name string số phòng ()
     * @queryParam phone_number string số điện thoại
     * @queryParam status int trạng thái hóa đơn
     * @queryParam date_from datetime ngày bắt đầu tìm
     * @queryParam date_to datetime ngày bắt kết thúc
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string search theo: content,
     * 
     */
    public function getAll(Request $request)
    {
        $motel_id = $request->motel_id;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        //Config datetime
        $carbon = DatetimeUtils::getNow();
        $date1 = null;
        $date2 = null;
        $isExist = null;
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => RESPONSE::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }


        if ($motel_id != null) {
            $supporterManageTowerIds = DB::table('supporter_manage_towers')
                ->when($request->is_have_supporter != null, function ($subQ) use ($request) {
                    $subQ->where('supporter_id', $request->user->id);
                })
                ->when($request->manage_supporter_id !== null, function ($subQ) use ($request) {
                    $subQ->where('id', $request->manage_supporter_id);
                })
                ->distinct()
                ->pluck('id');

            $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                ->where('connect_manage_motels.motel_id', $motel_id)
                ->when($request->tower_id != null, function ($subQ) use ($request) {
                    $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
                })
                ->distinct()
                ->pluck('motels.id');
            $isExist = DB::table('motels')
                ->where([
                    ['id', $motel_id],
                    ['status', StatusMotelDefineCode::MOTEL_HIRED]
                ])
                ->where('user_id', $request->user->id)->orWhereIn('motels.id', $motelIds)
                ->exists();
            if (!$isExist) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_MOTEL_EXISTS[1],
                ]);
            }
        }

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }

        $listBill = Bill::sortByRelevance(true)
            ->join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
            ->where([
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
            ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->pluck('id');

                        $contractIds = Contract::join('connect_manage_motels', 'contracts.motel_id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('contracts.id');
                        $q->whereIn('bills.contract_id', $contractIds);
                    });
                }
            })
            ->when($request->phone_number != null, function ($query) use ($request) {
                $query->where('user_contracts.renter_phone_number', 'LIKE', '%' . $request->phone_number . '%');
            })
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('bills.status', $request->status);
            })
            ->when($isExist, function ($query) use ($motel_id) {
                $query->where('user_contracts.motel_id', $motel_id);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('bills.date_payment', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('bills.date_payment', '<=', $dateTo);
            })
            ->when(!empty($sortBy) && Bill::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when($request->search != null, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->select('bills.*')
            ->distinct('bills.id')
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listBill
        ]);
    }

    /**
     * 
     * Lấy 1 hóa đơn 
     * 
     * @urlParam bill_id int tìm hóa đơn theo mã 
     * 
     */
    public function getOne(Request $request, $id)
    {
        $billExisted = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->where([
                ['bills.id', $id],
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
            ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->pluck('id');

                        $contractIds = Contract::join('connect_manage_motels', 'contracts.motel_id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('contracts.id');
                        $q->whereIn('bills.contract_id', $contractIds);
                    });;
                }
            })
            ->select('bills.*')
            ->first();

        if (empty($billExisted)) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => true,
                'msg_code' => MsgCode::NO_BILL_EXISTS[0],
                'msg' => MsgCode::NO_BILL_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $billExisted
        ]);
    }


    /**
     * 
     * Lấy hóa đơn theo phòng
     * 
     * @urlParam motel_id int
     * 
     */
    public function getLatestBillByMotel(Request $request)
    {
        if (empty($request->motel_id)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::MOTEL_ID_IS_REQUIRE[0],
                'msg' => MsgCode::MOTEL_ID_IS_REQUIRE[1]
            ]);
        }
        $supporterManageTowerIds = DB::table('supporter_manage_towers')
            ->when($request->is_have_supporter != null, function ($subQ) use ($request) {
                $subQ->where('supporter_id', $request->user->id);
            })
            ->when($request->manage_supporter_id !== null, function ($subQ) use ($request) {
                $subQ->where('id', $request->manage_supporter_id);
            })
            ->distinct()
            ->pluck('id');

        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
            ->when($request->tower_id != null, function ($subQ) use ($request) {
                $subQ->where('connect_manage_motels.tower_id', $request->tower_id);
            })
            ->where('connect_manage_motels.motel_id', $request->motel_id)
            ->distinct()
            ->pluck('motels.id');
        $motelExits = DB::table('motels')->where('id', $request->motel_id)
            ->where('user_id', $request->user->id)->orWhereIn('motels.id', $motelIds)
            ->exists();

        if (!$motelExits) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        $billExisted = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            // ->join('renters', 'contracts.renter_phone_number', '=', 'renters.phone_number')
            ->where([
                // ['renters.phone_number', $request->user->phone_number],
                ['contracts.motel_id', $request->motel_id],
                ['contracts.status', StatusContractDefineCode::COMPLETED],
                ['bills.type_bill', StatusBillDefineCode::BILL_BY_MONTH],
            ])
            ->where('contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                $supporterManageTowerIds = DB::table('supporter_manage_towers')
                    ->where('supporter_id', $request->user->id)
                    ->pluck('id');

                $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                    ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                    ->pluck('motels.id');
                $q->whereIn('contracts.motel_id', $motelIds);
            })
            ->orderBy('date_payment', 'desc')
            ->select('bills.*')
            ->first();


        $contract = Contract::where([
            ['contracts.status', StatusContractDefineCode::COMPLETED],
            ['contracts.motel_id', $request->motel_id]
        ])
            ->where('contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                $supporterManageTowerIds = DB::table('supporter_manage_towers')
                    ->where('supporter_id', $request->user->id)
                    ->pluck('id');

                $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                    ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                    ->pluck('motels.id');
                $q->whereIn('contracts.motel_id', $motelIds);
            })
            ->select('contracts.*')
            ->first();

        $groupApiBill = [
            'bill_closest' => $billExisted,
            'contract' => $contract
        ];

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $groupApiBill
        ]);
    }

    /**
     * 
     * Xóa 1 hóa đơn 
     * 
     * @urlParam bill_id int tìm hóa đơn theo mã 
     * 
     */
    public function delete(Request $request, $id)
    {
        $billExisted = Bill::join('contracts', 'bills.contract_id', '=', 'contracts.id')
            ->where([
                ['bills.id', $id],
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
            ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                        $supporterManageTowerIds = DB::table('supporter_manage_towers')
                            ->where('supporter_id', $request->user->id)
                            ->pluck('id');

                        $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                            ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                            ->pluck('motels.id');
                        $q->whereIn('contracts.motel_id', $motelIds);
                    });
                }
            })
            ->select('bills.*');

        if (!$billExisted->exists()) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => true,
                'msg_code' => MsgCode::NO_BILL_EXISTS[0],
                'msg' => MsgCode::NO_BILL_EXISTS[1]
            ]);
        }

        $billExisted->delete();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }
}
