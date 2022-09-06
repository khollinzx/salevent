<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    protected $mainModel;

    /**
     * ProductController constructor.
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->mainModel = $product;
    }

    /** Create a New Product
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewProduct(Request $request)
    {
        $userId = $this->getUserId();

        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => [
                'required',
                Rule::unique('products', ucwords('name'))
            ],
            "price" => 'required|integer',
            'image' => 'required',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        $fields = [
            'name' => $request->name,
            'price' => $request->price,
            'image' => $request->image,
            'category_id' => $request->category_id
        ];

        return JsonAPIResponse::sendSuccessResponse("A new Products has been created Successfully",
            $this->mainModel->initializeNewProduct($userId, $fields));
    }

    /** Create a New Bulk Product
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewBulkProduct(Request $request)
    {
        $userId = $this->getUserId();

        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => [
                'required',
                Rule::unique('products', ucwords('name'))
            ],
            "price" => 'required|integer',
            'image' => 'required',
            'parent_product' => 'required|integer|exists:products,id',
            'bulk_quantity' => 'required|integer',
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        $fields = [
            'name' => $request->name,
            'price' => $request->price,
            'image' => $request->image,
            'bulk_quantity' => $request->bulk_quantity,
            'parent_product' => $request->parent_product
        ];

        return JsonAPIResponse::sendSuccessResponse("A new Bulk Products has been created Successfully",
            $this->mainModel->initializeNewBulkProduct($userId, $fields));
    }

    /** Fetches all Products Where Status Available and Low on Stock
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAvailableAndLowOnStockProducts(): \Illuminate\Http\JsonResponse
    {
        if(! $this->mainModel->fetchPOSProducts())
            return JsonAPIResponse::sendErrorResponse("No Records Found");

        return JsonAPIResponse::sendSuccessResponse("All Products Still in Stock", $this->mainModel->fetchPOSProducts());
    }

    /** Fetch a Products by Id
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductById(int $productId)
    {
        $product = $this->mainModel::findById($productId);

        if(!$product)
            return JsonAPIResponse::sendErrorResponse('No Record Found');

        if($product->is_bulk)
            return JsonAPIResponse::sendErrorResponse('The product has a parent product');

        $childProducts = $this->mainModel->findAllChildProduct('parent_product', $product->id);

        $response = [
            'product' => $product,
            'child_product' => $childProducts
        ];

        return JsonAPIResponse::sendSuccessResponse("Category Details", $response);
    }

    /** Updates a Category by Id
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProductById(Request $request, int $productId)
    {
        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => [
                'required',
                function($k, $v, $fn) use($productId)
                {
                    if($this->mainModel::CheckIfNameExistElseWhere($productId, $k, $v))
                        $fn("This product name already exists.");
                }
            ],
            "price" => 'required|integer',
            'image' => 'required'
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());


        if(! $this->mainModel::findById($productId))
            return JsonAPIResponse::sendErrorResponse("Invalid Product Selected");

        $fields = [
                'name' => $request->name,
                'price' => $request->price,
                'image' => $request->image
            ];

        return JsonAPIResponse::sendSuccessResponse("Product Successfully Updated",
            $this->mainModel->updateProductWhereExist($productId, $fields));

    }

    /** Updates a Bulk Products by Id
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBulkProductById(Request $request, int $productId)
    {
        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            "name" => [
                'required',
                function($k, $v, $fn) use($productId)
                {
                    if($this->mainModel::CheckIfNameExistElseWhere($productId, $k, $v))
                        $fn("This product name already exists.");
                }
            ],
            "price" => 'required|integer',
            'image' => 'required',
            'bulk_quantity' => 'required|integer'
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        if(! $this->mainModel::findById($productId))
            return JsonAPIResponse::sendErrorResponse("Invalid Bulk Product Selected");

        if(! $this->mainModel::findById($productId)->is_bulk)
            return JsonAPIResponse::sendErrorResponse("The Item selected is not a bulk product");

        $fields = [
            'name' => $request->name,
            'price' => $request->price,
            'image' => $request->image,
            'bulk_quantity' => $request->bulk_quantity
        ];

        return JsonAPIResponse::sendSuccessResponse("Bulk Product Successfully Updated",
            $this->mainModel->updateBulkProductWhereExist($productId, $fields));

    }

    /** Updates a Bulk Products by Id
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function topUpProductById(Request $request, int $productId)
    {
        $userId = $this->getUserId();
        /**
         * Set the Validation rules
         */
        $Validation = Validator::make($request->all(), [
            'quantity' => 'required|integer'
        ]);

        /**
         * Returns validation errors if any
         */
        if ($Validation->fails())
            return JsonAPIResponse::sendErrorResponse($Validation->errors()->first());

        if(! $this->mainModel::findById($productId))
            return JsonAPIResponse::sendErrorResponse("Invalid Product Selected");

        if($this->mainModel::findById($productId)->is_bulk)
            return JsonAPIResponse::sendErrorResponse("Can't Top Up a child product");

        try {

            return JsonAPIResponse::sendSuccessResponse(
                "{$this->mainModel->topUpProductQuantity($userId, $productId, $request->quantity)->name} has been Top Up Successfully");

        } catch (\Exception $exception) {
            Log::error($exception);
            return JsonAPIResponse::sendErrorResponse("An internal error has occurred. Please try again later.");
        }

    }


}
