<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;

class PolicyApproval extends Model
{
    public function approvers()
    {
        return $this->morphMany('NocturnalSm\Approval\Entities\PolicyApprover');
    }
}
