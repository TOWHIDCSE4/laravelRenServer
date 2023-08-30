<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemPermission extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'view_badge' => 'boolean',
        'manage_motel' => 'boolean',
        'manage_user' => 'boolean',
        'manage_mo_post' => 'boolean',
        'manage_contract' => 'boolean',
        'manage_renter' => 'boolean',
        'manage_bill' => 'boolean',
        'manage_message' => 'boolean',
        'manage_report_problem' => 'boolean',
        'manage_service' => 'boolean',
        'manage_service_sell' => 'boolean',
        'manage_order_service_sell' => 'boolean',
        'manage_notification' => 'boolean',
        'setting_banner' => 'boolean',
        'setting_contact' => 'boolean',
        'setting_help' => 'boolean',
        'setting_category_help' => 'boolean',
        'manage_motel_consult' => 'boolean',
        'manage_report_statistic' => 'boolean',
        'mange_motel_consult' => 'boolean',
        'all_access' => 'boolean',
        'able_remove' => 'boolean',
        'unable_access' => 'boolean',
        'able_decentralization' => 'boolean',
        'manage_collaborator' => 'boolean',
    ];
}
