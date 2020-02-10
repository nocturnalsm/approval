<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolicyHasApprovers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('policy_has_approvers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('policy_id')->index();
            $table->unsignedInteger('model_id')->index();
            $table->string('model_type')->index();
            $table->smallInteger('level')->index();
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
        Schema::dropIfExists('policy_has_approvers');
    }
}
