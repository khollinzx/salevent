<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashBook extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function user(): Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** get details by userId and Date
     * @param int $userId
     * @param string $date
     * @return mixed
     */
    public static function findCashBook(int $userId,string $date){
        return self::where('user_id', $userId)
            ->where('date', $date)
            ->first();
    }

    /** get details by date
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    public static function fetchAllCashBookByDate(string $date){
        return DB::table('cash_books')
            -> where('date', $date)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param int $userId
     * @return int|mixed
     */
    public static function sumCashbookAmountByUserIdAndDate(int $userId){
        return DB::table('cash_books')
            -> where('user_id', $userId)
            ->sum('amount');
    }

    /**
     * @param int $userId
     * @return int|mixed
     */
    public static function sumAmount(int $userId){
        return DB::table('cash_books')
            -> where('user_id', $userId)
            ->sum('amount');
    }

    /** This create a new open cash book record
     * @param int $amount
     * @param string $date
     * @param int $userId
     * @return Model
     */
    public function initializeNewOpenCashBook(int $amount, string $date, int $userId)
    {
        return Helper::runModelCreation(new self(),[
            'amount' => $amount,
            'date' => $date,
            'user_id' => $userId
        ]);
    }

    /** fetches the current cash bok for a user by Date and Id
     * @param int $userId
     * @param string $date
     * @return int
     */
    public function getUserCashBookByUserIdAndDate(int $userId,string $date)
    {
        $checker = self::findCashBook($userId, $date);
        if(count($checker) == 0)
            return 0;

        return $checker;
    }

    /** fetches all the users current cash book by date
     * @param string $date
     * @return \Illuminate\Support\Collection|string
     */
    public function getAllCashBookByDate(string $date)
    {
        $checker = self::fetchAllCashBookByDate($date);
        if(count($checker) == 0)
            return 0;

        return $checker;
    }


}
