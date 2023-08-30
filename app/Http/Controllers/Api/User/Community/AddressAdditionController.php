<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Helper\ParamUtils;
use App\Helper\Place;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\AddressAddition;
use App\Models\MsgCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AddressAdditionController extends Controller
{
    /**
     * 
     * Danh cách địa chỉ bổ sung
     * 
     * @queryParam limit int Số item trong page
     * @queryParam search string tìm kiếm (title)
     */
    public function getAll(Request $request)
    {
        $sortBy = $request->sort_by ?? 'created_at';
        $limit = $request->limit ?: 20;
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
        $search = $request->search;

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        $addressAdditions = AddressAddition::where('address_additions.user_id', $request->user->id)
            ->when($request->province != null, function ($query) {
                $query->where('province', request('province'));
            })
            ->when($request->district != null, function ($query) {
                $query->where('district', request('district'));
            })
            ->when($request->wards != null, function ($query) {
                $query->where('wards', request('wards'));
            })
            ->paginate($limit);


        // $custom = collect(
        //     MotelUtils::getBadgesMotels($request->user->id)
        // );
        // $data = $custom->merge($addressAdditions);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $addressAdditions,
        ]);
    }


    /**
     * 
     * Thêm 1 địa chỉ bổ sung
     * 
     * @bodyParam province
     * @bodyParam district
     * @bodyParam wards
     * @bodyParam address_detail 
     * @bodyParam note 
     * 
     */
    public function create(Request $request)
    {
        // check place
        if (Place::getNameProvince($request->province) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PROVINCE[0],
                'msg' => MsgCode::INVALID_PROVINCE[1],
            ], 400);
        }

        if (Place::getNameDistrict($request->district) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ], 400);
        }

        if (Place::getNameWards($request->wards) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_WARDS[0],
                'msg' => MsgCode::INVALID_WARDS[1],
            ], 400);
        }

        $addressAdditionExists = DB::table('address_additions')->where([
            ['user_id', $request->user->id],
            ['province', $request->province],
            ['district', $request->district],
            ['wards', $request->wards],
            ['address_detail', $request->address_detail],
        ])->exists();

        if ($addressAdditionExists) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ADDRESS_ADDITION_EXISTS[0],
                'msg' => MsgCode::ADDRESS_ADDITION_EXISTS[1],
            ], 400);
        }


        $addressAddition = AddressAddition::create([
            "user_id" => $request->user->id,
            "province" => $request->province,
            "district" => $request->district,
            "wards" => $request->wards,
            "address_detail" => $request->address_detail,
            "note" => $request->note,
        ]);


        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $addressAddition,
        ]);
    }


    /**
     * Thong tin 1 địa chỉ bổ sung
     * 
     */
    public function getOne(Request $request)
    {

        $address_addition_id = request("address_addition_id");

        $addressAddition = AddressAddition::where('id', $address_addition_id)
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($addressAddition == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADDRESS_ADDITION_EXISTS[0],
                'msg' => MsgCode::NO_ADDRESS_ADDITION_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $addressAddition,
        ]);
    }

    /**
     * Cập nhật 1 địa chỉ bổ sung
     * 
     * @bodyParam 
     * @bodyParam 
     */
    public function update(Request $request)
    {
        $address_addition_id = request("address_addition_id");

        $addressAddition = AddressAddition::where('id', $address_addition_id)
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($addressAddition == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADDRESS_ADDITION_EXISTS[0],
                'msg' => MsgCode::NO_ADDRESS_ADDITION_EXISTS[1]
            ]);
        }

        if (isset($request->province) && Place::getNameProvince($request->province) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_PROVINCE[0],
                'msg' => MsgCode::INVALID_PROVINCE[1],
            ], 400);
        }

        if (isset($request->district) && Place::getNameDistrict($request->district) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DISTRICT[0],
                'msg' => MsgCode::INVALID_DISTRICT[1],
            ], 400);
        }

        if (isset($request->wards) && Place::getNameWards($request->wards) == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_WARDS[0],
                'msg' => MsgCode::INVALID_WARDS[1],
            ], 400);
        }

        $addressAdditionExists = DB::table('address_additions')->where([
            ['id', '<>', $address_addition_id],
            ['province', $request->province],
            ['district', $request->district],
            ['wards', $request->wards],
            ['address_detail', $request->address_detail],
        ])->exists();

        if ($addressAdditionExists) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::ADDRESS_ADDITION_EXISTS[0],
                'msg' => MsgCode::ADDRESS_ADDITION_EXISTS[1],
            ], 400);
        }


        DB::beginTransaction();
        try {
            $addressAddition->update(
                [
                    "province" => $request->province,
                    "district" => $request->district,
                    "wards" => $request->wards,
                    "address_detail" => $request->address_detail,
                    "note" => $request->note,
                ]
            );


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
            'data' => $addressAddition,
        ]);
    }


    /**
     * Xóa 1 địa chỉ bổ sung
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {

        $address_addition_id = request("address_addition_id");
        $addressAddition = AddressAddition::where([
            ['id', $address_addition_id]
        ])
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($addressAddition == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_ADDRESS_ADDITION_EXISTS[0],
                'msg' => MsgCode::NO_ADDRESS_ADDITION_EXISTS[1]
            ]);
        }

        $idDeleted = $addressAddition->id;
        $addressAddition->delete();

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ]);
    }
}
