<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected $mainModel;

    /**
     * CategoryController constructor.
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->mainModel = $category;
    }

    /** Create a New Category
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewCategory(Request $request)
    {
        $userId = $this->getUserId();

        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => [
                'required',
                Rule::unique('categories', ucwords('name'))
            ]
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        return JsonAPIResponse::sendSuccessResponse("A new Category has been created Successfully",
            $this->mainModel->initializeNewCategory($userId, $request->name));
    }

    /** Fetches all Categories
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCategories(): \Illuminate\Http\JsonResponse
    {
        if(!$this->mainModel::fetchAllCategories())
            return JsonAPIResponse::sendErrorResponse("No Records Found");

        return JsonAPIResponse::sendSuccessResponse("All Categories",
            $this->mainModel::fetchAllCategories());
    }

    /** Fetch a Category by Id
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoryById(int $categoryId)
    {
        if(!$this->mainModel::findCategoryById($categoryId))
            return JsonAPIResponse::sendErrorResponse('No Record Found');

        return JsonAPIResponse::sendSuccessResponse("Category Details",
            $this->mainModel::findCategoryById($categoryId));
    }

    /** Updates a Category by Id
     * @param Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCategoryById(Request $request, int $categoryId)
    {
        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => [
                'required'
            ]
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        if(! $this->mainModel::findCategoryById($categoryId))
            return JsonAPIResponse::sendErrorResponse("Invalid Category Selected");

        return JsonAPIResponse::sendSuccessResponse("Category Successfully Updated",
            $this->mainModel->updateCategoryWhereExist($this->mainModel::findCategoryById($categoryId), $request->name));

    }
}
