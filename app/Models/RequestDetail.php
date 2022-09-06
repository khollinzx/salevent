<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RequestDetail extends Model
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
    public function requests(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    /** initialize a new purchased items from the cart table
     * @param int $request_code
     * @param int $request_id
     * @param array $fields
     * @return bool
     */
    public function initializeNewOrderDetails(int $request_code,int $request_id, array $fields)
    {
        foreach ($fields as $field) {

            Helper::runModelCreation(new self(),[
                'request_code' => $request_code,
                'request_id' => $request_id,
                'product_id' => $field['product_id'],
                'quantity' => $field['quantity'],
            ]);
        }

        return true;
    }

    /** get a logged in user open cash amount by userId and date
     * @param int $orderId
     * @param int $userId
     * @return mixed
     */
    public function getItemsByOrderIdAndUserId(int $orderId)
    {
        return self::with($this->relationships)
            ->where('order_id', $orderId)
            ->get();
    }
}
