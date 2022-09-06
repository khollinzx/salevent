<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Cart extends Model
{
    use HasFactory;

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /** get all items been attended to by a sales representative
     * @param int $userId
     * @param int $productId
     * @return mixed
     */
    public static function getProductByUserIdAndProductId(int $userId, int $productId)
    {
        return self::where('product_id',$productId)
            ->where('user_id', $userId)
            ->first();
    }

    /** clears all items in cart
     * @param Collection $models
     */
    public static function clearItemsFromCart(Collection $models)
    {
        foreach ($models as $model){
            self::findById($model->id)->delete();
        }
    }

    /** check for a id */
    public static function findById(int $productId)
    {
        return self::find($productId);
    }

    /** its perform calculation on Bulk Items
     * and set its status to 'Out of Stock'
     * @param Model $parent
     * @param Model $child
     * @param int $isBulk
     */
    public static function removeFromStockForBulkItems(Model $parent, Model $child, int $isBulk = 0)
    {
        if($isBulk){
            $remaining = ($parent->quantity - $child->bulk_quantity);

            if($remaining > 0){
                (new Product())->updateProductWhereExist($parent->id,[
                    'quantity' => $remaining ,
                ]);
            } else {
                (new Product())->updateProductWhereExist($parent->id,
                    [
                        'quantity' => $remaining > 0 ? $remaining : 0,
                        'status_id' => Status::getStatusByName(Status::$OUT_OF_STOCK)->id
                    ]
                );
            }

            if($child->bulk_quantity > $remaining)
                (new Product())->updateProductWhereExist($child->id,
                    ['status_id' => Status::getStatusByName(Status::$OUT_OF_STOCK)->id]
                );

        }

    }

    /** its perform calculation on Non Bulk Items
     * and set its status to 'Out of Stock'
     * @param Model $parent
     * @param $quantity
     */
    public static function removeFromStockForNonBulkItems(Model $parent, $quantity)
    {
        $remaining = ($parent->quantity - $quantity);
        if($remaining > 0){
            (new Product())->updateProductWhereExist($parent->id,[
                'quantity' => $remaining ,
            ]);
        } else {
            (new Product())->updateProductWhereExist($parent->id,
                ['quantity' => $remaining > 0 ? $remaining : 0,
                    'status_id' => Status::getStatusByName(Status::$OUT_OF_STOCK)->id]
            );
        }
    }

    /** get all items added to cart by a user by UserId
     * @param int $userId
     * @return mixed
     */
    public static function fetchAllCartItemBySalesId(int $userId)
    {
        return self::where('user_id', $userId)
            ->orderByDesc('id')
            ->get();
    }

    /** this functions adds a new item to a cart
     * @param int $userId
     * @param int $productId
     * @param int $quantity
     * @param int $price
     * @param int $isBulk
     * @param int $subTotal
     * @return Model
     */
    public function addNewItemToCart(int $userId, int $productId, int $quantity, int $price, int $isBulk, int $subTotal)
    {
        if($isBulk){
            return Helper::runModelCreation(new self,
                [
                    'product_id' => $productId,
                    'user_id'=> $userId,
                    'quantity'=> $quantity,
                    'price'=> $price,
                    'is_bulk'=> $isBulk,
                    'is_bulk_count' => 1,
                    'sub_total'=> $subTotal
                ]
            );
        } else {
            return Helper::runModelCreation(new self,
                [
                    'product_id' => $productId,
                    'user_id'=> $userId,
                    'quantity'=> $quantity,
                    'price'=> $price,
                    'is_bulk'=> $isBulk,
                    'sub_total'=> $subTotal,
                    'is_bulk_count' => 0
                ]
            );
        }
    }

    /** this function updates an existing item in a cart
     * @param Model $model
     * @param int $quantity
     * @param int $subTotal
     * @param int $is_bulk
     * @param int|null $is_bulk_count
     * @return Model
     */
    public function updateItemInCart(Model $model, int $quantity, int $subTotal, int $is_bulk = 0, int $is_bulk_count = 0)
    {
        if($is_bulk){
            return Helper::runModelUpdate($model,
                [
                    'is_bulk_count' => $is_bulk_count,
                    'quantity'=> $quantity,
                    'sub_total'=> $subTotal
                ]
            );
        } else {
            return Helper::runModelUpdate($model,
                [
                    'quantity'=> $quantity,
                    'sub_total'=> $subTotal
                ]
            );
        }

    }

    /** initialize a new item to a cart
     * it also deduct from the existing product quantity either in bulk or single quantity
     * then set the status of the parent product to 'Out of Stock' if the quantity is 0
     * then also set the child product to 'Out of Stock' is its bulk quantity is greater that it parent quantity
     * @param int $userId
     * @param int $productId
     * @return Model
     */
    public function addToCart(int $userId,int $productId)
    {
        $product = (new Product())::findById($productId);
        $quantity = 1;

        if($product)
        {
            if($product->is_bulk){
                $cartProduct = self::getProductByUserIdAndProductId($userId, $productId);
                $parentProduct = (new Product())::findById((new Product())::findById($productId)->parent_product);

                if($parentProduct->quantity >= $product->bulk_quantity ){
                    if($cartProduct)
                    {
                         self::updateItemInCart(
                            $cartProduct,
                            ($cartProduct->quantity + $product->bulk_quantity),
                            (($cartProduct->is_bulk_count + $quantity)*$product->price),
                            $product->is_bulk,
                            ($cartProduct->is_bulk_count + $quantity)
                         );

                         self::removeFromStockForBulkItems($parentProduct, $product, $product->is_bulk);

                        return true;
                    }else{
                        self::addNewItemToCart(
                            $userId,
                            $productId,
                            $product->bulk_quantity,
                            $product->price,
                            $product->is_bulk,
                            $product->price
                        );

                        self::removeFromStockForBulkItems($parentProduct, $product, $product->is_bulk);

                        return true;
                    }
                } else {
                    return false;
                }
            } else {
                $cartProduct = self::getProductByUserIdAndProductId($userId, $productId);

                if($product->quantity >= $quantity){
                    if($cartProduct)
                    {
                        self::updateItemInCart(
                            $cartProduct,
                            ($cartProduct->quantity + $quantity),
                            (($cartProduct->quantity + $quantity) * $product->price)
                        );
                        self::removeFromStockForNonBulkItems($product, $quantity);

                        return true;

                    }else{
                        self::addNewItemToCart(
                            $userId,
                            $productId,
                            $quantity,
                            $product->price,
                            $product->is_bulk,
                            $product->price
                        );
                        self::removeFromStockForNonBulkItems($product, $quantity);

                        return true;
                    }
                } else {
                    return false;
                }
            }


        }
    }

    /** this increment an existing item in a cart by 1 using the CartId
     * it also deduct from the existing product quantity either in bulk or single quantity
     * then set the status of the parent product to 'Out of Stock' if the quantity is 0
     * then also set the child product to 'Out of Stock' is its bulk quantity is greater that it parent quantity
     * @param int $cartId
     * @return Model
     */
    public function increaseItemByOne(int $cartId)
    {
        $increment = 1;

            if(self::findById($cartId)->is_bulk){

                $parentProduct = (new Product())::findById((new Product())::findById(self::findById($cartId)->product_id)->parent_product);

                if($parentProduct->quantity >= (new Product())::findById(self::findById($cartId)->product_id)->bulk_quantity ){
                    self::updateItemInCart(
                        self::findById($cartId),
                        (self::findById($cartId)->quantity + (new Product())::findById(self::findById($cartId)->product_id)->bulk_quantity),
                        ((self::findById($cartId)->is_bulk_count + $increment)*self::findById($cartId)->price),
                        (new Product())::findById(self::findById($cartId)->product_id)->is_bulk,
                        (self::findById($cartId)->is_bulk_count + $increment)
                    );

                    self::removeFromStockForBulkItems(
                        $parentProduct,
                        (new Product())::findById(self::findById($cartId)->product_id),
                        (new Product())::findById(self::findById($cartId)->product_id)->is_bulk
                    );

                    return true;
                } else {
                    return false;
                }

            } else {
                if((new Product())::findById(self::findById($cartId)->product_id)->quantity >= $increment ){
                    self::updateItemInCart(
                        self::findById($cartId),
                        (self::findById($cartId)->quantity + $increment),
                        ((self::findById($cartId)->quantity + $increment) * self::findById($cartId)->price)
                    );

                    self::removeFromStockForNonBulkItems(
                        (new Product())::findById(self::findById($cartId)->product_id),
                        $increment
                    );

                    return true;
                } else {
                    return false;
                }
            }
    }

    /** this decrement an existing item in a cart by 1 or by bulk quantity using the CartId
     * it also returns a selected amount of quantity either in bulk or single product and
     * set the status of a parent product to 'Available' if its greater than zero then also
     * set the status of a child product to 'Available if the parent quantity is greater than it bulk quantity
     * then empties the cart if no item is existing in it.
     * @param int $cartId
     * @return Model
     */
    public function decreaseItemByOne(int $cartId)
    {
        $decrement = 1;
        if(self::findById($cartId)->is_bulk)
        {
            if(self::findById($cartId)->is_bulk_count > $decrement)
            {
                self::returnToStockByDecrement($cartId, self::findById($cartId)->is_bulk);
                return self::updateItemInCart(
                    self::findById($cartId),
                    (self::findById($cartId)->quantity - (new Product())::findById(self::findById($cartId)->product_id)->bulk_quantity),
                    ((self::findById($cartId)->is_bulk_count - $decrement)*self::findById($cartId)->price),
                    (new Product())::findById(self::findById($cartId)->product_id)->is_bulk,
                    (self::findById($cartId)->is_bulk_count - $decrement)
                );

            } else {
                self::returnToStockByDecrement($cartId, self::findById($cartId)->is_bulk);
                self::findById($cartId)->delete();
            }
        } else {
            if(self::findById($cartId)->quantity > $decrement)
            {
                self::returnToStockByDecrement($cartId);
                return self::updateItemInCart(
                    self::findById($cartId),
                    (self::findById($cartId)->quantity - $decrement),
                    ((self::findById($cartId)->quantity - $decrement) * self::findById($cartId)->price)
                );

            }else{
                self::returnToStockByDecrement($cartId);
                self::findById($cartId)->delete();
            }
        }
    }

    /** its returns the entire quantity of items back to stock using cartId
     * @param int $cartId
     * @return bool
     */
    public function returnToStockInFull(int $cartId)
    {
        if(self::findById($cartId)->is_bulk)
        {
            $quantityInStock = (new Product())::findById((new Product())::findById(self::findById($cartId)->product_id)->parent_product)->quantity;
            $sumQuantity = $quantityInStock + self::findById($cartId)->quantity;

            if($sumQuantity > (new Product())::findById(self::findById($cartId)->product_id)->bulk_quantity)
            {
                (new Product())->updateProductWhereExist((new Product())::findById(self::findById($cartId)->product_id)->parent_product,
                    ['quantity' => $sumQuantity,
                    'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id]
                );

                (new Product())->updateProductWhereExist(self::findById($cartId)->product_id,
                    ['status_id' => Status::getStatusByName(Status::$AVAILABLE)->id]
                );

                self::findById($cartId)->delete();

                return true;
            }
            return false;

        } else {
            $quantityInStock = (new Product())::findById(self::findById($cartId)->product_id)->quantity;
            $sumQuantity = $quantityInStock + self::findById($cartId)->quantity;

            (new Product())->updateProductWhereExist(self::findById($cartId)->product_id,
                ['quantity' => $sumQuantity,
                    'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id ]
            );

            self::findById($cartId)->delete();

            return true;
        }
    }

    public function returnToStockByDecrement(int $cartId, $isBulk = 0)
    {
        if($isBulk)
        {
            $quantityInStock = (new Product())::findById((new Product())::findById(self::findById($cartId)->product_id)->parent_product)->quantity;
            $sumQuantity = $quantityInStock + (new Product())::findById(self::findById($cartId)->product_id)->bulk_quantity;

            if($sumQuantity > (new Product())::findById(self::findById($cartId)->product_id)->bulk_quantity) {
                (new Product())->updateProductWhereExist((new Product())::findById(self::findById($cartId)->product_id)->parent_product,
                    ['quantity' => $sumQuantity,
                        'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id]
                );

                (new Product())->updateProductWhereExist(self::findById($cartId)->product_id,
                    ['status_id' => Status::getStatusByName(Status::$AVAILABLE)->id]
                );

            } else {
                (new Product())->updateProductWhereExist((new Product())::findById(self::findById($cartId)->product_id)->parent_product,
                    ['quantity' => $sumQuantity,
                        'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id]
                );
            }

        } else {
            $quantityInStock = (new Product())::findById(self::findById($cartId)->product_id)->quantity;
            $sumQuantity = $quantityInStock + 1;

            (new Product())->updateProductWhereExist(self::findById($cartId)->product_id,
                [
                    'quantity' => $sumQuantity,
                    'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id
                ]
            );
        }
    }




}
