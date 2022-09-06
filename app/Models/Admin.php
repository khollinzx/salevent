<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    public static $FIRSTNAME = 'Kenneth';
    public static $LASTNAME = 'Benson';
    public static $USERNAME = 'kenneth@salevent.com';
    public static $PASSWORD = 'password';

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * This is the authentication guard to be used on this Model
     * This overrides the default guard which is the user guard
     * @var string
     */
    protected static $guard = 'admin';

    /**
     * This forces the auth guard to use the drivers table for authentication
     * @var string
     */
    protected $table = 'admins';

    /**
     * finds a Admin User by login credentials
     * @param string $column
     * @param string $value
     * @return mixed
     */
    public static function findByUserAndColumn(string $column, string $value)
    {
        return self::where($column, $value)
            ->first();
    }

    /**check if a user with the username exist
     * @param string $username
     * @return mixed
     */
    public static function getUserByUsername(string $username)
    {
        return self::where('username',$username)->first();
    }


    /**
     * This is initializes a default user
     */
    public static function initUser()
    {
        if(!self::getUserByUsername(self::$USERNAME))
        {
            $Status = new self();
            $Status->first_name = ucwords(self::$FIRSTNAME);
            $Status->last_name = ucwords(self::$LASTNAME);
            $Status->username = self::$USERNAME;
            $Status->password = Hash::make(self::$PASSWORD);
            $Status->save();
        }
    }

}
