<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;

class PolicyApprover extends Model
{
    public function responses()
    {
        return $this->hasMany("NocturnalSm\Approval\Entities\ApprovalResponse",
                              "approver_id");
    }
}
