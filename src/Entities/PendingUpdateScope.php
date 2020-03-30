<?php

namespace NocturnalSm\Approval\Entities;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use NocturnalSm\PendingUpdate\Update;

class PendingUpdateScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $model->leftJoinSub(
            Update::where("status",Update::STATUS_PENDING)                  
                ->where("model_id", $model->id)
                ->where("model_type", get_class($model))
                ->select("model_id","model_type","status","data"),
            'updates', function ($join) use ($model){
                $join->on("updates.model_id","=", $model->getTable() .".id");
            }
        )->addSelect("data");
    }
}