<?php

namespace App\Http\Resources\Api\WalletTransaction;

use App\Helper\StatusWithdrawalDefineCode;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'withdraw_money' => $this->withdraw_money,
            'account_number' => $this->account_number,
            'bank_account_holder_name' => $this->bank_account_holder_name,
            'bank_name' => $this->bank_name,
            'withdraw_trading_code' => $this->withdraw_trading_code,
            'withdraw_date_time' => $this->withdraw_date_time,
            'withdraw_content' => $this->withdraw_content,
            'status' => StatusWithdrawalDefineCode::getStatusWithdrawalCode($this->status)
        ];
    }
}
