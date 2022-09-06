<?php

namespace App\Http\Middleware;

use App\Models\OauthAccessToken;
use App\Services\JsonAPIResponse;
use Closure;
use Illuminate\Http\Request;

class SetAuthAccessControl
{
    /**
     * Handle an incoming request.
     * Accepted access parameters
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedGuards = ['staffs', 'admins'];

        if(!$request->hasHeader('authorization'))
            return JsonAPIResponse::sendErrorResponse("Access denied! No Authorization was defined.");

        $guard = $request->guard;
        if(!$guard)
            return JsonAPIResponse::sendErrorResponse("Access denied! Make sure you're passing a guard type.");

        if(!in_array($guard, $allowedGuards))
            return JsonAPIResponse::sendErrorResponse("Your authentication guard is invalid.");

        /**
         * Switch among the guard requested and set the provider
         * accordingly using passport authentication means
         */
        switch ($guard)
        {
            case 'staffs':
                OauthAccessToken::setAuthProvider('staffs');
                break;

            case 'admins':
                OauthAccessToken::setAuthProvider('admins');
                break;
        }

        return $next($request);
    }
}
