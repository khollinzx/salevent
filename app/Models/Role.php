<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public static $SUPER_ADMIN = 'Super Admin';
    public static $SALES_SUPERVISOR = 'Sales Supervisor';
    public static $SALES_REPRESENTATIVE = 'Sales Representative';

    protected $fillable = [
        'id', 'name'
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Fetches Roles model by title
     * @param string $name
     * @return mixed
     */
    public static function getRolesByName(string $name)
    {
        return self::where('name', ucwords($name))->first();
    }

    /**
     * This is initializes all default statuses
     */
    public static function initRoles()
    {
        $roles = [
            self::$SUPER_ADMIN,
            self::$SALES_SUPERVISOR,
            self::$SALES_REPRESENTATIVE
        ];

        foreach ($roles as $role) {
            self::addRoles($role);
        }
    }

    /**
     * Add a new status
     * @param string $name
     */
    public static function addRoles(string $name)
    {
        if (!self::getRolesByName(ucwords($name))) {
            $Role = new self();
            $Role->name = ucwords($name);
            $Role->save();
        }
    }

    public function initializeNewRole(string $name)
    {
        $checker = self::getRolesByName($name);
        if(! $checker)
            return Helper::runModelCreation(new self(), [
                'name' => $name
            ]);
    }


}
