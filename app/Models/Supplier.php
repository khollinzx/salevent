<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function user(): Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function findById(int $supplierId)
    {
        return self::find($supplierId);
    }

    public static function findByColumnAndField($column, $field)
    {
        return self::with(['user'])
            ->where($column, $field)
            ->first();
    }

    /**Fetches all Suppliers
     * @return Supplier[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function fetchAllSuppliers()
    {
        return self::orderByDesc('id')
            ->get();
    }

    public static function findSupplierByName(string $name)
    {
        return self::where('name', ucwords($name))
            ->first();
    }

    /**This method create new Supllier
     * by checking if the category name exist
     * @param int $user_id
     * @param array $fields
     * @return Model
     */
    public function initializeNewSupplier(int $user_id, array $fields):Model
    {
        $supplier = self::findSupplierByName($fields['name']);
        if(!$supplier)
            return Helper::runModelCreation(new self(),
                [
                    'name' => $fields['name'],
                    'email' => $fields['email'],
                    'phone' => $fields['phone'],
                    'address' => $fields['address'],
                    'user_id' => $user_id
                ]
            );
    }

    /**This method updates and exist supplier by Id
     * @param int $supplier_id
     * @param array $fields
     * @return Model
     */
    public function updateSupplierWhereExist(int $supplier_id,array $fields):Model
    {
        $supplier = self::findById($supplier_id);
        if($supplier)
            return Helper::runModelUpdate($supplier,
                [
                    'name' => $fields['name'],
                    'email' => $fields['email'],
                    'phone' => $fields['phone'],
                    'address' => $fields['address'],
                ]);
    }


}
