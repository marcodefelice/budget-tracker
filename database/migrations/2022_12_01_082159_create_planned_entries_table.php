<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planned_entries', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("uuid");
            $table->float("amount");
            $table->text("note")->nullable();
            $table->string("type");
            $table->string("planning")->nullable();
            $table->integer("waranty")->default(0);
            $table->integer('confirmed')->default(0);
     
            $table->integer('category_id')->nullable();
     
            $table->integer('model_id')->nullable();
     
            $table->integer('account_id');
     
            $table->integer('currency_id');
     
            $table->integer('payment_type')->nullable();
     
            $table->integer('geolocation_id')->nullable();
     
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('planned_entries');
    }
};
