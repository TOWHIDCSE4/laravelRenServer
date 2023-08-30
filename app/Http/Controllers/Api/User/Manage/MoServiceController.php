<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\ResponseUtils;
use App\Http\Controllers\Controller;
use App\Models\MoService;
use App\Models\MsgCode;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Helper\ServiceUnitDefineCode;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MoServiceController extends Controller
{
    /**
     * Danh cách dịch vụ
     */
    public function getAll(Request $request)
    {
        $motelId = request('motel_id');

        $data = MoService::where('motel_id', $motelId)->get();

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $data,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }


    /**
     * Thêm 1 dịch vụ
     * 
     * @bodyParam list_service_id int id service_id
     * @bodyParam user_id int id user
     * @bodyParam service_name string tên dịch vụ
     * @bodyParam service_icon string icon dịch vụ
     * @bodyParam service_unit double phí dịch vụ cho mỗi đơn vị
     * @bodyParam service_charge double phí dịch vụ cho mỗi đơn vị
     * @bodyParam type_unit int loại đơn vị sẽ tính phí dựa theo 
     */
    public function create(Request $request)
    {
        $listServiceId = $request->service_id;

        if (ServiceUnitDefineCode::getServiceUnitCode($request->type_unit, false) == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1],
            ]);
        }

        if (!DB::table('motels')->where('id', $request->motel_id)->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
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

        if (is_array($listServiceId) && count($listServiceId) > 0) {
            foreach ($listServiceId as $serviceId) {
                $serviceExist = Service::where('id', $serviceId)->first();
                if (empty($serviceExist)) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_NOT_FOUND,
                        'success' => false,
                        'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                        'msg' => MsgCode::NO_SERVICE_EXISTS[1],
                    ]);
                }

                $moService = MoService::where([['service_name', 'LIKE', '%' . $request->name . '%'], ['motel_id', $request->motel_id]])->first();

                if ($moService != null) {
                    return ResponseUtils::json([
                        'code' => Response::HTTP_NOT_FOUND,
                        'success' => false,
                        'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                        'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
                    ]);
                }

                MoService::create([
                    "motel_id" => $request->motel_id,
                    "service_name"  => $serviceExist->service_name,
                    "service_icon"  => $serviceExist->service_icon,
                    "service_unit"  => $serviceExist->service_unit,
                    "service_charge" => $serviceExist->service_charge,
                    "type_unit" => $request->type_unit,
                    "note" => $request->note,
                ]);
            }
        } else {
            MoService::create([
                "motel_id" => $request->motel_id,
                "service_name"  => $request->service_name,
                "service_icon"  => $request->service_icon,
                "service_unit"  => $request->service_unit,
                "images"  => json_encode($request->images) ?? [],
                "service_charge" => $request->service_charge,
                "type_unit" => $request->type_unit,
                "note" => $request->note
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1]
        ]);
    }


    /**
     * Thong tin 1 dịch vụ
     * 
     */
    public function getOne(Request $request)
    {

        $service_id = request("service_id");

        $motelExists = MoService::where(
            'id',
            $service_id
        )
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($motelExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_EXISTS[1],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => MoService::where('id', '=',   $motelExists->id)->first(),
        ], 200);
    }

    /**
     * Cập nhật 1 dịch vụ
     * 
     * @bodyParam user_id int id user
     * @bodyParam service_name string tên dịch vụ
     * @bodyParam service_icon string icon dịch vụ
     * @bodyParam service_unit double phí dịch vụ cho mỗi đơn vị
     * @bodyParam service_charge double phí dịch vụ cho mỗi đơn vị
     * @bodyParam type_unit int loại đơn vị sẽ tính phí dựa theo 
     * 
     */
    public function update(Request $request)
    {

        $service_id = request("service_id");

        $moServiceExist = MoService::where(
            'id',
            $service_id
        )
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();

        if ($moServiceExist == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_EXISTS[1],
            ], 404);
        }

        $ServiceNameExist = MoService::where([
            ['service_name', 'LIKE', '%' . $request->name . '%'],
            ['id', '<>', $service_id]
        ])
            ->where('motel_id', $moServiceExist->motel_id)
            ->first();
        if ($ServiceNameExist  != null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => true,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ]);
        }

        if (ServiceUnitDefineCode::getServiceUnitCode($request->type_unit, false) == null) {
            return ResponseUtils::json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_TYPE_UNIT_EXISTS[0],
                'msg' => MsgCode::NO_TYPE_UNIT_EXISTS[1],
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

        $moService = MoService::where([
            ['service_name', $request->name],
            ['user_id', $request->user->id],
            ['id', '<>', $moServiceExist->id]
        ])->first();
        if ($moService  != null) {

            return ResponseUtils::json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ]);
        }


        $moServiceExist->update(Helper::removeItemArrayIfNullValue(
            [
                "service_name"  => $request->service_name,
                "service_icon"  => $request->service_icon,
                "service_unit"  => $request->service_unit,
                "service_charge" => $request->service_charge,
                "images" => json_encode($request->images) ?? [],
                "type_unit" => $request->type_unit,
                "note" => $request->note
            ]
        ));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => MoService::where('id', '=',   $moServiceExist->id)->first(),
        ], 200);
    }

    /**
     * Xóa 1 dịch vụ
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {
        $service_id = request("service_id");

        $motelExists = MoService::where(
            'id',
            $service_id
        )
            ->where(function ($query) use ($request) {
                if ($request->user->is_admin != true) {
                    $query->where('user_id', $request->user->id);
                }
            })
            ->first();
        if ($motelExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_SERVICE_EXISTS[0],
                'msg' => MsgCode::NO_SERVICE_EXISTS[1],
            ], 404);
        }

        $idDeleted = $motelExists->id;
        $motelExists->delete();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }
}
