<?php

namespace App\Helper;

use App\Models\User;

class UserUtils
{

    static function isUser()
    {
        $user = request('user', $default = null);

        if ($user != null) {
            return true;
        }
        return false;
    }
}
