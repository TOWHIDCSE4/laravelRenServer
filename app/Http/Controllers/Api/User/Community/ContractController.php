<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\Contract;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @group User/Cộng đồng/Hợp đồng
 */

class ContractController extends Controller
{
    /**
     * 
     * Danh cách hợp đồng
     * 
     * @queryParam contract_status int 
     * @queryParam represent_name  string Tên người đại diện thuê
     * @queryParam phone_number string số điện thoại người thuê
     */
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $fromMoney = $request->money_from;
        $toMoney = $request->money_to;
        $typeMoney = $request->type_money;
        $contractStatus = $request->contract_status;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $now = Helper::getTimeNowString();


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

        if ($typeMoney != null) {
            if ($typeMoney != 'money' && $typeMoney != 'deposit') {
                $typeMoney = 'money';
            }
        }

        $updateContactExpiry = Contract::where('status', '!=', 1)->where('rent_to', '<',  $now)->update([
            'status' => 1
        ]);

        $listContracts = Contract::join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
            ->where([
                ['user_contracts.renter_phone_number', $request->user->phone_number]
            ])
            ->when($fromMoney != null && is_numeric($fromMoney), function ($query) use ($fromMoney, $typeMoney) {
                $query->where($typeMoney, '>=', $fromMoney);
            })
            ->when($contractStatus != null, function ($query) use ($contractStatus) {
                if ($contractStatus == StatusContractDefineCode::PROGRESSING) {
                    $query->whereIn('contracts.status', [StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
                    $query->orderBy('contracts.status', 'asc');
                } else {
                    $query->where('contracts.status', $contractStatus);
                }
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
                // $query->search(request('search'));
                $query->join('users', 'contracts.user_id', '=', 'users.id');
                $query->join('motels', 'contracts.motel_id', '=', 'motels.id');
                $query->where(function ($q) {
                    $q->where('users.phone_number', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('motels.phone_number', 'LIKE', '%' . request('search') . '%')
                        // ->orWhere('users.name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('motels.motel_name', 'LIKE', '%' . request('search') . '%');
                });
            })
            ->select('contracts.*')
            ->when($sortBy != null && Schema::hasColumn('contracts', $sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $listContracts,
        ]);
    }


    /**
     * Thong tin 1 hợp đồng
     * 
     */
    public function getOne(Request $request, $id)
    {
        $contractExists = Contract::join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
            ->where([
                ['contracts.id', $id],
                ['user_contracts.renter_phone_number', $request->user->phone_number]
            ])
            ->select('contracts.*');

        if (!$contractExists->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $contractExists->first(),
        ]);
    }

    /**
     * Xác nhận hợp đồng
     * 
     * @bodyParam is_confirmed boolean 
     * 
     */
    public function update(Request $request, $id)
    {
        if (!is_bool($request->is_confirmed)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => true,
                'msg_code' => MsgCode::INVALID_CONTRACT_STATUS[0],
                'msg' => MsgCode::INVALID_CONTRACT_STATUS[1],
            ]);
        }

        $contractExists = Contract::join('user_contracts', 'contracts.id', '=', 'user_contracts.contract_id')
            ->where([
                ['contracts.id', $id],
                ['user_contracts.contract_id', $id],
                ['user_contracts.renter_phone_number', $request->user->phone_number]
            ])
            ->select('contracts.*')
            ->first();

        if ($contractExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_CONTRACT_EXISTS[0],
                'msg' => MsgCode::NO_CONTRACT_EXISTS[1],
            ]);
        }

        if ($contractExists->status == StatusContractDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::CONTRACT_IS_ACTIVE[0],
                'msg' => MsgCode::CONTRACT_IS_ACTIVE[1],
            ]);
        }

        if (isset($request->images_deposit)) {
            if (!is_array($request->images_deposit)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_IMAGES[0],
                    'msg' => MsgCode::INVALID_IMAGES[1],
                ]);
            }

            if (count($request->images_deposit) > 0) {
                foreach ($request->images_deposit as $imageItem) {
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

        $motelExists = DB::table('motels')->join('user_contracts', 'motels.id', '=', 'user_contracts.motel_id')
            ->where([
                ['user_contracts.renter_phone_number', $request->user->phone_number],
                // ['user_id', $contractExists->user_id],
                ['user_contracts.motel_id', $contractExists->motel_id],
                ['user_contracts.contract_id', $contractExists->id]
            ])
            ->select('motels.*')
            ->first();

        // if ($motelExists->status == StatusContractDefineCode::COMPLETED && $motelExists->has_contract != false) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::MOTEL_HAS_CONTRACT[0],
        //         'msg' => MsgCode::MOTEL_HAS_CONTRACT[1],
        //     ]);
        // }

        $contractExists->update([
            "status" =>  $request->is_confirmed ? StatusContractDefineCode::WAITING_CONFIRM : StatusContractDefineCode::PROGRESSING,
            "images_deposit" => $request->images_deposit == null ? $contractExists->images_deposit : json_encode($request->images_deposit),
            "deposit_amount_paid" => $request->deposit_amount_paid,
            "deposit_actual_paid" => $request->deposit_amount_paid,
            // "cmnd_back_image_url" => $request->cmnd_back_image_url,
            // "cmnd_front_image_url" => $request->cmnd_front_image_url,
            // "cmnd_number" => $request->cmnd_number,
        ]);

        // setup notifications
        $motelExist = DB::table('motels')->where('id', $contractExists->motel_id)->first();
        if ($request->is_confirmed == StatusContractDefineCode::WAITING_CONFIRM) {
            NotificationUserJob::dispatch(
                $contractExists->user_id,
                "Xác nhận hợp đồng và tiền cọc",
                'Hợp đồng phòng ' . $motelExist->motel_name . ', đã được xác nhận từ nguời thuê ' . $request->user->name . ' với số tiền cọc là: ' . Helper::currency_money_format($request->deposit_amount_paid),
                TypeFCM::CONFIRM_CONTRACT_AND_DEPOSIT_PAID,
                NotiUserDefineCode::USER_IS_HOST,
                $id
            );
        } else if ($request->is_confirmed == StatusContractDefineCode::PROGRESSING) {
            NotificationUserJob::dispatch(
                $contractExists->user_id,
                "Hợp đồng bị hủy",
                'Hợp đồng phòng ' . $motelExist->motel_name . ', đã bị hủy từ nguời thuê ' . $request->user->name,
                TypeFCM::CONFIRM_CONTRACT,
                NotiUserDefineCode::USER_IS_HOST,
                $id
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $contractExists->first(),
        ]);
    }
}
