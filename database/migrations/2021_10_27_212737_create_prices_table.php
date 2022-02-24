<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->string('product')->nullable();
            $table->boolean('livemode')->default(false);
            $table->boolean('active')->default(false);
            $table->text('metadata')->nullable();
            $table->text('description')->nullable();
            $table->string('aggregate_usage')->nullable();
            $table->string('unit_amount')->nullable();
            $table->string('unit_amount_decimal')->nullable();
            $table->string('billing_scheme')->nullable();
            $table->string('currency')->nullable();
            $table->string('interval')->nullable();
            $table->integer('interval_count')->nullable();
            $table->string('nickname')->nullable();
            $table->text('tiers')->nullable();
            $table->string('tiers_mode')->nullable();
            $table->string('transform_usage')->nullable();
            $table->integer('trial_period_days')->nullable();
            $table->string('usage_type')->nullable();
            $table->string('type')->nullable();
            $table->text('name')->nullable();
            $table->string('created')->nullable();
            $table->integer('company_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prices');
    }
}
