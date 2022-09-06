<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    public static $FIRSTNAME = 'Collins';
    public static $LASTNAME = 'Benson';
    public static $USERNAME = 'collins@salevent.com';
    public static $PASSWORD = 'password';
    public static $PHONE = '08188531726';
    public static $ADDRESS = '188, Borno Way, Yaba, Lagos.';
    public static $TAG = 'Collins[Sales Supervisor]';
    public static $IS_LOGGED_IN = 0;
    public static $ROLE_ID = "Super Admin";

    /**
     * This is the authentication guard to be used on this Model
     * This overrides the default guard which is the user guard
     * @var string
     */
    protected static $guard = 'api';

    /**
     * This forces the auth guard to use the drivers table for authentication
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'username',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
    ];

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
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
            $Status->phone = self::$PHONE;
            $Status->address = self::$ADDRESS;
            $Status->tag = self::$TAG;
            $Status->is_logged_in = self::$IS_LOGGED_IN;
            $Status->role_id = Role::getRolesByName(self::$ROLE_ID)->id;
            $Status->save();
        }
    }

    /**
     * finds a Driver using phone number and Vendor code
     * @param string $vendorCode
     * @param string $column
     * @param string $value
     * @return mixed
     */
    public static function findByUserAndColumn(string $column, string $value)
    {
        return self::with(['role'])
            ->where($column, $value)
            ->first();
    }

    /**
     * @param int $userId
     * @param int $value
     */
    protected static function selectIsLoggedIn(int $userId,int $value = 0)
    {
        $user  = self::find($userId);
        $user->is_logged_in = $value;
        $user->save();
    }

}
