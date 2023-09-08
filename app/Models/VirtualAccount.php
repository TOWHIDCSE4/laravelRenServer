<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    use HasFactory;

    protected  $guarded = [];

    const PAYMENT_PROCESSING = 'processing';
    const PAYMENT_CANCELED = 'canceled';
    const PAYMENT_SUCCESSFUL = 'paid';
    const PAYMENT_FAILED = 'failed';
}
