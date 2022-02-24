<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeColumnsCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('cashier_stripe_key')->nullable();
            $table->string('cashier_stripe_secret')->nullable();
            $table->string('cashier_stripe_curreny')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'cashier_stripe_key',
                'cashier_stripe_secret',
                'cashier_stripe_curreny',
            ]);
        });
    }
}
