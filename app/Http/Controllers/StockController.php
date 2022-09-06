<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected $mainModel;

    /**
     * ProductController constructor.
     * @param Stock $stock
     */
    public function __construct(Stock $stock)
    {
        $this->mainModel = $stock;
    }

    /** Fetches all Products Where Status Available and Low on Stock
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllStockRecords(): \Illuminate\Http\JsonResponse
    {
        if(! $this->mainModel->fetchAllStockRecord())
            return JsonAPIResponse::sendErrorResponse("No Records Found");

        return JsonAPIResponse::sendSuccessResponse("All Products Still in Stock", $this->mainModel->fetchPOSProducts());
    }
}
