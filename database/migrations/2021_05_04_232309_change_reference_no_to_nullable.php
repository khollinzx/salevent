<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeReferenceNoToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if(Schema::hasColumn('orders', 'reference_no')){
                $table->dropColumn(['reference_no', 'total', 'total_amount_paid']);
            }
            $table->integer( 'reference_no')->nullable()->after('payment_type_id');
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
            $table->integer( 'reference_no')->nullable()->after('payment_type_id');
            $table->double( 'total_price')->after('reference_no');
            $table->double( 'amount_paid')->after('total_price');
            $table->double( 'balance')->after('amount_paid');
        });
    }
}
