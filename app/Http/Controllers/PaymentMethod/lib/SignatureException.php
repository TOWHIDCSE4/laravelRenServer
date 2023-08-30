<?php

namespace App\Http\Controllers\PaymentMethod\lib;

use Exception;

class SignatureException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}