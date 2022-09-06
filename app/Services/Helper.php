<?php


namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class Helper
 * @package App\Services
 */
class Helper
{
    /**
     * This creates a new instance of a model
     * Note: The Model should be used as (new Model/self()) in implementation
     * @param Model $model
     * @param array $fields
     * @return Model
     */
    public static function runModelCreation(Model $model, array $fields): Model
    {
        if (count($fields) > 0) {
            foreach ($fields as $key => $value) {
                if (isset($key))
                {
                    if ($key === 'password')
                        $model->password = $value ? Hash::make($value) : $value;

                    else if ($key === 'name')
                        $model->name = ucwords($value);

                    else if ($key === 'email')
                        $model->email = strtolower($value);

                    else if ($key === 'phone')
                        $model->phone = "+234" . (int)($value);

                    else $model->$key = $value;
                }
            }

            $model->save();

            return $model;
        }
    }

    /**
     * This is used to run a basic update on any model
     * @param Model $model
     * @param array $fields
     * @return Model
     */
    public static function runModelUpdate(Model $model, array $fields): Model
    {
        if ($model && count($fields) > 0)
        {
            foreach ($fields as $key => $value)
            {
                if (isset($key))
                {
                    if ($key === 'password')
                        $model->password = Hash::make($value);

                    else if ($key === 'name')
                        $model->name = ucwords($value);

                    else if ($key === 'email')
                        $model->email = strtolower($value);

                    else if ($key === 'phone')
                        $model->phone = "+234" . (int)($value);

                    else $model->$key = $value;
                }
            }

            $model->save();

            return $model;
        }
    }

    /**
     * This is used to update the records of a given table, where the pointerId is the record id
     * @param int $pointerId
     * @param string $tableName
     * @param array $fields
     * @return int
     */
    public static function updateWithDBQuery(int $pointerId, string $tableName, array $fields = []): int
    {
        if(count($fields) > 0)
        {
            if(isset($fields['guard']))
                unset($fields['guard']);

            return DB::table($tableName)
                ->where('id', $pointerId)
                ->update($fields);
        }
    }

    /**
     * @param Model $model
     * @param string $column
     * @param string $value
     * @param string $vendorCode
     * @return mixed
     */
    public static function findByUserAndColumn(
        Model $model,
        string $column,
        string $value
    ) {
        return $model::findByUserAndColumn($column, $value);
    }

    /**
     * This generates an otp
     * @return int
     * @throws \Exception
     */
    public static function generateOTP(): int
    {
        return random_int(1000, 9999) . time();
    }

    /**
     * This generates a ticket number
     * @return int
     * @throws \Exception
     */
    public static function generateTicketNumber()
    {
        return rand(10000, 90000) . rand(11, 99);
    }

    /**
     * This encrypts a certain string
     * @param string $string
     * @return false|string
     */
    public static function encryptString(string $string)
    {
        return Crypt::encryptString($string);
    }

    /**
     * This decrypts an encrypted string
     * @param string $encryptedValue
     * @return false|string
     */
    public static function decryptString(string $encryptedValue)
    {
        try {

            return Crypt::decryptString($encryptedValue);

        } catch (DecryptException $exception) {

            return null;
        }
    }

    /**
     * @return int
     */
    public static function getIV(): int
    {
        return 1234567891011121;
    }

    /**
     * This gets the exact amount based on Markup
     * @param string $markupType
     * @param float|int $value
     * @param float $initialAmount
     * @return float|int
     */
    public static function getMarkUpAmount(string $markupType = 'Flat', float $value = 0,
                                           float $initialAmount = 0)
    {
        $amount = 0;
        if($markupType === 'Flat')
            $amount = $value + $initialAmount;

        if($markupType === 'Percentage' && $value > 0)
            $amount = $initialAmount + (( $value / 100 ) * $initialAmount);

        return $amount;
    }

    /**
     * return accepted date format
     * @param $date
     * @return bool|false|string
     */
    public static function returnAcceptedDateTime($date)
    {
        try {

            return date('D M jS, Y - h:m A', strtotime($date)); //Tue, Oct 27, 2019

        } catch (\Exception $exception) {

            return $date;
        }
    }
}
