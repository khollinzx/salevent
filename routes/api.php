<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/', 'Controller@welcome');

Route::group(['middleware' => ['set-header'], 'prefix' => 'v1'], function(){

    Route::get('/', 'Controller@welcome');

    /**
     * Onboard section
     */
    Route::group(['prefix' => 'auth'], function (){
        Route::post('login', 'Auth\StaffAuthController@staffLogin');
    });

//    Route::group(['middleware' => ['access-control']], function () {

        Route::group(['middleware' => ['auth:api']], function() {

            /**
             * Category Section
             */
            Route::group(['prefix' => 'categories'], function () {
                Route::post('new', 'CategoryController@createNewCategory');
                Route::get('all/pull', 'CategoryController@getAllCategories');
                Route::get('{category_id}', 'CategoryController@getCategoryById');
                Route::patch('update/{category_id}', 'CategoryController@updateCategoryById');
            });

            /**
             * Suppliers section
             */
            Route::group(['prefix' => 'suppliers'], function () {
                Route::post('new', 'SupplierController@createNewSupplier');
                Route::get('all/pull', 'SupplierController@getAllSuppliers');
                Route::get('{supplier_id}', 'SupplierController@getSupplierById');
                Route::patch('update/{supplier_id}', 'SupplierController@updateSupplierById');
            });

            Route::group(['prefix' => 'cashBook'], function () {
                Route::post('new', 'CashBookController@createNewCashBook');
                Route::get('all/pull', 'CashBookController@getAllCashBooks');
                Route::get('user', 'CashBookController@getCurrentCashBookForUser');
                Route::get('user/amount', 'CashBookController@getSumAmount');
//                Route::patch('update/{supplier_id}', 'CashBookController@updateSupplierById');
            });

            Route::group(['prefix' => 'products'], function () {
                Route::post('new', 'ProductController@createNewProduct');
                Route::post('new/bulk', 'ProductController@createNewBulkProduct');
                Route::get('all/pull', 'ProductController@getAllAvailableAndLowOnStockProducts');
                Route::get('{product_id}', 'ProductController@getProductById');
                Route::patch('topUp/{product_id}', 'ProductController@topUpProductById');
                Route::patch('update/{product_id}', 'ProductController@updateProductById');
                Route::patch('update/{product_id}/bulk', 'ProductController@updateBulkProductById');
            });

            Route::group(['prefix' => 'carts'], function () {
                Route::post('new/{product_id}', 'CartController@addItemToCart');
//                Route::post('new/bulk', 'ProductController@createNewBulkProduct');
                Route::get('all/pull', 'CartController@getAllCartItems');
                Route::post('add/{product_id}', 'CartController@incrementByOne');
                Route::post('remove/{product_id}', 'CartController@decrementByOne');
//                Route::patch('update/{product_id}', 'ProductController@updateProductById');
//                Route::patch('update/{product_id}/bulk', 'ProductController@updateBulkProductById');
            });

            Route::group(['prefix' => 'order'], function () {
                Route::post('create', 'OrderController@processCartItemToOrder');
                Route::get('pull/all', 'OrderController@getAllOrderedItems');
                Route::get('pull/{orderId}/items', 'OrderController@getOrderedItemWithDetails');
            });

        });
//    });
});

//
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
