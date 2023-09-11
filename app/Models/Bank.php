<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;
use App\Helper\StatusContractDefineCode;
use App\Models\Base\BaseModel;


class Bank extends Model
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];
}
