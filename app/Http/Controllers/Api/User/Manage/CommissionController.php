<?php

namespace App\Http\Controllers\Api\User\Manage;

use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\ResponseUtils;
use App\Helper\StatusCollaboratorReferMotelDefineCode;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationAdminJob;
use App\Models\CollaboratorReferMotel;
use App\Models\MsgCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommissionController extends Controller
{
    public function getAllCommission(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';


        if ($dateFrom != null || $dateTo != null) {
            $dateFrom = Helper::createAndValidateFormatDate($dateFrom, 'Y-m-d');
            $dateTo = Helper::createAndValidateFormatDate($dateTo, 'Y-m-d');

            if ($dateFrom != false && $request->date_from != null) {
                $dateFrom = $dateFrom->format('Y-m-d') . ' 00:00:01';
            } else {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }

            if ($dateTo != false && $request->date_to != null) {
                $dateTo = $dateTo->format('Y-m-d') . ' 23:59:59';
            } else {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_DATETIME_QUERY[0],
                    'msg' => MsgCode::INVALID_DATETIME_QUERY[1],
                ]);
            }
        }

        $listCollaboratorReferMotel = CollaboratorReferMotel::join('contracts', 'collaborator_refer_motels.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.user_id', $request->user->id],
            ])
            ->when(isset($request->status), function ($query) use ($request) {
                $query->where('collaborator_refer_motels.status',  $request->status);
            })
            ->select('collaborator_refer_motels.*')
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('collaborator_refer_motels.date_refer_success', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('collaborator_refer_motels.date_refer_success', '<=', $dateTo);
            })
            ->when(!empty($sortBy) && CollaboratorReferMotel::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
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
            'data' =>  $listCollaboratorReferMotel
        ]);
    }

    public function getOne(Request $request)
    {
        $collaboratorExist = CollaboratorReferMotel::join('contracts', 'collaborator_refer_motels.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.user_id', $request->user->id],
            ])
            ->where('collaborator_refer_motels.id', $request->commission_collaborator_id)
            ->select('collaborator_refer_motels.*')
            ->first();

        if ($collaboratorExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $collaboratorExist,
        ]);
    }

    public function update(Request $request)
    {
        $imagesHostPaid = [];
        $collaboratorExist = CollaboratorReferMotel::join('contracts', 'collaborator_refer_motels.contract_id', '=', 'contracts.id')
            ->where([
                ['contracts.user_id', $request->user->id],
            ])
            ->where('collaborator_refer_motels.id', $request->commission_collaborator_id)
            ->select('collaborator_refer_motels.*')
            ->first();

        if ($collaboratorExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_COLLABORATOR_EXISTS[0],
                'msg' => MsgCode::NO_COLLABORATOR_EXISTS[1]
            ]);
        }

        if ($collaboratorExist->status == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::COLLABORATOR_COMMISSION_COMPLETED[0],
                'msg' => MsgCode::COLLABORATOR_COMMISSION_COMPLETED[1]
            ]);
        }

        if ($collaboratorExist->status == StatusCollaboratorReferMotelDefineCode::CANCEL) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[0],
                'msg' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[1]
            ]);
        }

        if ($collaboratorExist->status == StatusCollaboratorReferMotelDefineCode::WAIT_CONFIRM) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::COLLABORATOR_COMMISSION_WAIT_CONFIRM[0],
                'msg' => MsgCode::COLLABORATOR_COMMISSION_WAIT_CONFIRM[1]
            ]);
        }

        if ($request->status == StatusCollaboratorReferMotelDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_STATUS_COLLABORATOR[0],
                'msg' => MsgCode::INVALID_STATUS_COLLABORATOR[1]
            ]);
        }

        // if ($request->status == !StatusCollaboratorReferMotelDefineCode::PROGRESSING) {
        //     return ResponseUtils::json([
        //         'code' => Response::HTTP_BAD_REQUEST,
        //         'success' => false,
        //         'msg_code' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[0],
        //         'msg' => MsgCode::COLLABORATOR_COMMISSION_CANCEL[1]
        //     ]);
        // }

        if ($request->images_host_paid != null) {
            if (!is_array($request->images_host_paid)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_IMAGES[0],
                    'msg' => MsgCode::INVALID_IMAGES[1],
                ]);
            }
            $imagesHostPaid = $request->images_host_paid;
        }

        $collaboratorExist->update([
            'status' => $request->status,
            'images_host_paid' => json_encode($imagesHostPaid),
        ]);

        if ($request->status == StatusCollaboratorReferMotelDefineCode::WAIT_CONFIRM) {
            NotificationAdminJob::dispatch(
                null,
                'Thông báo hoa hồng',
                'Xác nhận thanh toán tiền hoa hồng của chủ nhà',
                TypeFCM::CONFIRM_COMMISSION_PAYMENT_HOST_FOR_ADMIN,
                NotiUserDefineCode::USER_IS_ADMIN,
                $collaboratorExist->id
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $collaboratorExist,
        ]);
    }
}
