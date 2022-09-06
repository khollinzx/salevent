<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public static $POS = 'POS (ATM Card)';
    public static $CASH = 'Cash';

    protected $fillable = [
        'id', 'name'
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Fetches Payment Type model by title
     * @param string $name
     * @return mixed
     */
    public static function getPaymentTypeByName(string $name)
    {
        return self::where('name', ucwords($name))->first();
    }

    /**
     * This is initializes all default Payment Types
     */
    public static function initPaymentType()
    {
        $payment_type = [
            self::$POS,
            self::$CASH
        ];

        foreach ($payment_type as $type)
        {
            self::addPaymentType($type);
        }
    }

    /**
     * Add a new Payment Type
     * @param string $name
     */
    public static function addPaymentType(string $name)
    {
        if(!self::getPaymentTypeByName(ucwords($name)))
        {
            $Status = new self();
            $Status->name = ucwords($name);
            $Status->save();
        }
    }
}
