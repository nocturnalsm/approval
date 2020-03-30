<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Winz\NumberGenerator;

class Approval extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['status'];

    public function responses()
    {
        return $this->hasMany("NocturnalSm\Approval\Entities\ApprovalResponse");
    }    
    public function approvers()
    {
        return $this->hasMany("NocturnalSm\Approval\Entities\PolicyApprover","policyapproval_id","policy_id");
    }    
    public function model()
    {
        return $this->morphTo("model");
    }
    public function requester()
    {
        return $this->morphTo("requester");
    }
    public function policy()
    {
        return $this->belongsTo('NocturnalSm\Approval\Entities\PolicyApproval','policy_id','id');
    }        
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }    
}
