<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order_items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(OrderDetail::class);
    }

    /** checks if the id exist
     * @param int $orderId
     * @return mixed
     */
    public static function findById(int $orderId){
        return self::with(['user'])
                    ->find($orderId);
    }

    /** gets a record by orderId and userId
     * @param int $orderId
     * @param int $userId
     * @return mixed
     */
    public static function findBbyIdAndUserId(int $orderId, int $userId){
        return self::where('id',$orderId)
                    ->where('user_id', $userId)
                    ->first();
    }

    /** initialize a new purchased order
     * @param int $userId
     * @param int $orderCode
     * @param array $fields
     * @return Model
     */
    public function initializeNewOrder(int $userId, int $orderCode, array $fields)
    {
        return Helper::runModelCreation(new self(),[
            'order_code' => $orderCode,
            'total_items' => $fields["total_items"],
            'total_price' => $fields["total_price"],
            'payment_type_id' => $fields["payment_type_id"],
            'reference_no' => $fields["reference_no"],
            'balance' => $fields["balance"],
            'amount_paid' => $fields["amount_paid"],
            'user_id' => $userId,
            'date' => date('Y-m-d'),
            'month' => date('F'),
            'year' => date('Y')
        ]);
    }

    /** get a logged in user open cash amount by userId and date
     * @param int $userId
     * @param $date
     * @return mixed
     */
    public function getAllOrderedItemsByUserId(int $userId)
    {
        return self::where('user_id', $userId)
                    ->get();
    }

    /** get an order record by orderId
     * @param int $orderId
     * @return mixed
     */
    public function getOrderRecord(int $orderId)
    {
        return self::where('id', $orderId)->first();
    }


}
