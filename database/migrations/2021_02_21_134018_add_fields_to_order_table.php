<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table){
            $table->dropColumn(['item_quantity', 'total_price']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer( 'total_items')->after('order_code');
            $table->integer( 'payment_type')->after('total_item');
            $table->integer( 'reference_no')->after('payment_type');
            $table->double('total')->after('total_item');
            $table->double('total_amount_paid')->after('payment_type');
        });
    }
}
