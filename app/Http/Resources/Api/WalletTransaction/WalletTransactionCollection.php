<?php

namespace App\Http\Resources\Api\WalletTransaction;

use App\Traits\ApiResourceTrait;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use JsonSerializable;

class WalletTransactionCollection extends ResourceCollection
{
    use ApiResourceTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $collection = ['data' => $this->collection];

        if ($this->resource instanceof AbstractPaginator) {
            $paginated = $this->resource->toArray();
            $collection['links'] = $this->paginationLinks($paginated);
            $collection['meta'] = $this->meta($paginated);
        }

        return $collection;
    }
}
