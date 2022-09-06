<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function welcome(): string
    {
        return "Welcome to SaleVent ".env("APP_ENV")." API Version 1 destination";
    }

    /**
     * This returns a signed in User Id
     * @return mixed
     */
    public function getUserId()
    {
        return auth()->id();
    }

    public function getUser()
    {
        return auth()->user();
    }
}
