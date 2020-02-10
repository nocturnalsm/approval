<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModelHasPolicies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_has_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('policy_id')->index();
            $table->string('model_type')->index();
            $table->unsignedInteger('model_id')->index();
            $table->string('approval',20);
            $table->string('enabled',1)->default("Y");
        });
        Schema::table('approval_policies', function (Blueprint $table){
            $table->dropColumn('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_has_policies');
    }
}
