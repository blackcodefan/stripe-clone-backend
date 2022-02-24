<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->unique();
            $table->boolean('livemode')->default(false);
            $table->text('metadata')->nullable();
            $table->text('description')->nullable();
            $table->string('name')->nullable();
            $table->boolean('active')->nullable();
            $table->text('attributes')->nullable();
            $table->text('images')->nullable();
            $table->text('package_dimensions')->nullable();
            $table->boolean('shippable')->nullable();
            $table->string('url')->nullable();
            $table->string('statement_descriptor')->nullable();
            $table->string('unit_label')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('created')->nullable();
            $table->string('updated')->nullable();
            $table->integer('company_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
