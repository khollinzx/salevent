<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public static $AVAILABLE = 'Available';
    public static $OUT_OF_STOCK = 'Out of Stock';
    public static $LOW_ON_STOCK = 'Low on Stock';
    public static $NO_ADDED_STOCK = 'No Added Stock';

    protected $fillable = [
        'id', 'name'
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Fetches status model by title
     * @param string $name
     * @return mixed
     */
    public static function getStatusByName(string $name)
    {
        return self::where('name', ucwords($name))->first();
    }

    /**
     * This is initializes all default statuses
     */
    public static function initStatus()
    {
        $statuses = [
            self::$AVAILABLE,
            self::$OUT_OF_STOCK,
            self::$LOW_ON_STOCK,
            self::$NO_ADDED_STOCK
        ];

        foreach ($statuses as $status)
        {
            self::addStatus($status);
        }
    }

    /**
     * Add a new status
     * @param string $name
     */
    public static function addStatus(string $name)
    {
        if(!self::getStatusByName(ucwords($name)))
        {
            $Status = new self();
            $Status->name = ucwords($name);
            $Status->save();
        }
    }

    public function initializeNewStatus(string $name)
    {
        $checker = self::getStatusByName($name);
        if(! $checker)
            return Helper::runModelCreation(new self(), [
                'name' => $name
            ]);
    }
}
