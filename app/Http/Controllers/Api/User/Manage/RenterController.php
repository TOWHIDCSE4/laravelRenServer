<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\PhoneUtils;
use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\StatusRenterDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationUserJob;
use App\Models\Motel;
use App\Models\Renter;
use App\Models\MsgCode;
use App\Models\PotentialUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * @group User/Quản lý/Người thuê
 */

class RenterController extends Controller
{
    /**
     * 
     * Danh cách người thuê
     * 
     * @queryParam renter_status int (0 chưa có phòng,2 đang thuê )
     * @queryParam number_phone string 
     * @queryParam has_motel boolean 
     * @queryParam motel_name string
     * 
     */
    public function getAll(Request $request)
    {
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $renterHasMotel = filter_var($request->has_contract, FILTER_VALIDATE_BOOLEAN);
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $dateFrom = null;
        $dateTo = null;

        if (isset($request->renter_status) && StatusRenterDefineCode::getStatusMotelCode($request->renter_status) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_STATUS_EXISTS[0],
                'msg' => MsgCode::NO_STATUS_EXISTS[1],
            ]);
        }

        if ($request->from_time != null || $request->to_time != null) {
            $dateFrom = Helper::parseAndValidateDateTime($request->from_time);
            $dateTo = Helper::parseAndValidateDateTime($request->to_time);
            if ($dateFrom == false || $dateTo == false) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ], 400);
            }

            if ($dateTo->gt($dateFrom)) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ], 400);
            }
        }

        $listRenter = Renter::sortByRelevance(true)
            ->where([
                ['renters.phone_number', '<>', $request->user->phone_number],
            ])
            ->where(function ($query) use ($request) {
                $query->where('renters.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->distinct()
                        ->pluck('motels.id');
                    // $renterIds = Renter::join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                    //     ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    //     ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                    //     ->whereIn('contracts.motel_id', $motelIds)->pluck('renters.id')->toArray();
                    $renterIds = Renter::whereIn('renters.motel_id', $motelIds)
                        ->pluck('renters.id')->toArray();
                    $q->whereIn('renters.id', $renterIds);
                });
            })
            ->when(isset($request->renter_status), function ($query) use ($request) {
                if ($request->renter_status == StatusRenterDefineCode::RENTER_HAS_RENTED_MOTEL) {
                    $query->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number');
                    $query->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id');
                    $query->where([
                        ['contracts.status', StatusContractDefineCode::COMPLETED],
                        ['contracts.user_id', $request->user->id],
                        ['renters.has_contract', true],
                    ]);
                } else if ($request->renter_status == StatusRenterDefineCode::RENTER_HAS_NOT_MOTEL) {
                    $renHasContract = DB::table('user_contracts')
                        ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                        ->where([
                            ['contracts.user_id', $request->user->id],
                        ])
                        ->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED, StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM])
                        ->distinct()
                        ->pluck('renter_phone_number')
                        ->toArray();
                    $query->whereNotIn('phone_number', $renHasContract);
                    $query->where('renters.has_contract', false);
                    $query->where('renters.is_hidden', false);
                }
                $query->select('renters.*');
                // $query->distinct('renters.id');
            })
            ->when(isset($request->phone_number), function ($query) use ($request) {
                $query->where('renters.phone_number',  $request->phone_number);
            })
            // ->when(isset($renterHasMotel), function ($query) use ($renterHasMotel) {
            //     $query->where('renters.has_contract', $renterHasMotel);
            // })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('renters.created_at', '<=', $dateTo);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('renters.created_at', '>=', $dateFrom);
            })
            ->when(!empty($sortBy) && Renter::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->when(!empty($request->search), function ($query) use ($request) {
                $query->search($request->search);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $listRenter
        ]);
    }

    /**
     * 
     * Thêm 1 người thuê
     * 
     * @bodyParam name string tên người đại diện
     * @bodyParam phone_number string tên người đại diện
     * @bodyParam email string tên người đại diện
     * @bodyParam cmnd_number string tên người đại diện
     * @bodyParam cmnd_front_image_url string tên người đại diện
     * @bodyParam cmnd_back_image_url string tên người đại diện
     * @bodyParam address string tên người đại diện
     * 
     */
    public function create(Request $request)
    {
        $renterExist = null;
        $isUserExist = false;
        $isEmailExist = false;
        $userRenter = null;
        $tower = null;

        if ($request->phone_number == null || empty($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_IS_REQUIRED[0],
                'msg' => MsgCode::PHONE_NUMBER_IS_REQUIRED[1],
            ]);
        }

        if (!PhoneUtils::isNumberPhoneValid($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        $motel = DB::table('motels')->where('id', $request->motel_id)->first();
        if ($motel == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }

        if ($request->tower_id != null) {
            $tower = DB::table('towers')->where('id', $request->tower_id)->first();
            if ($tower == null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::NO_TOWER_EXISTS[0],
                    'msg' => MsgCode::NO_TOWER_EXISTS[1],
                ]);
            }
        }

        $renterExist = Renter::where([['phone_number', $request->phone_number], ['user_id', $motel != null ? $motel->user_id : $request->user->id]])->first();


        if ($renterExist != null && $renterExist->is_hidden == false) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::RENTER_ALREADY_EXISTS[0],
                'msg' => MsgCode::RENTER_ALREADY_EXISTS[1],
            ]);
        } else if ($renterExist != null && $renterExist->is_hidden == true) {
            $renterExist->update([
                "motel_id" => $request->motel_id ?: $renterExist->motel_id,
                "tower_id" => $request->tower_id ?: $renterExist->tower_id,
                "name" => $request->name ?: $renterExist->name,
                "phone_number" => $request->phone_number ?: $renterExist->phone_number,
                "email" => $request->email ?: $renterExist->email,
                "cmnd_number" => $request->cmnd_number ?: $renterExist->cmnd_number,
                "cmnd_front_image_url"  => $request->cmnd_front_image_url ?: $renterExist->cmnd_front_image_url,
                "cmnd_back_image_url" => $request->cmnd_back_image_url ?: $renterExist->cmnd_back_image_url,
                "address" => $request->address ?: $renterExist->address,
                "image_url" => ($request->image_url == null ? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg" : $request->image_url) ?: $renterExist->image_url,
                "address" => $request->address ?: $renterExist->address,
                "motel_name" => $request->motel_name ?: $renterExist->motel_name,
                "name_tower_expected" => $request->name_tower_expected ?: $renterExist->name_tower_expected,
                "name_motel_expected" => $request->name_motel_expected ?: $renterExist->name_motel_expected,
                "price_expected" => $request->price_expected ?: $renterExist->price_expected ?: $renterExist->price_expected,
                "deposit_expected" => $request->deposit_expected ?: $renterExist->deposit_expected ?: $renterExist->deposit_expected,
                "estimate_rental_period" => $request->estimate_rental_period ?: $renterExist->estimate_rental_period,
                "estimate_rental_date" => $request->estimate_rental_date ?: $renterExist->estimate_rental_date,
                "is_hidden" => false,
            ]);

            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $renterExist,
            ]);
        }

        if ($request->user->is_host) {

            if ($request->user->phone_number == $request->phone_number) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::UNABLE_RENT_YOUR_MOTEL[0],
                    'msg' => MsgCode::UNABLE_RENT_YOUR_MOTEL[1],
                ]);
            }
        }


        // if (isset($request->email)) {
        //     $isEmailExist = DB::table('renters')->where([['email', $request->email], ['user_id', $request->user->id]])->exists();
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::EMAIL_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::EMAIL_ALREADY_EXISTS[1],
        //     ]);
        // }

        // if (DB::table('renters')->where([['cmnd_number', $request->cmnd_number], ['user_id', $request->user->id]])->exists()) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::CODE_CITIZEN_IDENTIFICATION_ALREADY_EXISTS[0],
        //         'msg' => MsgCode::CODE_CITIZEN_IDENTIFICATION_ALREADY_EXISTS[1],
        //     ]);
        // }        
        DB::beginTransaction();
        try {
            $renter_created = Renter::create([
                "user_id" => $motel != null ? $motel->user_id : $request->user->id,
                "tower_id" => $request->tower_id,
                "motel_id" => $request->motel_id,
                "name" => $request->name,
                "phone_number" => $request->phone_number,
                "email" => $request->email,
                "cmnd_number" => $request->cmnd_number,
                "cmnd_front_image_url"  => $request->cmnd_front_image_url,
                "cmnd_back_image_url" => $request->cmnd_back_image_url,
                "image_url" => $request->image_url ?? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg",
                "motel_name" => $request->motel_name,
                "address" => $request->address,
                "name_tower_expected" => $request->name_tower_expected,
                "name_motel_expected" => $request->name_motel_expected,
                "price_expected" => $request->price_expected,
                "deposit_expected" => $request->deposit_expected,
                "estimate_rental_period" => $request->estimate_rental_period,
                "estimate_rental_date" => Carbon::parse($request->estimate_rental_date)
            ]);

            $renterPotential = PotentialUser::join('users', 'potential_users.user_guest_id', '=', 'users.id')
                ->join('renters', 'users.phone_number', '=', 'renters.phone_number')
                ->where([
                    ['renters.phone_number', $renter_created->phone_number],
                    ['potential_users.user_host_id', $request->user->id]
                ])
                ->select('potential_users.*')
                ->first();

            if ($renterPotential != null) {
                $renterPotential->update([
                    'status' => StatusHistoryPotentialUserDefineCode::HIDDEN,
                    "name_tower" => $request->name_tower_expected,
                    "name_motel" => $request->name_motel_expected,
                    "is_renter" => true,
                ]);
                $userRenter = DB::table('users')->where('phone_number', $renter_created->phone_number)->first();
            } else {
                $userRenter = DB::table('users')->where('phone_number', $request->phone_number)->first();
            }
            // $motelExits = Motel::where([
            //     ['id', $request->motel_id],
            //     ['status', StatusMotelDefineCode::MOTEL_EMPTY],
            //     ['has_contract', false],
            // ])->first();

            // if ($motelExits) {
            //     $motelExits->update([
            //         'status' => StatusMotelDefineCode::MOTEL_HIRED
            //     ]);
            // }

            if ($userRenter) {
                NotificationUserJob::dispatch(
                    $userRenter->id,
                    'Bạn đã thuê phòng',
                    'Bạn đã thuê phòng ' . $motel->motel_name . ($tower != null ? (' ' .
                        $tower->tower_name) : null) . ' của chủ nhà ' . $request->user->name,
                    TypeFCM::NEW_RENTER,
                    NotiUserDefineCode::USER_NORMAL,
                    $request->user->id
                );
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $renter_created,
        ]);
    }


    /**
     * Thong tin 1 người thuê
     * 
     */
    public function getOne(Request $request)
    {

        $renter_id = request("renter_id");

        $renterExists = Renter::where([
            ['id', $renter_id],
            // ['renters.user_id', $request->user->id]
        ])
            ->where(function ($query) use ($request) {
                $query->where('renters.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->distinct()
                        ->pluck('motels.id');
                    // $renterIds = Renter::join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                    //     ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    //     ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                    //     ->whereIn('contracts.motel_id', $motelIds)->pluck('renters.id')->toArray();
                    $renterIds = Renter::whereIn('renters.motel_id', $motelIds)
                        ->pluck('renters.id')->toArray();
                    $q->whereIn('renters.id', $renterIds);
                });
            })
            ->first();

        // $renterExists->append('bill', 'contract')->toArray();

        if ($renterExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_RENTER_EXISTS[0],
                'msg' => MsgCode::NO_RENTER_EXISTS[1],
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $renterExists,
        ]);
    }

    /**
     * Cập nhật 1 người thuê
     * 
     * @bodyParam name string tên người đại diện
     * @bodyParam phone_number string tên người đại diện
     * @bodyParam email string tên người đại diện
     * @bodyParam cmnd_number string tên người đại diện
     * @bodyParam cmnd_front_image_url string tên người đại diện
     * @bodyParam cmnd_back_image_url string tên người đại diện
     * @bodyParam address string tên người đại diện
     * 
     */
    public function update(Request $request, $id)
    {
        $renterExists = Renter::where([
            ['id', $id],
        ])
            ->where(function ($query) use ($request) {
                $query->where('renters.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->distinct()
                        ->pluck('motels.id');
                    // $renterIds = Renter::join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                    //     ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    //     ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                    //     ->whereIn('contracts.motel_id', $motelIds)->pluck('renters.id')->toArray();
                    $renterIds = Renter::whereIn('renters.motel_id', $motelIds)
                        ->pluck('renters.id')->toArray();
                    $q->whereIn('renters.id', $renterIds);
                });
            })
            ->first();

        if ($renterExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_RENTER_EXISTS[0],
                'msg' => MsgCode::NO_RENTER_EXISTS[1],
            ]);
        }

        if ($request->phone_number == null || empty($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::PHONE_NUMBER_IS_REQUIRED[0],
                'msg' => MsgCode::PHONE_NUMBER_IS_REQUIRED[1],
            ]);
        }

        if (!PhoneUtils::isNumberPhoneValid($request->phone_number)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PHONE_NUMBER[0],
                'msg' => MsgCode::INVALID_PHONE_NUMBER[1],
            ]);
        }

        if ($request->motel_id != null && !DB::table('motels')->where('id', $request->motel_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1],
            ]);
        }
        if ($request->tower_id != null && !DB::table('towers')->where('id', $request->tower_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TOWER_EXISTS[0],
                'msg' => MsgCode::NO_TOWER_EXISTS[1],
            ]);
        }

        // if (!Helper::validEmail($request->email) || (filter_var($request->email, FILTER_VALIDATE_EMAIL)) ? false : true) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_EMAIL[0],
        //         'msg' => MsgCode::INVALID_EMAIL[1],
        //     ]);
        // }

        // if (!Helper::checkContainSpecialCharacter($request->cmnd_number)) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::INVALID_CODE_CITIZEN_IDENTIFICATION[0],
        //         'msg' => MsgCode::INVALID_CODE_CITIZEN_IDENTIFICATION[1],
        //     ]);
        // }

        // if (trim($request->address) == '') {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::ADDRESS_IS_REQUIRED[0],
        //         'msg' => MsgCode::ADDRESS_IS_REQUIRED[1],
        //     ]);
        // }

        if ($request->phone_number != null) {
            if (DB::table('renters')->where([['phone_number', $request->phone_number], ['renters.phone_number', '<>', $renterExists->phone_number]])->exists()) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[0],
                    'msg' => MsgCode::PHONE_NUMBER_ALREADY_EXISTS[1],
                ]);
            }
        }

        // if ($request->email != null) {
        //     if (DB::table('renters')->where([['email', $request->email], ['user_id', $request->user->id], ['renters.id', '<>', $id]])->exists()) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_BAD_REQUEST,
        //             'success' => false,
        //             'msg_code' => MsgCode::EMAIL_ALREADY_EXISTS[0],
        //             'msg' => MsgCode::EMAIL_ALREADY_EXISTS[1],
        //         ]);
        //     }
        // }

        // if ($request->cmnd_number != null) {
        //     if (DB::table('renters')->where([['cmnd_number', $request->cmnd_number], ['user_id', $request->user->id], ['renters.id', '<>', $id]])->exists()) {
        //         return ResponseUtils::json([
        //             'code' => Response::HTTP_BAD_REQUEST,
        //             'success' => false,
        //             'msg_code' => MsgCode::CODE_CITIZEN_IDENTIFICATION_ALREADY_EXISTS[0],
        //             'msg' => MsgCode::CODE_CITIZEN_IDENTIFICATION_ALREADY_EXISTS[1],
        //         ]);
        //     }
        // }

        $renterExists->update([
            "motel_id" => $request->motel_id ?? $renterExists->motel_id,
            "tower_id" => $request->tower_id ?? $renterExists->tower_id,
            "name" => $request->name ?? $renterExists->name,
            "phone_number" => $request->phone_number ?? $renterExists->phone_number,
            "email" => $request->email ?? $renterExists->email,
            "cmnd_number" => $request->cmnd_number ?? $renterExists->cmnd_number,
            "cmnd_front_image_url"  => $request->cmnd_front_image_url ?? $renterExists->cmnd_front_image_url,
            "cmnd_back_image_url" => $request->cmnd_back_image_url ?? $renterExists->cmnd_back_image_url,
            "address" => $request->address ?? $renterExists->address,
            "image_url" => ($request->image_url == null ? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg" : $request->image_url) ?? $renterExists->image_url,
            "address" => $request->address,
            "motel_name" => $request->motel_name,
            "name_tower_expected" => $request->name_tower_expected,
            "name_motel_expected" => $request->name_motel_expected,
            "price_expected" => $request->price_expected ?? $renterExists->price_expected,
            "deposit_expected" => $request->deposit_expected ?? $renterExists->deposit_expected,
            "estimate_rental_period" => $request->estimate_rental_period,
            "estimate_rental_date" => $request->estimate_rental_date
        ]);


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>   $renterExists
        ]);
    }

    /**
     * Xóa 1 người thuê
     */
    public function delete(Request $request)
    {

        $renter_id = request("renter_id");

        $renterExists = Renter::where([
            ['id', $renter_id],
        ])
            ->where(function ($query) use ($request) {
                $query->where('renters.user_id', $request->user->id)->orWhere(function ($q) use ($request) {
                    $supporterManageTowerIds = DB::table('supporter_manage_towers')
                        ->where('supporter_id', $request->user->id)
                        ->pluck('id');

                    $motelIds = Motel::join('connect_manage_motels', 'motels.id', '=', 'connect_manage_motels.motel_id')
                        ->whereIn('connect_manage_motels.supporter_manage_tower_id', $supporterManageTowerIds)
                        ->distinct()
                        ->pluck('motels.id');
                    // $renterIds = Renter::join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number')
                    //     ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    //     ->where('contracts.status', StatusContractDefineCode::COMPLETED)
                    //     ->whereIn('contracts.motel_id', $motelIds)->pluck('renters.id')->toArray();
                    $renterIds = Renter::whereIn('renters.motel_id', $motelIds)
                        ->pluck('renters.id')->toArray();
                    $q->whereIn('renters.id', $renterIds);
                });
            })
            ->first();

        if ($renterExists == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_RENTER_EXISTS[0],
                'msg' => MsgCode::NO_RENTER_EXISTS[1],
            ]);
        }

        if (!$request->user->is_admin) {
            $hasRenterContractActive = DB::table('contracts')
                ->join('user_contracts', 'contracts.id', 'user_contracts.contract_id')
                ->where([
                    ['contracts.user_id', $renterExists->user_id],
                    ['user_contracts.renter_phone_number', $renterExists->phone_number],
                    ['contracts.status', StatusContractDefineCode::COMPLETED]
                ])->first();

            if ($hasRenterContractActive != null) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::RENTER_HAS_IN_ACTIVE_CONTRACT[0],
                    'msg' => MsgCode::RENTER_HAS_IN_ACTIVE_CONTRACT[1],
                ]);
            }
        }

        // handle potential renter
        $renterPotential = PotentialUser::join('users', 'potential_users.user_guest_id', '=', 'users.id')
            ->join('renters', 'users.phone_number', '=', 'renters.phone_number')
            ->where([
                ['renters.phone_number', $renterExists->phone_number],
                ['potential_users.user_host_id', $renterExists->user_id]
            ])
            ->select('potential_users.*')
            ->first();

        if ($renterPotential != null) {
            $renterPotential->update([
                "potential_users.is_renter" => false,
                "potential_users.is_has_contract" => false,
                "potential_users.status" => StatusHistoryPotentialUserDefineCode::COMPLETED,
            ]);
        }


        // update again motel
        $motelHasNotContract = Motel::join('renters', 'motels.id', '=', 'renters.motel_id')
            ->where([
                ['renters.id', $renter_id],
                ['motels.status', StatusMotelDefineCode::MOTEL_HIRED],
                ['motels.has_contract', false]
            ])
            ->update([
                'motels.status' => StatusMotelDefineCode::MOTEL_EMPTY,
                'motels.has_contract' => false
            ]);

        $idDeleted = $renterExists->id;
        $renterExists->delete();


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
