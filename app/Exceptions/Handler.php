<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // $this->render();
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpExceptionInterface) {
            if ($exception->getStatusCode() == 429) {
                return response()->json([
                    'code' => 429,
                    'success' => false,
                    'msg_code' => 'ERROR',
                    'msg' => 'Bạn đã gửi quá nhiều yêu cầu trong 1 phút ' . $request->url(),
                ], 429);
            }
        }
        return parent::render($request, $exception);
    }
}
