<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    use HasFactory;

    public static string $BANK_NAME = 'Fidelity Bank';
    public static string $ACCT_NAME = 'Collins Benson';
    public static int $ACCT_NUMBER = 0101010010;
    public static int $SORT_CODE = 4556;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /** fetch a details by $id
     * @param int $id
     * @return mixed
     */
    public static function findById(int $id)
    {
        return self::find($id);
    }

    /** fetches all plentywaka bank details
     * @return mixed
     */
    public function getAllBankDetails()
    {
        return self::orderByDesc('id')
            ->get();
    }

    public static function initSaleVentBankDetail()
    {
        Helper::runModelCreation(new self(), [
            'bank_name' => self::$BANK_NAME,
            'account_name' => self::$ACCT_NAME,
            'account_number' => self::$ACCT_NUMBER,
            'sort_code' => self::$SORT_CODE
        ]);
    }

    /** update an existing plentywaka bank details
     * @param Model $model
     * @param array $fields
     * @return Model
     */
    public function updateExistingBankDetail(Model $model, array $fields)
    {
        return Helper::runModelUpdate($model, [
            'bank_name' => $fields['bank_name'],
            'account_name' => $fields['account_name'],
            'account_number' => $fields['account_number'],
            'sort_code' => $fields['sort_code']
        ]);
    }
}
