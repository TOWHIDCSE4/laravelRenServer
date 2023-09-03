<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helper\Helper;
use App\Helper\ParamUtils;
use App\Helper\PhoneUtils;
use App\Helper\RenterType;
use App\Helper\ResponseUtils;
use App\Helper\StatusContractDefineCode;
use App\Helper\StatusHistoryPotentialUserDefineCode;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\PotentialUser;
use App\Models\Renter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RenterController extends Controller
{

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
    public function createMaster(Request $request)
    {
        $renterExist = null;
        $isUserExist = false;
        $isEmailExist = false;
        $userRenter = null;

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


        $renterExist = Renter::where([['phone_number', $request->phone_number], ['user_id', $request->user->id]])->first();


        if ($renterExist != null && $renterExist->is_hidden == false) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::RENTER_ALREADY_EXISTS[0],
                'msg' => MsgCode::RENTER_ALREADY_EXISTS[1],
            ]);
        } else if ($renterExist != null && $renterExist->is_hidden == true) {
            $renterExist->update([
                "name" => $request->name ?: $renterExist->name,
                "phone_number" => $request->phone_number ?: $renterExist->phone_number,
                "email" => $request->email ?: $renterExist->email,
                "cmnd_number" => $request->cmnd_number ?: $renterExist->cmnd_number,
                "cmnd_front_image_url" => $request->cmnd_front_image_url ?: $renterExist->cmnd_front_image_url,
                "cmnd_back_image_url" => $request->cmnd_back_image_url ?: $renterExist->cmnd_back_image_url,
                "address" => $request->address ?: $renterExist->address,
                "image_url" => ($request->image_url == null ? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg" : $request->image_url) ?: $renterExist->image_url,
                "address" => $request->address ?: $renterExist->address,
                "is_hidden" => false,
                "date_of_birth" => $request->date_of_birth ?: $renterExist->date_of_birth,
                "date_range" => $request->date_range ?: $renterExist->date_range,
                "sex" => $request->sex ?: $renterExist->sex,
                "job" => $request->job ?: $renterExist->job,
            ]);

            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $renterExist,
            ]);
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
                "user_id" => $request->user->id,
                "name" => $request->name,
                "phone_number" => $request->phone_number,
                "email" => $request->email,
                "cmnd_number" => $request->cmnd_number,
                "cmnd_front_image_url" => $request->cmnd_front_image_url,
                "cmnd_back_image_url" => $request->cmnd_back_image_url,
                "image_url" => $request->image_url ?? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg",
                "address" => $request->address,
                "date_of_birth" => $request->date_of_birth,
                "date_range" => $request->date_range,
                "sex" => $request->sex,
                "job" => $request->job,
                "type" => RenterType::MASTER,
            ]);


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


        $renterExist = Renter::where([['phone_number', $request->phone_number], ['user_id', $request->user->id]])->first();


        if ($renterExist != null && $renterExist->is_hidden == false) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::RENTER_ALREADY_EXISTS[0],
                'msg' => MsgCode::RENTER_ALREADY_EXISTS[1],
            ]);
        } else if ($renterExist != null && $renterExist->is_hidden == true) {
            $renterExist->update([
                "name" => $request->name ?: $renterExist->name,
                "phone_number" => $request->phone_number ?: $renterExist->phone_number,
                "email" => $request->email ?: $renterExist->email,
                "cmnd_number" => $request->cmnd_number ?: $renterExist->cmnd_number,
                "cmnd_front_image_url" => $request->cmnd_front_image_url ?: $renterExist->cmnd_front_image_url,
                "cmnd_back_image_url" => $request->cmnd_back_image_url ?: $renterExist->cmnd_back_image_url,
                "address" => $request->address ?: $renterExist->address,
                "image_url" => ($request->image_url == null ? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg" : $request->image_url) ?: $renterExist->image_url,
                "address" => $request->address ?: $renterExist->address,
                "is_hidden" => false,
                "date_of_birth" => $request->date_of_birth ?: $renterExist->date_of_birth,
                "date_range" => $request->date_range ?: $renterExist->date_range,
                "sex" => $request->sex ?: $renterExist->sex,
                "job" => $request->job ?: $renterExist->job,
            ]);

            return ResponseUtils::json([
                'code' => Response::HTTP_OK,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => $renterExist,
            ]);
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
                "user_id" => $request->user->id,
                "name" => $request->name,
                "phone_number" => $request->phone_number,
                "email" => $request->email,
                "cmnd_number" => $request->cmnd_number,
                "cmnd_front_image_url" => $request->cmnd_front_image_url,
                "cmnd_back_image_url" => $request->cmnd_back_image_url,
                "image_url" => $request->image_url ?? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg",
                "address" => $request->address,
                "date_of_birth" => $request->date_of_birth,
                "date_range" => $request->date_range,
                "sex" => $request->sex,
                "job" => $request->job,
                "type" => RenterType::RENTER,
            ]);


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
     *
     * Danh sách user
     *
     * @bodyParam name string tên người dùng
     * @bodyParam number_phone số điện thoại người dùng
     * @bodyParam email string email
     * @bodyParam date_from datetime ngày bắt đầu
     * @bodyParam date_to datetime ngày kết thúc
     * @bodyParam descending boolean sắp xếp theo (default true)
     * @bodyParam sort_by string sắp xếp theo tên cột (account_rank, name)
     * @bodyParam limit int Số lượng bản ghi sẽ lấy
     *
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $limit = $request->limit ?: 20;
        $descending = filter_var(($request->descending ?: true), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($dateFrom != null || $dateTo != null) {
            if ($dateFrom != null && $dateTo != null) {
                if (
                    !Helper::validateDate($dateFrom, 'Y-m-d')
                    || !Helper::validateDate($dateTo, 'Y-m-d')
                ) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateFrom != null) {
                if (!Helper::validateDate($dateFrom, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
            if ($dateTo != null) {
                if (!Helper::validateDate($dateTo, 'Y-m-d')) {
                    return ResponseUtils::json([
                        'code' => 400,
                        'success' => false,
                        'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                        'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                    ]);
                }
            }
        }
        // Renter::whereIn('phone_number', DB::table('user_contracts')->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
        //     ->where('contracts.status', '<>', 2)
        //     ->pluck('renter_phone_number'))
        //     ->update([
        //         'has_contract' => false
        //     ]);

        $renters = Renter::when(isset($request->is_rented), function ($query) use ($request) {
            $hasContract = isset($request->is_rented) ? filter_var($request->is_rented, FILTER_VALIDATE_BOOLEAN) : null;
            if ($hasContract) {
                $query->join('user_contracts', 'renters.phone_number', '=', 'user_contracts.renter_phone_number');
                $query->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id');
                $query->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED, StatusContractDefineCode::PROGRESSING, StatusContractDefineCode::WAITING_CONFIRM]);
            } else {
                $query->whereNotIn('renters.phone_number', DB::table('user_contracts')
                    ->join('contracts', 'user_contracts.contract_id', '=', 'contracts.id')
                    ->whereIn('contracts.status', [StatusContractDefineCode::COMPLETED])
                    ->when($request->user_id != null, function ($query) use ($request) {
                        $query->where('contracts.user_id', $request->user_id);
                    })
                    ->distinct()
                    ->pluck('renter_phone_number')
                    ->toArray());
                $query->where('renters.is_hidden', false);
            }
            $query->where('renters.has_contract', $hasContract);
        })
            ->select('renters.*')
            ->when(isset($request->user_id), function ($query) use ($request) {
                $query->where('renters.user_id', $request->user_id);
            })
            ->when($dateFrom != null || $dateTo != null, function ($query) use ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            })
            ->distinct()
            ->when($request->search != null, function ($query) {
                $query->search(request('search'));
            })
            ->when(Renter::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $renters
        ]);
    }


    /**
     * Thong tin 1 user
     *
     */
    public function getOne(Request $request)
    {
        $renter_id = request("renter_id");

        $renterExist = Renter::where('id', $renter_id)
            ->first();

        if ($renterExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
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
            'data' => $renterExist
        ]);
    }

    /**
     *
     * Cập nhật renter
     *
     * @bodyParam host_rank
     *
     */
    public function update(Request $request)
    {
        $renter_id = request("renter_id");
        $renterExist = Renter::where('id', $renter_id)->first();

        if ($renterExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_RENTER_EXISTS[0],
                'msg' => MsgCode::NO_RENTER_EXISTS[1],
            ]);
        }

        $renterExist->update(
            [
                'name' => $request->name ?? $renterExist->name,
                'email' => $request->email ?? $renterExist->email,
                'phone_number' => $request->phone_number ?? $renterExist->phone_number,
                'address' => $request->address ?? $renterExist->address,
                'cmnd_number' => $request->cmnd_number ?? $renterExist->cmnd_number,
                'cmnd_front_image_url' => $request->cmnd_front_image_url ?? $renterExist->cmnd_front_image_url,
                'cmnd_back_image_url' => $request->cmnd_back_image_url ?? $renterExist->cmnd_back_image_url,
                "image_url" => ($request->image_url == null ? "https://data3gohomy.ikitech.vn/api/SHImages/ODLzIFikis1681367637.jpg" : $request->image_url) ?? $renterExist->image_url,
                "name_tower_expected" => $request->name_tower_expected ?? $renterExist->name_tower_expected,
                "name_motel_expected" => $request->name_motel_expected ?? $renterExist->name_motel_expected,
                "price_expected" => $request->price_expected ?? $renterExist->price_expected ?? 0,
                "deposit_expected" => $request->deposit_expected ?? $renterExist->deposit_expected ?? 0,
                "estimate_rental_period" => $request->estimate_rental_period ?? $renterExist->estimate_rental_period,
                "estimate_rental_date" => $request->estimate_rental_date ?? $renterExist->estimate_rental_date
            ]
        );

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $renterExist
        ]);
    }

    /**
     *
     * Xóa ng thuê
     *
     *
     */
    public function delete(Request $request)
    {
        $renter_id = request("renter_id");

        $renterExist = Renter::where(
            'id',
            $renter_id
        )
            ->first();

        if ($renterExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_RENTER_EXISTS[0],
                'msg' => MsgCode::NO_RENTER_EXISTS[1],
            ]);
        }

        $hasRenterContractActive = DB::table('contracts')
            ->join('user_contracts', 'contracts.id', 'user_contracts.contract_id')
            ->where([
                ['contracts.user_id', $request->user->id],
                ['user_contracts.renter_phone_number', $renterExist->phone_number],
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

        $renterExist->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ]);
    }

    // KYC GET RENTER BY USER ID
    public function getRenterByUserid($userId)
    {
        $renters = Renter::query()
            ->where(["type" => RenterType::RENTER, "user_id" => $userId])
            ->first();

        if (!$renters) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_RENTER_EXISTS[0],
                'msg' => MsgCode::NO_RENTER_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $renters
        ]);
    }

    // KYC GET MASTER BY USER ID
    public function getMasterByUserid($userId)
    {
        $masters = Renter::query()
            ->where(["type" => RenterType::MASTER, "user_id" => $userId])
            ->first();

        if (!$masters) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_MASTER_EXISTS[0],
                'msg' => MsgCode::NO_MASTER_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $masters
        ]);
    }
}
