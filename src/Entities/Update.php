<?php

namespace  NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPLIED = 'applied';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['status','data'];    

    public static function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    public static function scopeApplied($query)
    {
        return $query->where('status', self::STATUS_APPLIED);
    }
    public static function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }      
}
