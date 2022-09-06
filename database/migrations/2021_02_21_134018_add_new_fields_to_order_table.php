<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
                $table->integer('total_items')->after('order_code');
                $table->double('total')->after('total_items');
                $table->unsignedBigInteger('payment_type_id')->nullable()->after('total');
                $table->double('reference_no')->after('payment_type_id');
                $table->double('total_amount_paid')->after('reference_no');

                $table->foreign('payment_type_id')->references('id')->on('payment_types')->onDelete('SET NULL');
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
            $table->dropColumn('reference_no');
        });
    }
}
