<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $relationships = [
        'users',
        'category',
        'status'
    ];

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public static function findById(int $product_id)
    {
        return self::with(['category','status'])
            ->find($product_id);
    }

    public function findByColumnAndField($column, $field)
    {
        return self::with($this->relationships)
            ->where($column, $field)
            ->first();
    }

    public function findAllChildProduct($column, $field)
    {
        return self::with(['status'])
            ->where($column, $field)
            ->orderByDesc('id')
            ->get();
    }

    /**Fetches all Products
     * @return Product[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllProducts()
    {
        return self::with($this->relationships)
            ->orderByDesc('id')
            ->get();
    }

    /**Fetches all POS products where $quantity is greater than zero
     * @return Product[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchPOSProducts()
    {
        return self::with($this->relationships)
            ->where('status_id', Status::getStatusByName(Status::$AVAILABLE)->id)
            ->orWhere('status_id', Status::getStatusByName(Status::$LOW_ON_STOCK)->id)
            ->orderByDesc('id')
            ->get();
    }

    public static function findProductByName(string $name)
    {
        return self::where('name', ucwords($name))
            ->first();
    }

    /**This method create new Product
     * by checking if the Product name exist
     * @param int $user_id
     * @param array $fields
     * @return Model
     */
    public function initializeNewProduct(int $user_id, array $fields):Model
    {
        $product = self::findProductByName($fields['name']);
        $statusId = Status::getStatusByName(Status::$NO_ADDED_STOCK);
        if(!$product)
            return Helper::runModelCreation(new self(),
                [
                    'name' => $fields['name'],
                    'price' => $fields['price'],
                    'is_bulk' => 0,
                    'bulk_quantity' => 0,
                    'quantity' => 0,
                    'image' => $fields['image'],
                    'category_id' => $fields['category_id'],
                    'user_id' => $user_id,
                    'status_id' => $statusId->id,
                    'parent_product' => 0
                ]
            );
    }

    /**This method create new Product
     * by checking if the Product name exist
     * @param int $user_id
     * @param array $fields
     * @return Model
     */
    public function initializeNewBulkProduct(int $user_id, array $fields):Model
    {
        $product = self::findProductByName($fields['name']);
        if(!$product)
            $productDetails = self::findByColumnAndField('id', $fields['parent_product']);

        if($productDetails->quantity >= $fields['bulk_quantity']){
            $statusId = Status::getStatusByName(Status::$AVAILABLE);
            return Helper::runModelCreation(new self(),
                [
                    'name' => $fields['name'],
                    'price' => $fields['price'],
                    'is_bulk' => 1,
                    'bulk_quantity' => $fields['bulk_quantity'],
                    'quantity' => 0,
                    'image' => $fields['image'],
                    'category_id' => $productDetails->category_id,
                    'user_id' => $user_id,
                    'status_id' => $statusId->id,
                    'parent_product' => $productDetails->id
                ]
            );
        } else {
            $statusId = Status::getStatusByName(Status::$OUT_OF_STOCK);
            return Helper::runModelCreation(new self(),
                [
                    'name' => $fields['name'],
                    'price' => $fields['price'],
                    'is_bulk' => 1,
                    'bulk_quantity' => $fields['bulk_quantity'],
                    'quantity' => 0,
                    'image' => $fields['image'],
                    'category_id' => $productDetails->category_id,
                    'user_id' => $user_id,
                    'status_id' => $statusId->id,
                    'parent_product' => $productDetails->id
                ]
            );
        }

    }

    /**This method updates and exist product by Id
     * @param int $product_id
     * @param array $fields
     * @return Model
     */
    public function updateProductWhereExist(int $product_id, array $fields):Model
    {
        $product = self::findById($product_id);
        if($product)
            return Helper::runModelUpdate($product, $fields);
    }

    /** check if the product name exist else where
     * @param int $productId
     * @param string $column
     * @param string $value
     * @return mixed
     */
    public static function CheckIfNameExistElseWhere(int $productId, string $column, string $value)
    {
        return self::where('id','!=', $productId)
            ->where($column, $value)
            ->first();
    }

    /**This method updates and exist bulk product by Id
     * @param int $product_id
     * @param array $fields
     * @return Model
     */
    public function updateBulkProductWhereExist(int $product_id,array $fields):Model
    {
        $bulkProduct = self::findById($product_id);
        if($bulkProduct)
            return Helper::runModelUpdate($bulkProduct, $fields);
    }

    /**This method top up product quantity product by Id
     * @param int $userId
     * @param int $product_id
     * @param int $quantity
     * @return Model
     */
    public function topUpProductQuantity(int $userId, int $product_id, int $quantity):Model
    {
        $product = self::findById($product_id);
        if($product)
            $childProducts = self::findAllChildProduct('parent_product',$product->id);

        $totalQuantity = ($product->quantity + $quantity);

        (new Stock())->initializeNewStocks($userId, $product_id, $product->quantity, $quantity);

        if($childProducts){
            /** update each childe product status */
            if(self::setEachProductChild($childProducts, $totalQuantity))
                /** Update parent product status and quantity */
                return Helper::runModelUpdate($product,
                    [
                        'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id,
                        'quantity' => $totalQuantity
                    ]);
        }else{
            /** Update parent product status and quantity */
            return Helper::runModelUpdate($product,
                [
                    'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id,
                    'quantity' => $totalQuantity
                ]);
        }
    }

    /** This function set the quantity and the status the child product of a product
     * @param Model $models
     * @param int $quantity
     * @return bool
     */
    public static function setEachProductChild(Collection $models, int $quantity){
        foreach ($models as $model) {

            /**
             * Update child products status if parent quantity is sufficient
             */
            if ($quantity >= $model->bulk_quantity) {
                Helper::runModelUpdate($model,
                    [
                        'status_id' => Status::getStatusByName(Status::$AVAILABLE)->id
                    ]);
            } else {
                Helper::runModelUpdate($model,
                    [
                        'status_id' => Status::getStatusByName(Status::$OUT_OF_STOCK)->id
                    ]);
            }
        }

        return true;
    }

}
