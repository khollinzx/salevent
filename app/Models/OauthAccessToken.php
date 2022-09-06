<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;

class OauthAccessToken extends Model
{
    use HasFactory;

    /**
     * this is used to truncate all tables
     * @return mixed
     */
    public static function truncateTable()
    {
        self::truncate();

        exec("php artisan passport:install");
    }

    /**
     * Fetch the tokens of a particular user with respect to the guard used
     * @param int $userID
     * @param string $guard
     * @return mixed
     */
    private static function getUserTokens(int $userID, string $guard = 'staff')
    {
        return self::where('user_id', $userID)
            ->where('guard', $guard)
            ->get();
    }

    /**
     * delete a particular user access tokens
     * @param int $userID
     * @param string $guard
     */
    public static function deleteUserAccessToken(int $userID, string $guard = 'staff')
    {
        $tokens = self::getUserTokens($userID, $guard);

        if(count($tokens) > 0)
        {
            foreach ($tokens as $token)
            {
                $token->delete();
            }
        }
    }


    /**
     * this adds the provider type after creating an access token
     * All the Available Guards Are:
     * 'api', 'vendor', 'terminal_manager', 'driver'
     * @param string $bearerToken
     * @param string $guard
     */
    private static function addGuard(string $bearerToken, string $guard = 'staff')
    {
        $token = (new Parser(new JoseEncoder()))->parse($bearerToken)->claims()->all()['jti'];
        $User = self::find($token);

        if($User)
        {
            $User->guard = $guard;
            $User->save();
        }
    }

    /**
     * This i an exposed function to create access token for a particular user
     * @param Model $model
     * @param string $guard
     * @return array
     */
    public static function createAccessToken(Model $model, string $guard = 'staff') : array
    {
        self::deleteUserAccessToken($model->id, $guard);

        $accessToken = '';
        $token = $model->createToken('accessToken')->accessToken;

        if($token)
        {
            $accessToken = $token;

            self::addGuard($accessToken, $guard);
        }

        $ResponseObject['access_token'] = $accessToken;
        $ResponseObject['user_type'] = $guard;
        $ResponseObject['profile'] = $model;

        return $ResponseObject;
    }

    /**
     * decodes and fetches the oauth client key
     * @param string $bearerToken
     * @return mixed
     */
    public static function retrieveOauthProvider(string $bearerToken) : string
    {
        $Key = (new Parser(new JoseEncoder()))->parse($bearerToken)->claims()->all()['jti'];

        $value = null;
        $Provider = self::find($Key)->provider;

        $Provider ? $value = $Provider : $value = '';

        return $value;
    }

    /**
     * This is used to set the Authentication guard for a particular User
     * @param string $provider
     */
    public static function setAuthProvider(string $provider)
    {
        /**
         * Allowed Providers
         * staffs
         * Admins
         */
        Config::set('auth.guards.api.provider', $provider);
    }

}
