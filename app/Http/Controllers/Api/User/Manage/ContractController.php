<?php

namespace App\Http\Controllers\Api\User\Manage;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Helper\AccountRankDefineCode;
use App\Helper\DatetimeUtils;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\PhoneUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\StatusCollaboratorReferMotelDefineCode;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Helper\StatusMoPostDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Api\TingTingSmsController;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\Motel;
use App\Models\MsgCode;
use App\Models\Renter;
use App\Models\ServiceClose;
use App\Models\UserContract;
use App\Jobs\NotificationAdminJob;
use App\Models\CollaboratorReferMotel;
use App\Models\HistoryPotentialUser;
use App\Models\PersonChats;
use App\Models\PotentialUser;
use App\Models\ServiceCloseChild;
use App\Models\Tower;
use Exception;

/**
 * @group User/Quản lý/Hợp đồng
 */

class ContractController extends Controller
{
    /**
     * 
     * Danh cách hợp đồng
     * 
     * @queryParam contract_status int (2 đang hoạt động,1 quá hạn, 0 đã thanh lý )
     * @queryParam represent_name  string Tên người đại diện thuê
     * @queryParam phone_number string số điện thoại người thuê
     * @queryParam motel_name string tên phòng
     * @queryParam limit int Số item trong page
     * @queryParam sort_by string tên cột sắp xếp
     * @queryParam money_from int
     * @queryParam money_to int
     * @bodyParam date_from date ngày bắt đầu
     * @bodyParam date_to date ngày kết thúc
     * @queryParam descending boolean sắp xếp theo (default = false)
     * @queryParam search string tìm kiếm (title)
     * 
     */
    public function getAll(Request $request)
    {
        $contractStatus = $request->contract_status;
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;
        $search = $request->search;
        $limit = $request->limit ?: 20;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $now = Helper::getTimeNowString();

        if ($request->filled('contract_status')) {
            if (StatusContractDefineCode::getStatusContractCode($contractStatus, false) == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                    'msg' => MsgCode::NO_STATUS_EXISTS[1],
                ]);
            }
        }

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

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

        // handle contract expiry
        try {
            Contract::where('status', '!=', 1)
                ->where('rent_to', '<',  $now)
                ->update([
                    'status' => StatusContractDefineCode::TERMINATION
                ]);

            Motel::whereIn('id', DB::table('contracts')->where('status', '!=', 1)
                ->where([
                    ['rent_to', '<',  $now],
                    ['status', StatusContractDefineCode::TERMINATION]
                ])->pluck('id'))
                ->update([
                    'motels.status' => StatusMotelDefineCode::MOTEL_EMPTY
                ]);
        } catch (\Throwable $th) {
        }


        $all = Contract::where(function ($query) use ($request) {
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
            ->when($contractStatus != null, function ($query) use ($contractStatus) {
                if ($contractStatus == StatusContractDefineCode::PROGRESSING) {
                    $query->whereIn('contracts.status', [StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
                    $query->orderBy('contracts.status', 'asc');
                } else {
                    $query->where('contracts.status', $contractStatus);
                }
            })
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($toMoney != null && is_numeric($toMoney), function ($query) use ($toMoney, $typeMoney) {
                $query->where($typeMoney, '<=', $toMoney);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('contracts.created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('contracts.created_at', '<=', $dateTo);
            })
            ->when($request->search != null, function ($query) {
                $query->join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id');
                $query->join('renters', 'user_contracts.renter_phone_number', '=', 'renters.phone_number');
                $query->join('motels', 'contracts.motel_id', '=', 'motels.id');
                $query->where(function ($q) {
                    $q->where('renters.phone_number', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('renters.name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('motels.motel_name', 'LIKE', '%' . request('search') . '%');
                });
            })
            ->when($sortBy != null && Schema::hasColumn('contracts', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->distinct('contracts.id')
            ->select('contracts.*')
            ->paginate($limit);



        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $all,
        ]);
    }


    /**
     * 
     * Thêm 1 hợp đồng
     * 
     * @bodyParam motel_id int phòng cho thuê
     * @bodyParam money double tiền phòng
     * @bodyParam rent_from time thuê từ ngày
     * @bodyParam rent_to time thuê đến ngày
     * @bodyParam pay_start time ngày bắt đầu tính tiền
     * @bodyParam payment_space int kỳ thanh toán
     * @bodyParam deposit_money double tiền đặt cọc
     * @bodyParam images list danh sách ảnh
     * @bodyParam mo_services list danh sách danh sách dịch vụ
     * @bodyParam list_renter list danh sách người thuê
     * 
     */
    public function create(Request $request)
    {
        $renterPhoneNumberRepresent = null;
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
            ->distinct()
            ->pluck('motels.id');

        //check motel exists
        $motelExists = Motel::where('id', $request->motel_id)
            ->where(function ($query) use ($request, $motelIds) {
                $query->where('user_id', $request->user->id)->orWhereIn('motels.id', $motelIds);
            })
            ->first();

        if ($motelExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        // check list renter
        if ($request->list_renter != null && is_array($request->list_renter)) {
            $countRepresent = 0;
            $listNumberPhone = array_unique(array_column($request->list_renter, 'phone_number'));
            if (count($listNumberPhone) != count($request->list_renter)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::DOUBLE_PHONE_NUMBER_IN_LIST_RENTER[0],
                    'msg' => MsgCode::DOUBLE_PHONE_NUMBER_IN_LIST_RENTER[1],
                ]);
            }

            foreach ($request->list_renter as $renter) {
                if (isset($renter['is_represent']) && $renter['is_represent']) {
                    $renterPhoneNumberRepresent = $renter['phone_number'];
                    $countRepresent += 1;
                    // if (!$renter['is_represent']) {
                    //     return ResponseUtils::json([
                    //         'code' => Response::HTTP_BAD_REQUEST,
                    //         'success' => false,
                    //         'msg_code' => MsgCode::INVALID_REPRESENT[0],
                    //         'msg' => MsgCode::INVALID_REPRESENT[1],
                    //     ]);
                    // }
                }
            }

            if (DB::table('users')->where('is_host', true)->whereIn('phone_number', $listNumberPhone)->exists()) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::PHONE_NUMBER_HAS_REGISTER_HOST[0],
                    'msg' => MsgCode::PHONE_NUMBER_HAS_REGISTER_HOST[1],
                ]);
            }

            if ($countRepresent > 1) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::TOO_MANY_REPRESENT[0],
                    'msg' => MsgCode::TOO_MANY_REPRESENT[1],
                ]);
            }

            // foreach ($request->list_renter as $renter) {
            $renterExist = DB::table('user_contracts')->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->whereIn('renter_phone_number', $listNumberPhone)
                ->where([
                    ['contracts.user_id', $request->user->id],
                ])
                ->whereNotIn('contracts.status', [StatusContractDefineCode::TERMINATION, StatusContractDefineCode::UNCONFIRMED_BY_HOST])
                ->select('user_contracts.*')
                ->get();

            if ($renterExist->isNotEmpty()) {
                // foreach ($renterExist as $key => $value) {
                //     # code...
                // }

                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::RENTER_ALREADY_EXISTS[0],
                    'msg' => MsgCode::RENTER_ALREADY_EXISTS[1],
                ]);
            }

            // if ($renterExist != null) {
            //     return ResponseUtils::json([
            //         'code' => Response::HTTP_BAD_REQUEST,
            //         'success' => false,
            //         'msg_code' => MsgCode::THIS_PHONE_NUMBER_REGISTERED_RENTER[0] . $renter['phone_number'],
            //         'msg' => MsgCode::THIS_PHONE_NUMBER_REGISTERED_RENTER[1] . $renter['phone_number'],
            //     ]);
            // }
            // }

            // $amountRepresent = DB::table('user_contracts')->where([
            //     ['user_id', $request->user->id],
            //     ['motel_id', $request->motel_id],
            //     ['is_represent', StatusContractDefineCode::IS_REPRESENT],
            //     ['renter_phone_number', '<>', $renterPhoneNumberRepresent],
            // ])->count();

            // if ($amountRepresent >= 1) {
            //     return ResponseUtils::json([
            //         'code' => Response::HTTP_BAD_REQUEST,
            //         'success' => false,
            //         'msg_code' => MsgCode::TOO_MANY_REPRESENT[0],
            //         'msg' => MsgCode::TOO_MANY_REPRESENT[1],
            //     ]);
            // }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_RENTER[0],
                'msg' => MsgCode::INVALID_LIST_RENTER[1]
            ]);
        }

        if (!is_array($request->images)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_IMAGES[0],
                'msg' => MsgCode::INVALID_IMAGES[1],
            ]);
        }

        if (count($request->images) > 0) {
            foreach ($request->images as $imageItem) {
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

        if ($request->mo_services != null && !is_array($request->mo_services)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_SERVICE[0],
                'msg' => MsgCode::INVALID_LIST_SERVICE[1],
            ]);
        }

        $motelHasContract = DB::table('contracts')->join('motels', 'contracts.motel_id', '=', 'motels.id')
            ->where([
                ['contracts.user_id', $request->user->id],
                ['contracts.motel_id', $request->motel_id]
            ])
            ->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED])
            ->whereIn('motels.status', [StatusMotelDefineCode::MOTEL_HIRED, StatusMotelDefineCode::MOTEL_EMPTY]);

        if ($motelHasContract->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::MOTEL_HAS_CONTRACT[0],
                'msg' => MsgCode::MOTEL_HAS_CONTRACT[1],
            ]);
        }

        if ($request->furniture != null && !is_array($request->furniture)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_FURNITURE[0],
                'msg' => MsgCode::INVALID_LIST_FURNITURE[1],
            ]);
        }

        if ($request->furniture != null && is_array($request->furniture)) {
            foreach ($request->furniture as $furnitureItem) {
                if (empty($furnitureItem['name'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_FURNITURE_IS_REQUIRED[0],
                        'msg' => MsgCode::NAME_FURNITURE_IS_REQUIRED[1],
                    ]);
                }
                if (empty($furnitureItem['quantity'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::QUANTITY_IS_REQUIRED[0],
                        'msg' => MsgCode::QUANTITY_IS_REQUIRED[1],
                    ]);
                }

                if ($furnitureItem['quantity'] <= 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_QUANTITY[0],
                        'msg' => MsgCode::INVALID_QUANTITY[1],
                    ]);
                }

                if (!is_array($furnitureItem['images'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_IMAGES[0],
                        'msg' => MsgCode::INVALID_IMAGES[1],
                    ]);
                }
            }
        }

        DB::beginTransaction();
        try {
            $contract_created = Contract::create([
                "user_id" => $motelExists->user_id,
                "user_maker_id" => $request->user->id,
                "tower_id"  => $motelExists->tower_id,
                "motel_id"  => $request->motel_id,
                "rent_from" =>   $request->rent_from,
                "rent_to" =>   $request->rent_to,
                "furniture" =>  $request->furniture != null ? json_encode($request->furniture) : json_encode([]),
                "mo_services" =>   json_encode($request->mo_services),
                "images" =>   json_encode($request->images),
                "pay_start" =>   $request->pay_start,
                "payment_space" =>   $request->payment_space,
                "money"  =>   $request->money,
                "deposit_money" =>   $request->deposit_money,
                "note" =>   $request->note,
                "status" =>   StatusContractDefineCode::PROGRESSING,
                "cmnd_back_image_url" => $request->cmnd_back_image_url,
                "cmnd_front_image_url" => $request->cmnd_front_image_url,
                "cmnd_number" => $request->cmnd_number,
            ]);

            if ($request->list_renter != null && is_array($request->list_renter)) {
                // if (count($request->list_renter) > 1) {
                foreach ($request->list_renter as $renter) {
                    $renterExists = DB::table('renters')->where([['user_id', $motelExists->user_id], ['phone_number', $renter['phone_number']]]);
                    if (!$renterExists->exists() && !empty($renter['phone_number'])) {
                        Renter::create([
                            'name' => $renter['name'] ?? '',
                            'phone_number' => $renter['phone_number'],
                            'user_id' => $motelExists->user_id,
                            'email' => $renter['email'] ?? null,
                            'cmnd_number' => $renter['cmnd_number'] ?? null,
                            'cmnd_front_image_url' => $renter['cmnd_front_image_url'] ?? null,
                            'cmnd_back_image_url' => $renter['cmnd_back_image_url'] ?? null,
                            'address' => $renter['address'] ?? null,
                            'has_contract' => true
                        ]);

                        // PushNotificationUserJob::dispatch(
                        //     $request->user->id,
                        //     'Có người thuê mới đã được thêm',
                        //     'Người thuê ' . $request->user->name . ', tên phòng ' . $motelExists->motel_name . ' cần xác nhận ',
                        //     TypeFCM::NEW_RENTER,
                        //     NotiUserDefineCode::USER_IS_ADMIN,
                        //     $contract_created->id
                        // );
                    }
                    // $userContractExist = DB::table('user_contracts')
                    //     ->where([
                    //         ['user_id', $request->user->id],
                    //         ['renter_phone_number', $renter['phone_number']],
                    //         ['motel_id', $request->motel_id],
                    //     ]);
                    // if (!$userContractExist->exists()) {
                    UserContract::create([
                        "user_id" => $motelExists->user_id,
                        "motel_id"  => $request->motel_id,
                        "contract_id"  => $contract_created->id,
                        "renter_phone_number" => $renter['phone_number'],
                        "is_represent" => isset($renter['is_represent']) && $renter['is_represent'] ? true : false
                    ]);
                    // }
                    //     }
                    // } else {
                    //     $renterExists = DB::table('renters')->where([['user_id', $request->user->id], ['phone_number', $renter['phone_number']]]);
                    //     if (!$renterExists->exists()) {
                    //         DB::table('renters')->insert([
                    //             'phone_number' => $request->list_renter[0]['phone_number'],
                    //             'user_id' => $request->user->id
                    //         ]);
                    //     }
                    //     UserContract::create([
                    //         "user_id" => $request->user->id,
                    //         "motel_id"  => $request->motel_id,
                    //         "contract_id"  => $contract_created->id,
                    //         "renter_phone_number" => $request->list_renter[0]['phone_number'],
                    //         "is_represent" => true
                    //     ]);
                    // }
                }
            }

            $serviceClose = ServiceClose::create([
                'motel_id' => $contract_created->motel_id,
                'close_date' => DatetimeUtils::getNow()
            ]);

            $motelExists->update([
                "money"  =>   $request->money,
                "deposit" =>   $request->deposit_money,
                // 'has_contract' => true
            ]);

            foreach ($request->mo_services as $serviceItem) {
                ServiceCloseChild::create([
                    'service_close_id' => $serviceClose->id,
                    'service_name' => $serviceItem['service_name'] ?: null,
                    'service_icon' => $serviceItem['service_icon'] ?? null,
                    'service_unit' => $serviceItem['service_unit'] ?? 0,
                    'service_charge' => $serviceItem['service_charge'] ?? 0,
                    "images" => json_encode($serviceItem['images'] ?? []),
                    "type_unit" => $serviceItem['type_unit'],
                    'quantity' => $serviceItem['quantity'],
                    'old_quantity' => isset($serviceItem['old_quantity']) ? $serviceItem['old_quantity'] : 0,
                    'total' => 0
                ]);
            }

            Bill::create([
                'contract_id' => $contract_created->id,
                'service_close_id' => $serviceClose->id,
                'status' => StatusBillDefineCode::PROGRESSING,
                'date_payment' => DatetimeUtils::getNow(),
                'type_bill' => 0,
                'content' => '',
                'note' => null,
                'total_money_motel' => 0,
                'total_money_service' => 0,
                'total_final' => 0,
                'content' => null,
                "images"  => json_encode([]),
                "is_init"  => true,
                'discount' => 0
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        //setup notification
        if ($request->list_renter != null) {
            $listRenter = DB::table('users')
                // ->join('user_device_tokens', 'users.id', '=', 'user_device_tokens.user_id')
                ->whereIn('phone_number', array_column($request->list_renter, 'phone_number'))
                ->get();

            foreach ($listRenter as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    'Bạn có hợp đồng thuê phòng mới',
                    'Bạn có hợp đồng từ chủ nhà ' . $request->user->name . ', tên phòng ' . $motelExists->motel_name . ' cần xác nhận ',
                    TypeFCM::NEW_CONTRACT,
                    NotiUserDefineCode::USER_NORMAL,
                    $contract_created->id
                );
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Contract::distinct()->where('id', '=',   $contract_created->id)->first(),
        ]);
    }


    /**
     * 
     * Thong tin 1 hợp đồng
     * 
     */
    public function getOne(Request $request)
    {
        $contract_id = request("contract_id");

        $contractExists = Contract::where(
            'id',
            $contract_id
        )
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
                    // $query->where('contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    //     $supporterManageTowerIds = DB::table('supporter_manage_towers')
                    //         ->where('supporter_id', $request->user->id)
                    //         // ->when($request->manage_supporter_id != null, function ($sq) use ($request) {
                    //         //     $sq->where('id', $request->manage_supporter_id);
                    //         // })
                    //         ->pluck('id');

                    //     $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                    //         ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                    //         ->pluck('motels.id');
                    //     // if (filter_var($request->is_have_supporter, FILTER_VALIDATE_BOOLEAN) == true) {
                    //     $q->whereIn('contracts.motel_id', $motelIds);
                    //     // } else if (filter_var($request->is_have_supporter, FILTER_VALIDATE_BOOLEAN) == false && filter_var($request->is_supporter_manage, FILTER_VALIDATE_BOOLEAN) == false) {
                    //     //     $q->whereNotIn('contracts.motel_id', $motelIds);
                    //     // }
                    // });
                }
            })
            ->first();

        if ($contractExists == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1],
            ]);
        }

        // $contractExists->list_renter = UserContract::where(
        //     'contract_id',
        //     $contract_id
        // )->where('user_id', $request->user->id)->get();

        return ResponseUtils::json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $contractExists,
        ]);
    }

    /**
     * Cập nhật 1 hợp đồng
     * 
     * @bodyParam motel_id int phòng cho thuê
     * @bodyParam money double tiền phòng
     * @bodyParam rent_from time thuê từ ngày
     * @bodyParam rent_to time thuê đến ngày 
     * @bodyParam pay_start time ngày bắt đầu tính tiền
     * @bodyParam payment_space int kỳ thanh toán
     * @bodyParam deposit_money double tiền đặt cọc
     * @bodyParam images list danh sách ảnh
     * @bodyParam mo_services list danh sách danh sách dịch vụ
     * @bodyParam list_renter list danh sách người thuê
     * 
     */
    public function update(Request $request, $id)
    {
        $renterPhoneNumberRepresent = null;
        $groupRenterPhone = [];
        $oldOwnContractUserId = null;

        if ($request->motel_id != null) {
            //check motel exists
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
                ->distinct()
                ->pluck('motels.id');
            $motelExists = Motel::where('id', $request->motel_id)
                ->where('user_id', $request->user->id)->orWhereIn('motels.id', $motelIds)
                ->first();

            if ($motelExists == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_MOTEL_EXISTS[1],
                ]);
            }
        }

        $contractExists = Contract::where('id', $id)
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
            ->first();

        if ($contractExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1],
            ]);
        }

        $oldOwnContractUserId = $contractExists->user_id;

        if ($request->list_renter != null && is_array($request->list_renter)) {
            $countRepresent = 0;
            $listNumberPhone = array_unique(array_column($request->list_renter, 'phone_number'));

            if (count(array_unique(array_column($request->list_renter, 'phone_number'))) != count($request->list_renter)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::DOUBLE_PHONE_NUMBER_IN_LIST_RENTER[0],
                    'msg' => MsgCode::DOUBLE_PHONE_NUMBER_IN_LIST_RENTER[1],
                ]);
            }

            foreach ($request->list_renter as $renter) {
                if (isset($renter['is_represent']) && $renter['is_represent']) {
                    $renterPhoneNumberRepresent = $renter['phone_number'];
                    $countRepresent += 1;
                    // if (!$renter['is_represent']) {
                    //     return ResponseUtils::json([
                    //         'code' => Response::HTTP_BAD_REQUEST,
                    //         'success' => false,
                    //         'msg_code' => MsgCode::INVALID_REPRESENT[0],
                    //         'msg' => MsgCode::INVALID_REPRESENT[1],
                    //     ]);
                    // }
                }
            }

            $renterExist = DB::table('user_contracts')->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->whereIn('renter_phone_number', $listNumberPhone)
                ->where('user_contracts.contract_id', '<>', $id)
                ->select('user_contracts.*', 'contracts.status')
                ->get();

            // foreach ($renterExist as $renterItem) {
            //     if ($renterItem->status == StatusContractDefineCode::PROGRESSING) {
            //         return ResponseUtils::json([
            //             'code' => Response::HTTP_BAD_REQUEST,
            //             'success' => false,
            //             'msg_code' => MsgCode::RENTER_ALREADY_EXISTS[0],
            //             'msg' => MsgCode::RENTER_ALREADY_EXISTS[1],
            //         ]);
            //     } else if ($renterItem->status == StatusContractDefineCode::PROGRESSING) {
            //         return ResponseUtils::json([
            //             'code' => Response::HTTP_BAD_REQUEST,
            //             'success' => false,
            //             'msg_code' => MsgCode::RENTER_ALREADY_EXISTS[0],
            //             'msg' => MsgCode::RENTER_ALREADY_EXISTS[1],
            //         ]);
            //     }
            // }

            if (!$renterExist) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::RENTER_ALREADY_EXISTS[0],
                    'msg' => MsgCode::RENTER_ALREADY_EXISTS[1],
                ]);
            }

            if ($countRepresent > 1) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::TOO_MANY_REPRESENT[0],
                    'msg' => MsgCode::TOO_MANY_REPRESENT[1],
                ]);
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_RENTER[0],
                'msg' => MsgCode::INVALID_LIST_RENTER[1]
            ]);
        }

        // if ($contractExists->status == StatusContractDefineCode::COMPLETED) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::UNABLE_CHANGE_INFO_WHILE_CONTRACT_IS_ACTIVE[0],
        //         'msg' => MsgCode::UNABLE_CHANGE_INFO_WHILE_CONTRACT_IS_ACTIVE[1],
        //     ]);
        // }

        if (!is_array($request->mo_services)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_SERVICE[0],
                'msg' => MsgCode::INVALID_LIST_SERVICE[1],
            ]);
        }

        if ($request->images != null || !empty($request->images)) {
            if (!is_array($request->images)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_IMAGES[0],
                    'msg' => MsgCode::INVALID_IMAGES[1],
                ]);
            }

            if (count($request->images) > 0) {
                foreach ($request->images as $imageItem) {
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
        }

        if ($request->furniture != null && !is_array($request->furniture)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIST_FURNITURE[0],
                'msg' => MsgCode::INVALID_LIST_FURNITURE[1],
            ]);
        }

        if ($request->furniture != null && is_array($request->furniture)) {
            foreach ($request->furniture as $furnitureItem) {
                if (empty($furnitureItem['name'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_FURNITURE_IS_REQUIRED[0],
                        'msg' => MsgCode::NAME_FURNITURE_IS_REQUIRED[1],
                    ]);
                }
                if (empty($furnitureItem['quantity'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::QUANTITY_IS_REQUIRED[0],
                        'msg' => MsgCode::QUANTITY_IS_REQUIRED[1],
                    ]);
                }

                if ($furnitureItem['quantity'] <= 0) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_QUANTITY[0],
                        'msg' => MsgCode::INVALID_QUANTITY[1],
                    ]);
                }

                if (!is_array($furnitureItem['images'])) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_IMAGES[0],
                        'msg' => MsgCode::INVALID_IMAGES[1],
                    ]);
                }
            }
        }

        // if (StatusContractDefineCode::getStatusContractCode($request->status, false) == null) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_CONTRACT_STATUS[0],
        //         'msg' => MsgCode::INVALID_CONTRACT_STATUS[1],
        //     ]);
        // }

        $contractExists->update([
            "user_id" =>  $contractExists->user_id,
            "motel_id"  => $request->motel_id,
            "rent_from" =>   $request->rent_from,
            "rent_to" =>   $request->rent_to,
            "pay_start" =>   $request->pay_start,
            "furniture" =>  json_encode($request->furniture),
            "mo_services" =>   json_encode($request->mo_services),
            "images" =>   json_encode($request->images),
            "payment_space" =>   $request->payment_space,
            "money"  =>   $request->money,
            "deposit_money" =>   $request->deposit_money,
            "cmnd_back_image_url" => $request->cmnd_back_image_url,
            "cmnd_front_image_url" => $request->cmnd_front_image_url,
            "cmnd_number" => $request->cmnd_number,
            "note" => $request->note,
            "status" => $contractExists->status ?? 0,
        ]);

        //update price motel
        Motel::where([
            ['id', $request->motel_id],
            ['user_id', $request->user->id]
        ])->update([
            "money"  =>   $request->money,
            "deposit" =>   $request->deposit_money,
            // 'has_contract' => true
        ]);

        if ($request->list_renter != null && is_array($request->list_renter)) {
            //remove renter contract old
            DB::table('user_contracts')->where([['user_id', $contractExists->user_id], ['contract_id', $contractExists->id]])->delete();
            $groupRenterPhone = DB::table('user_contracts')->where([['user_id', $contractExists->user_id], ['contract_id', $contractExists->id]])->pluck('renter_phone_number')->toArray();
            $groupRenterPhone = array_unique(array_merge($groupRenterPhone, array_column($request->list_renter, 'phone_number')));

            foreach ($request->list_renter as $renter) {
                $renterExists = DB::table('renters')->where([['user_id', $contractExists->user_id], ['phone_number', $renter['phone_number']]]);
                $userExist = DB::table('users')->where('phone_number', $renter['phone_number'])->first();
                if (!$renterExists->exists()) {
                    Renter::create([
                        'phone_number' => $renter['phone_number'],
                        'user_id' => $contractExists->user_id,
                        'has_contract' => true
                    ]);
                }

                UserContract::create([
                    "user_id" => $contractExists->user_id,
                    "motel_id"  => $request->motel_id,
                    "contract_id"  => $contractExists->id,
                    "renter_phone_number" => $renter['phone_number'],
                    "is_represent" => isset($renter['is_represent']) && $renter['is_represent'] ? true : false
                ]);
            }
        }

        // set again list renter to list origin
        // $listRenter = DB::table('renters')
        //     ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
        //     ->where([
        //         ['user_contracts.user_id', $request->user->id],
        //         ['user_contracts.contract_id', $contractExists->id],
        //         ['renters.user_id', $request->user->id]
        //     ])
        //     ->distinct('renters.phone_number')
        //     ->select('renters.name', 'renters.phone_number', 'renters.email', 'renters.cmnd_number', 'renters.cmnd_front_image_url', 'renters.cmnd_back_image_url', 'renters.address', 'user_contracts.is_represent')
        //     ->get()->toArray();
        // foreach ($listRenter as $renter) {
        //     $renter->is_represent = $renter->is_represent ? true : false;
        // }
        // $contractExists->update(['list_renter' => json_encode($listRenter)]);
        //setup notification
        if ($request->list_renter != null && $request->user->id == $contractExists->user_id) {
            $listRenter = DB::table('users')
                // ->join('user_device_tokens', 'users.id', '=', 'user_device_tokens.user_id')
                ->whereIn('phone_number', $groupRenterPhone)
                ->get();
            $motelExist = DB::table('motels')->where('id', $contractExists->motel_id)->first();

            foreach ($listRenter as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    'Hợp đồng có sự thay đổi',
                    'Hợp đồng phòng ' . $motelExist->motel_name . ', có sự thay đổi',
                    TypeFCM::CONTRACT_HAS_CHANGED,
                    NotiUserDefineCode::USER_NORMAL,
                    $contractExists->id
                );
            }
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $contractExists,
        ]);
    }

    /**
     * Xóa 1 hợp đồng
     */
    public function delete(Request $request)
    {

        $contract_id = request("contract_id");

        $contractExists = Contract::where(
            'id',
            $contract_id
        )->where(function ($query) use ($request) {
            if ($request->user->is_admin != true) {
                $query->where('contracts.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->pluck('motels.id');
                    $q->whereIn('contracts.motel_id', $motelIds);
                });;
            }
        })
            ->first();

        if ($contractExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1]
            ]);
        }

        if ($contractExists->status == StatusContractDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_REMOVE_CONTRACT_ACTIVE[0],
                'msg' => MsgCode::UNABLE_REMOVE_CONTRACT_ACTIVE[1]
            ]);
        }

        // update motel if motel isn't in contract
        if (DB::table('contracts')->where('motel_id', $contractExists->motel_id)->doesntExist()) {
            DB::table('motels')->where('id', $contractExists->motel_id)->update(['has_contract' => false]);
        }

        $idDeleted = $contractExists->id;
        $contractExists->delete();


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }

    /**
     * UpdateStatusContracts
     */
    public function updateStatusContract(Request $request, $id)
    {
        $towerExist = null;
        $now = Helper::getTimeNowDateTime();

        $contractExists = Contract::where([
            ['id', $id],
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
            })->first();

        $motelExist = Motel::where([
            ['motels.id', $contractExists->motel_id],
            ['motels.user_id', $contractExists->user_id]
        ])->first();

        if ($contractExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1]
            ]);
        }

        if (StatusContractDefineCode::getStatusContractCode($request->status, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1]
            ]);
        }

        if ($contractExists->status == $request->status) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::CONTRACT_HAS_BEEN_COMPLETED[0],
                'msg' => MsgCode::CONTRACT_HAS_BEEN_COMPLETED[1]
            ]);
        }

        if (!$request->status == StatusContractDefineCode::COMPLETED && !$request->status == StatusContractDefineCode::UNCONFIRMED_BY_HOST) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::INVALID_CONTRACT_STATUS[0],
                'msg' => MsgCode::INVALID_CONTRACT_STATUS[1]
            ]);
        }

        $contractExists->update([
            'status' => $request->status,
            'note' => $request->note
        ]);

        DB::table('users')
            // ->join('user_device_tokens', 'users.id', '=', 'user_device_tokens.user_id')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->where([
                ['user_contracts.contract_id', $id],
                ['user_contracts.user_id', $contractExists->user_id]
            ])
            ->update([
                'account_rank' => AccountRankDefineCode::LOYAL
            ]);

        // push notification
        $listRenter = DB::table('users')
            // ->join('user_device_tokens', 'users.id', '=', 'user_device_tokens.user_id')
            ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
            ->where([
                ['user_contracts.contract_id', $id],
                ['user_contracts.user_id', $contractExists->user_id]
            ])
            ->select('users.*')
            ->get();

        $motelExist = Motel::where([
            ['motels.id', $contractExists->motel_id],
        ])->first();

        if ($contractExists->tower_id != null) {
            $towerExist = Tower::where([
                ['towers.id', $contractExists->tower_id],
                ['towers.user_id', $contractExists->user_id]
            ])->first();
        }

        if ($request->status == StatusContractDefineCode::COMPLETED) {
            foreach ($listRenter as $renter) {
                // handle noti
                NotificationUserJob::dispatch(
                    $renter->id,
                    'Hợp đồng đã được xác nhận',
                    'Bạn đã thuê phòng ' . $motelExist->motel_name . ($towerExist != null ? ', ' . $towerExist->tower_name : '') . ' của chủ nhà ' . $request->user->name,
                    TypeFCM::CONFIRM_CONTRACT,
                    NotiUserDefineCode::USER_NORMAL,
                    $contractExists->id
                );

                // handle send sms
                try {
                    if (PhoneUtils::isNumberPhoneValid($renter->phone_number)) {
                        TingTingSmsController::sendSmsContractRenter(
                            $renter->phone_number,
                            $motelExist->motel_name,
                            $towerExist != null ? $towerExist->tower_name : "",
                            $request->user->name,
                            $request->user->id,
                            $renter->id
                        );
                    }
                } catch (\Throwable $th) {
                }


                // Thêm box chat cho người thuê
                if (!DB::table('person_chats')->where([['user_id', $renter->id], ['to_user_id', $request->user->id]])->exists()) {
                    PersonChats::create([
                        'user_id' => $renter->id,
                        'to_user_id' =>  $request->user->id,
                        "last_mess" => "",
                        'seen' => false,
                        'lasted_at' => $now->format('y-m-d H:i:s'),
                    ]);
                }
            }

            // handle commission collaborator
            $this->handleCommissionCollaborator($contractExists);

            // handle motel
            if ($motelExist != null) {
                $motelExist->update([
                    'has_post' => true,
                    'used_at' => Helper::getTimeNowDateTime(),
                    'has_contract' => true,
                    'status' => StatusMotelDefineCode::MOTEL_HIRED
                ]);
            }

            // handle list renter
            DB::table('renters')
                ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                ->where('user_contracts.contract_id', $contractExists->id)
                ->update([
                    'renters.has_contract' => true,
                    'renters.motel_name' => $motelExist->motel_name,
                    'renters.name_motel_expected' => $motelExist->motel_name,
                    'renters.name_tower_expected' => $towerExist != null ? $towerExist->tower_name : '',
                ]);

            DB::table('mo_posts')
                ->where([
                    ['mo_posts.motel_id', $contractExists->motel_id],
                    ['mo_posts.user_id', $contractExists->user_id]
                ])
                ->update([
                    'available_motel' => StatusMoPostDefineCode::MOTEL_HIRED,
                    'status' => StatusMoPostDefineCode::CANCEL
                ]);

            PotentialUser::join('users', 'potential_users.user_guest_id', '=', 'users.id')
                ->join('renters', 'users.phone_number', '=', 'renters.phone_number')
                ->where([
                    ['potential_users.user_host_id', $request->user->id],
                ])
                ->whereIn('renters.phone_number', $listRenter->pluck('phone_number'))
                ->update([
                    "potential_users.status" => StatusHistoryPotentialUserDefineCode::HIDDEN,
                ]);

            $contractExists->update([
                'deposit_payment_date' => Helper::getTimeNowDateTime()
            ]);
        } else if ($request->status == StatusContractDefineCode::UNCONFIRMED_BY_HOST) {
            foreach ($listRenter as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    'Hợp đồng không được xác nhận',
                    'Hợp đồng phòng ' . $motelExist->motel_name . ' không được xác nhận',
                    TypeFCM::UNCONFIRMED_CONTRACT_BY_HOST,
                    NotiUserDefineCode::USER_NORMAL,
                    $contractExists->id
                );
            }
        } else if ($request->status == StatusContractDefineCode::TERMINATION) {
            DB::table('motels')
                ->where([
                    ['motels.id', $contractExists->motel_id],
                    ['motels.user_id', $contractExists->user_id]
                ])
                ->update([
                    'status' => StatusMotelDefineCode::MOTEL_EMPTY,
                    'used_at' => Helper::getTimeNowDateTime(),
                    'has_contract' => false,
                ]);

            DB::table('mo_posts')
                ->where([
                    ['mo_posts.motel_id', $contractExists->motel_id],
                    ['mo_posts.user_id', $contractExists->user_id]
                ])
                ->update([
                    'available_motel' => StatusMoPostDefineCode::MOTEL_AVAILABLE,
                    'status' => StatusMoPostDefineCode::PROCESSING
                ]);

            DB::table('bills')
                ->join('contracts', 'bills.contract_id', '=', 'contracts.id')
                ->where([
                    ['contracts.user_id', $contractExists->user_id],
                    ['contracts.motel_id', $contractExists->motel_id]
                ])
                ->update([
                    'bills.status' => StatusBillDefineCode::CANCEL_BY_HOST
                ]);

            PotentialUser::join('users', 'potential_users.user_guest_id', '=', 'users.id')
                ->join('renters', 'users.phone_number', '=', 'renters.phone_number')
                ->where([
                    ['potential_users.user_host_id', $request->user->id],
                ])
                ->whereIn('renters.phone_number', $listRenter->pluck('phone_number'))
                ->update([
                    "potential_users.is_has_contract" => false,
                    "potential_users.status" => StatusHistoryPotentialUserDefineCode::COMPLETED,
                ]);

            // add potential not exists
            $userNotPotential = DB::table('renters')
                ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                ->join('users', 'renters.phone_number', '=', 'users.phone_number')
                ->where('user_contracts.contract_id', $contractExists->id)
                ->select('renters.*', 'users.id as user_guest_id')
                ->get();

            $newPotentialHasRented = [];
            foreach ($userNotPotential as $user) {
                if (!DB::table('potential_users')->where([['user_guest_id', $user->user_guest_id], ['user_host_id', $contractExists->user_id]])->exists()) {
                    array_push($newPotentialHasRented, [
                        'user_host_id' => $contractExists->user_id,
                        'user_guest_id' => $user->user_guest_id,
                        'title' => null,
                        'type_from' => null,
                        'status' => 2, // status potential oke  
                        'name_tower' => null,
                        'name_motel' => null,
                        'value_reference' => null,
                        'time_interact' => null,
                        'is_has_contract' => false,
                        'is_renter' => true,
                        'created_at' => $now->format('y-m-d H:i:s'),
                        'updated_at' => $now->format('y-m-d H:i:s')
                    ]);
                }
            }

            PotentialUser::insert($newPotentialHasRented);

            foreach ($listRenter as $user) {
                NotificationUserJob::dispatch(
                    $user->id,
                    'Hợp đồng đã chấm dứt',
                    'Hợp đồng phòng ' . $motelExist->motel_name . ' đã chấm dứt',
                    TypeFCM::TERMINATION_CONTRACT,
                    NotiUserDefineCode::USER_NORMAL,
                    $contractExists->id
                );

                foreach ($listRenter as $renter) {
                    // handle noti
                    NotificationUserJob::dispatch(
                        $renter->id,
                        'Hợp đồng đã được xác nhận',
                        'Bạn đã thuê phòng ' . $motelExist->motel_name . ($towerExist != null ? ', ' . $towerExist->tower_name : '') . ' của chủ nhà ' . $request->user->name,
                        TypeFCM::CONFIRM_CONTRACT,
                        NotiUserDefineCode::USER_NORMAL,
                        $contractExists->id
                    );

                    // handle send sms
                    if (PhoneUtils::isNumberPhoneValid($renter->phone_number)) {
                        TingTingSmsController::sendSmsContractRenter(
                            $renter->phone_number,
                            $motelExist->motel_name,
                            $towerExist != null ? $towerExist->tower_name : "",
                            $request->user->name,
                            $request->user->id,
                            $renter->id
                        );
                    }
                }
            }

            DB::table('renters')
                ->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                ->where('user_contracts.contract_id', $contractExists->id)
                ->update([
                    'renters.has_contract' => false,
                    'renters.is_hidden' => true,
                ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $contractExists,
        ]);
    }

    static public function handleCommissionCollaborator($contract)
    {
        if ($contract != null) {
            $eWalletHistoryCollaborator = null;
            $userRenterRepresent = DB::table('users') // đây là thằng đại diện hợp đồng, cũng là thằng sử dụng mã giới thiệu
                ->join('user_contracts', 'users.phone_number', '=', 'user_contracts.renter_phone_number')
                ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                ->where([
                    ['contracts.id', $contract->id],
                    ['contracts.status', StatusContractDefineCode::COMPLETED],
                    ['user_contracts.is_represent', true]
                ])
                ->select('users.*')
                ->first();

            if ($userRenterRepresent == null) {
                return;
            }

            $checkExistPreviousCollaborator = CollaboratorReferMotel::where([
                ['user_referral_id', $userRenterRepresent->id],
                ['motel_id', $contract->motel_id]
            ])->count();

            if ($checkExistPreviousCollaborator > 0) {
                return;
            }

            $userReferralCodeExist = DB::table('users') // đây là thằng chia sẻ mã giới thiệu
                ->whereNotNull('self_referral_code')
                ->where('self_referral_code', $userRenterRepresent->referral_code)
                ->first();

            if ($userReferralCodeExist == null || $userReferralCodeExist->account_rank != AccountRankDefineCode::LOYAL) {
                return;
            }

            $checkContractPreviousMonth = DB::table('collaborator_refer_motels')
                ->where([
                    ['user_referral_id', $userRenterRepresent->id]
                ])
                ->when($userReferralCodeExist != null, function ($query) use ($userReferralCodeExist) {
                    $query->where('user_id', $userReferralCodeExist->id);
                })
                ->first();

            $moPostExist = DB::table('mo_posts')
                ->where('motel_id', $contract->motel_id)
                ->first();

            if ($moPostExist == null || $moPostExist->status != StatusMoPostDefineCode::COMPLETED) {
                return;
            }

            if ($moPostExist != null) {
                $collaboratorReferMotelCreate = CollaboratorReferMotel::create([
                    'user_id' => $userReferralCodeExist != null ? $userReferralCodeExist->id : null,
                    'user_referral_id' =>  $moPostExist->money_commission_user != 0 || $moPostExist->money_commission_user != null ? $userRenterRepresent->id : null,
                    'contract_id' => $contract->id,
                    'motel_id' => $contract->motel_id,
                    'status' => StatusCollaboratorReferMotelDefineCode::PROGRESSING,
                    'date_refer_success' => Helper::getTimeNowDateTime(),
                    'money_commission_user' => $userReferralCodeExist == null || $moPostExist->status != StatusMoPostDefineCode::COMPLETED ? 0 : $moPostExist->money_commission_user,
                    'money_commission_admin' => $moPostExist->money_commission_admin,
                    'images_host_paid' => json_encode([]),
                    'first_receive_commission' => $checkContractPreviousMonth == null && $userReferralCodeExist != null && $userRenterRepresent->referral_code != null && $moPostExist->status == StatusMoPostDefineCode::COMPLETED && $moPostExist->money_commission_user >= 0 ? true : false
                ]);

                NotificationUserJob::dispatch(
                    $contract->user_id,
                    'Thông báo hoa hồng',
                    'Thanh toán hoa hồng cho admin',
                    TypeFCM::PAYMENT_COLLABORATOR_MANAGE,
                    NotiUserDefineCode::USER_IS_HOST,
                    $collaboratorReferMotelCreate->id
                );
                NotificationAdminJob::dispatch(
                    null,
                    'Thông báo hoa hồng',
                    'Xác nhận hoa hồng cho cộng tác viên',
                    TypeFCM::CONFIRM_COMMISSION_COLLABORATOR_FOR_ADMIN,
                    NotiUserDefineCode::USER_IS_ADMIN,
                    $collaboratorReferMotelCreate->id
                );
            }
        } else {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::ERROR[0],
                'msg' => MsgCode::ERROR[1]
            ]);
        }
    }
}
