<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;

class ApprovalResponse extends Model
{    
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVE = 'approve';
    const STATUS_REJECT = 'reject';

    public function approver()
    {
        return $this->morphTo("model");
    }   
    public function approval()
    {
        return $this->belongsTo("NocturnalSm\Approval\Entities\Approval","approval_id");
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
