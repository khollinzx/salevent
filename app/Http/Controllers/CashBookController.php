<?php

namespace App\Http\Controllers;

use App\Models\CashBook;
use App\Services\JsonAPIResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CashBookController extends Controller
{
    public function __construct(CashBook $cashBook)
    {
        $this->mainModel = $cashBook;
    }

    /** Create a New Category
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewCashBook(Request $request)
    {
        $userId = $this->getUserId();

        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "amount" => 'required|integer'
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        $todayDate = Carbon::today()->toDateString();

        $checker = $this->mainModel::findCashBook($userId, $todayDate);

        if($checker)
            return JsonAPIResponse::sendErrorResponse("Cash book already opened for today");

        $createCategory = $this->mainModel->initializeNewOpenCashBook($request->amount, $todayDate, $userId);

        return JsonAPIResponse::sendSuccessResponse("A new Cash Book has been created Successfully", $createCategory);
    }

    /** returns logged in user open cash amount using userId and date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentCashBookForUser()
    {
        $userId = $this->getUserId();

        $todayDate = Carbon::today()->toDateString();

        $userCashBook = $this->mainModel->getUserCashBookByUserIdAndDate($userId, $todayDate);

        return JsonAPIResponse::sendSuccessResponse("User Open Cash Book for Today", $userCashBook);
    }

    /** returns list of open cash amount for an admin using date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCashBooks()
    {
        $todayDate = Carbon::today()->toDateString();

        $allCashBook = $this->mainModel->getAllCashBookByDate($todayDate);

        return JsonAPIResponse::sendSuccessResponse("All Users Open Cash Book List for Today", $allCashBook);
    }

    /** returns total open cash amount for an admin using date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalOpenCashBookAmount()
    {
        $todayDate = Carbon::today()->toDateString();

        $sumAmount = $this->mainModel::totalOpenCashBookDisbursedPerDay($todayDate);

        return JsonAPIResponse::sendSuccessResponse("Total Open Cash Book Disbursed for Today", $sumAmount);
    }

//    public function getAllOpenCashBookToday()
//    {
//        $todayDate = Carbon::today()->toDateString();
//
//        $sumAmount = $this->mainModel::totalOpenCashBookPerDay($todayDate);
//
//        return JsonAPIResponse::sendSuccessResponse("Total Open Cash Book for Today", $sumAmount);
//    }

//    /**
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function getSingleUserOpenCashBookByUserIdAndDate()
//    {
//        $userId = $this->getUserId();
//
//        $todayDate = Carbon::today()->toDateString();
//
//        $sumAmount = $this->mainModel::userOpenCashBookByUserIdAndDate($userId, $todayDate);
//
//        return JsonAPIResponse::sendSuccessResponse("User Open Cash Book for Today", $sumAmount);
//    }
//
//    public function getSingleUserOpenCashBookByUserId()
//    {
//        $userId = $this->getUserId();
//
//        $sumAmount = $this->mainModel::userOpenCashBookById($userId);
//
//        return JsonAPIResponse::sendSuccessResponse("User Open Cash Book for Today", $sumAmount);
//    }




}
