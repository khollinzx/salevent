<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\OauthAccessToken;
use App\Models\User;
use App\Services\Helper;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StaffAuthController extends Controller
{
    use AuthenticatesUsers;

    protected $staffModel;
    protected $adminModel;
    protected $pointerModel;

    public function __construct(User $user, Admin $admin)
    {
        $this->staffModel = $user;
        $this->adminModel = $admin;
    }

    public function staffLogin(Request $request){
        $Validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:8',
            'user_type' => 'required|in:staff,admin'
        ]);

        if($Validator->fails())
            return JsonAPIResponse::sendErrorResponse($Validator->errors()->first());

        $guard = $request->user_type;//manager,staff,admin

        $loginData = [
            'username'=> $request->username,
            'password'=> $request->password
        ];

        if(!Auth::guard($guard)->attempt($loginData))
            return JsonAPIResponse::sendErrorResponse('Invalid login credentials.');

        switch ($request->user_type)
        {
            case "staff":
                $this->pointerModel = $this->staffModel;
                break;

            case "admin":
                $this->pointerModel = $this->adminModel;
                break;
        }

        /**
         * Get the User Account and create access token
         */
        $Account = Helper::findByUserAndColumn($this->pointerModel, 'username', $loginData['username']);

        $LoginRecord = OauthAccessToken::createAccessToken($Account, $guard);

        return JsonAPIResponse::sendSuccessResponse('Login succeeded', $LoginRecord);
    }
}
