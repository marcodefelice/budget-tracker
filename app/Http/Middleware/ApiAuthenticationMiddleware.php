<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $header = $request->header();
        $apiKey = "";
        if(array_key_exists("x-api-key",$header)) {
            $apiKey = $header['x-api-key'][0];
        }

        if($apiKey !== env("API_KEY")) {
            return response("Request not authorized",401);
        }

        return $next($request);
    }
}
