<?php

namespace App\Helper;


class ParamUtils
{
    static public function checkLimit($limit)
    {
        if ($limit >= 600 || $limit < 1) {
            return false;
        }
        return true;
    }
}
