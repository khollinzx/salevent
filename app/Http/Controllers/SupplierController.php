<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{

    protected $mainModel;

    public function __construct(Supplier $supplier)
    {
        $this->mainModel = $supplier;
    }

    /** Create a New Category
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewSupplier(Request $request)
    {
        $userId = $this->getUserId();

        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => [
                'required',
                Rule::unique('suppliers', 'name')
            ],
            "email" => 'required|email|unique:suppliers,email',
            'phone' => 'required|digits:11',
            'address' => 'required',
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        $fields = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address
        ];

        $createCategory = $this->mainModel->initializeNewSupplier($userId, $fields);

        return JsonAPIResponse::sendSuccessResponse("A new Supplier has been created Successfully", $createCategory);
    }

    /** Fetches all Categories
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllSuppliers(): \Illuminate\Http\JsonResponse
    {
        $fetchAllSuppliers = $this->mainModel::fetchAllSuppliers();

        if(!$fetchAllSuppliers)
            return JsonAPIResponse::sendErrorResponse("No Records Found");

        return JsonAPIResponse::sendSuccessResponse("All Suppliers", $fetchAllSuppliers);
    }

    /** Fetch a Category by Id
     * @param int $supplierId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupplierById(int $supplierId)
    {
        $supplier = $this->mainModel::findById($supplierId);

        if(!$supplier)
            return JsonAPIResponse::sendErrorResponse('No Record Found');

        return JsonAPIResponse::sendSuccessResponse("Supplier Details", $supplier);
    }

    /** Updates a Category by Id
     * @param Request $request
     * @param int $supplierId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSupplierById(Request $request, int $supplierId)
    {
        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => 'required',
            "email" => 'required',
            'phone' => 'required',
            'address' => 'required',
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        $checkSupplier = $this->mainModel::findById($supplierId);

        if(! $checkSupplier)
            return JsonAPIResponse::sendErrorResponse("Invalid Supplier Selected");

        $fields = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address
        ];

        $createCategory = $this->mainModel->updateSupplierWhereExist($supplierId, $fields);

        return JsonAPIResponse::sendSuccessResponse("Supplier Successfully Updated", $createCategory);

    }
}
