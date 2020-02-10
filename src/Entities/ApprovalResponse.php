<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;

class ApprovalResponse extends Model
{    
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVE = 'approve';
    const STATUS_REJECT = 'reject';

    protected $approverClass = "App\User";

    public function setApproverClass($class)
    {
        $this->approverClass = $class;
    }
    public function approver()
    {
        return $this->morphOne($this->approverClass,"model");
    }   
    public static function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    public static function scopeHasApprove($query)
    {
        return $query->where('status', self::STATUS_APPROVE);
    }
    public static function scopeHasReject($query)
    {
        return $query->where('status', self::STATUS_REJECT);
    }
}
