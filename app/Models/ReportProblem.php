<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Helper\StatusReportProblemDefineCode;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportProblem extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];
    // protected $hidden = ['created_at', 'updated_at'];
    protected $with = ['User', 'Motel'];
    protected $appends = ['name_status', 'name_severity'];

    protected $searchable = [
        'columns' => [
            'report_problems.reason'
        ]
    ];

    public function User()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function Motel()
    {
        return $this->belongsTo('App\Models\Motel');
    }

    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    public function getNameStatusAttribute()
    {
        return StatusReportProblemDefineCode::getStatusReportCode($this->status, true);
    }

    public function getNameSeverityAttribute()
    {
        return StatusReportProblemDefineCode::getStatusSeverityCode($this->severity, true);
    }
}
