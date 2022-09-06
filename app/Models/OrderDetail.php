<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class OrderDetail extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * @var array|string[]
     */
    protected array $relationships = [
        'product',
        'product.category',
        'product.user',
        'product.status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /** initialize a new purchased items from the cart table
     * @param int $orderId
     * @param int $orderCode
     * @param Collection $models
     * @return bool
     */
    public function initializeNewOrderDetails(int $orderId, int $orderCode, Collection $models)
    {
        foreach ($models as $model) {
            if ($model->is_bulk) {

                Helper::runModelCreation(new self(),[
                    'order_id' => $orderId,
                    'order_code' => $orderCode,
                    'product_id' => $model->product_id,
                    'quantity' => $model->is_bulk_count,
                    'price' => $model->sub_total,
                    'is_bulk' => $model->is_bulk
                ]);

            } else {

                Helper::runModelCreation(new self(),[
                    'order_id' => $orderId,
                    'order_code' => $orderCode,
                    'product_id' => $model->product_id,
                    'quantity' => $model->quantity,
                    'price' => $model->sub_total,
                    'is_bulk' => $model->is_bulk
                ]);
            }
        }

        return true;
    }

    /** get a logged in user open cash amount by userId and date
     * @param int $orderId
     * @return mixed
     */
    public function getItemsByOrderIdAndUserId(int $orderId)
    {
        return self::with($this->relationships)
            ->where('order_id', $orderId)
            ->get();
    }
}
