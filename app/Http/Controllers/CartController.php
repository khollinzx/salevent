<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Services\JsonAPIResponse;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\This;
use Psy\Util\Json;

class CartController extends Controller
{
    /**
     * CartController constructor.
     * @param Cart $cart
     * @param Product $product
     */
    public function __construct(Cart $cart, Product $product)
    {
        $this->mainModel = $cart;
        $this->productModel = $product;
    }

    /** add an item to a cart either by bulk or single quantity
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItemToCart(int $productId)
    {
        $userId = $this->getUserId();

        if(!$this->productModel::findById($productId))
            return JsonAPIResponse::sendErrorResponse("Sorry!, Invalid product item selected");

        $addToCart = $this->mainModel->addToCart($userId, $productId);

        if(!$addToCart)
            return JsonAPIResponse::sendErrorResponse("Sorry!, these item is either Low In Stock or Out Of Stock");

        return JsonAPIResponse::sendSuccessResponse('Item has been added' );
    }

    /** fetches all the cart items
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCartItems()
    {
        $userId = $this->getUserId();

        return JsonAPIResponse::sendSuccessResponse('All Cart Items attached to '.$userId , $this->mainModel->fetchAllCartItemBySalesId($userId));
    }

    /** increases a selected cart item by one or bulk quantity
     * @param int $cartId
     * @return \Illuminate\Http\JsonResponse
     */
    public function incrementByOne(int $cartId)
    {
        if(!$this->mainModel->increaseItemByOne($cartId))
            return JsonAPIResponse::sendErrorResponse("Sorry!, these item is either Low In Quantity or Out Of Stock");

        return JsonAPIResponse::sendSuccessResponse('Item has been added');
    }

    /** decreases a selected cart item by one or bulk quantity
     * @param int $cartId
     * @return \Illuminate\Http\JsonResponse
     */
    public function decrementByOne(int $cartId)
    {
        if(!$this->mainModel::findById($cartId))
            return JsonAPIResponse::sendErrorResponse("Sorry!, Invalid cart item selected");

        return JsonAPIResponse::sendSuccessResponse('Item has been removed', $this->mainModel->decreaseItemByOne($cartId));
    }

    /** return a selected cart item to its existing stock either in bulk or single quantity
     * @param int $cartId
     * @return \Illuminate\Http\JsonResponse
     */
    public function returnItemToStockByCartId(int $cartId)
    {
        if(!$this->mainModel::findById($cartId))
            return JsonAPIResponse::sendErrorResponse("Sorry!, invalid Item Selected");

        return JsonAPIResponse::sendSuccessResponse('Item has been removed', $this->mainModel->returnToStock($cartId));
    }
}
