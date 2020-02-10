<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;

class ModelHasPolicy extends Model
{
    public $incrementing = true;

    public function approver()
    {
        return $this->morphTo();
    }      
}
