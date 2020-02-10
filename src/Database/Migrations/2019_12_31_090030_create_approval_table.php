<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('model_id')->index();
            $table->string('model_type')->index();
            $table->unsignedInteger('policy_id')->index();
            $table->string('requester_type')->index();
            $table->unsignedInteger('requester_id')->index();
            $table->string('ticket_number',20)->index();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        Schema::create('approval_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('approval_id')->index();
            $table->string('model_type')->index();
            $table->unsignedInteger('model_id')->index();
            $table->string('response')->nullable();
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
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('approval_responses');
    }
}
