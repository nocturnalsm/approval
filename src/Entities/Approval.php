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

    protected $fillable = ['approval','user_id','status'];

    public function responses()
    {
        return $this->hasMany("NocturnalSm\Approval\Entities\Responses");
    }
    public function policy()
    {
        return $this->belongsTo("NocturnalSm\Approval\Entities\ModelHasPolicy",'policy_id');
    }
    public function ticketNumber()
    {
        return NumberGenerator::generate(get_class($this));
    }
    public static function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    public static function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
    public static function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }    
}
