<?php

namespace App\Http\Middleware;

use App\Services\JsonAPIResponse;
use Closure;
use Illuminate\Http\Request;

class SetHeader
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
        if(!$request->hasHeader('accept') || $request->header('accept') !== "application/json")
            return JsonAPIResponse::sendErrorResponse('The Header is required to have {Accept: Application/json}');

        return $next($request);
    }
}
