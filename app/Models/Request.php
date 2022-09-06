<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
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
    public function request_items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(RequestDetail::class);
    }

    /** get an order record by orderId
     * @param int $orderId
     * @return mixed
     */
    public function getRecord(int $orderId)
    {
        return self::where('id', $orderId)->first();
    }

    /** checks if the id exist
     * @param int $requestId
     * @return mixed
     */
    public static function findById(int $requestId){
        return self::with(['user'])
            ->find($requestId);
    }

    /** gets a record by orderId and userId
     * @param int $orderId
     * @param int $userId
     * @return mixed
     */
    public static function findBbyIdAndUserId(int $requestId, int $userId){
        return self::where('id',$requestId)
            ->where('user_id', $userId)
            ->first();
    }

    /** initialize a new purchased order
     * @param int $userId
     * @param int $orderCode
     * @param array $fields
     * @return Model
     */
    public function initializeNewRequest(int $userId, array $fields)
    {
        return Helper::runModelCreation(new self(),[
            'request_code' => $fields["total_items"],
            'user_id' => $userId,
            'date' => date('Y-m-d'),
            'month' => date('F'),
            'year' => date('Y')
        ]);
    }

    /** get a logged in user open cash amount by userId and date
     * @param int $userId
     * @return mixed
     */
    public function getAllRequestedByUserId(int $userId)
    {
        return self::where('user_id', $userId)
            ->get();
    }
}
