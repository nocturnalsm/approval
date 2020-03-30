<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    public function approvals()
    {
        return $this->hasMany('NocturnalSm\Approval\Entities\PolicyApproval','policy_id');
    }
}
