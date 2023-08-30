<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);  // after request completion get the controller response her

        if ($response instanceof \Illuminate\Http\JsonResponse) {


            try {
                $array =  (array)$response;
                $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";


                if ($array["\x00*\x00statusCode"]  != 200 && $array["\x00*\x00statusCode"]  != 201) {
                    $object = (object) ['request' => $request->all(), 'response' => $response->getContent()];

                    Log::notice($response, [
                        'url' =>  $actual_link,
                        'request' => $request->all(),
                        'response' => $response->getContent()
                    ]);
                }
            } catch (Exception $e) {
            }
        }
        return $response;
    }
}
