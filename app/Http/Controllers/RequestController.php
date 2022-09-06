<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    protected $mainModel;

    /**
     * ProductController constructor.
     * @param RequestModel $requestModel
     */
    public function __construct(RequestModel $requestModel)
    {
        $this->mainModel = $requestModel;
    }

    /** Fetches all Products Where Status Available and Low on Stock
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllStockRecords(): \Illuminate\Http\JsonResponse
    {

        if(! $this->mainModel->getAllRequestedByUserId())
            return JsonAPIResponse::sendErrorResponse("No Records Found");

        return JsonAPIResponse::sendSuccessResponse("All Products Still in Stock", $this->mainModel->fetchPOSProducts());
    }
}
