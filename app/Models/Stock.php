<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $relationships = [
        'product',
        'product.users',
        'product.category',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public static function findById(int $product_id)
    {
        return self::with(['product'])
            ->find($product_id);
    }

    public function findByColumnAndField($column, $field)
    {
        return self::with($this->relationships)
            ->where($column, $field)
            ->first();
    }

    /**Fetches all stocks
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllStockRecord()
    {
        return self::with($this->relationships)
            ->orderByDesc('id')
            ->get();
    }


    /**This method create new Stock
     * @param int $user_id
     * @param int $product_id
     * @param int $previousQty
     * @param int $addedQty
     * @return Model
     */
    public function initializeNewStocks(int $user_id, int $product_id, int $previousQty, int $addedQty):Model
    {
        return Helper::runModelCreation(new self(),
            [
                'product_id' => $product_id,
                'user_id' => $user_id,
                'previous_quantity' => $previousQty,
                'added_quantity' => $addedQty,
                'total_quantity' => ($previousQty + $addedQty),
                'date' => date('Y-m-d'),
                'month' => date('F'),
                'year' => date('Y'),
            ]
        );
    }

}
