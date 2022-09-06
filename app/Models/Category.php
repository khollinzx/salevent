<?php

namespace App\Models;

use App\Services\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function products(): Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function user(): Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Finds a category name by Id
     * @param int $categoryId
     * @return mixed
     */
    public static function findCategoryById(int $categoryId){
        return self::find($categoryId);
    }

    /**This finds an existing name
     * @param string $name
     * @return mixed
     */
    public static function findCategoryByName(string $name){
        return self::where('name',ucwords($name))->first();
    }

    /**Fetches all Categories
     * @return Category[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function fetchAllCategories()
    {
        return self::orderByDesc('id')
            ->get();
    }

    /**This method create new Category name
     * by checking if the category name exist
     * @param int $user_id
     * @param string $name
     * @return Model
     */
    public function initializeNewCategory(int $user_id, string $name):Model
    {
        return Helper::runModelCreation(new self(),
            [
                'name' => $name,
                'user_id' => $user_id
            ]
        );
    }

    /**This method updates and exist category by Id
     * @param int $category_id
     * @param string $name
     * @return Model
     */
    public function updateCategoryWhereExist(Model $model, string $name):Model
    {
        return Helper::runModelUpdate($model,
            [
                'name' => $name
            ]);
    }

}
