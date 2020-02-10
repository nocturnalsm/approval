<?php

namespace  NocturnalSm\Approval\Traits;

use NocturnalSm\Approval\Entities\Approval as ApprovalDB;
use NocturnalSm\Approval\Traits\PendingUpdate;
use NocturnalSm\Approval\Traits\Approvable;
use NocturnalSm\Approval\Entities\PendingUpdateScope;
use Approval;

trait HasApproval
{        
    use PendingUpdate, Approvable;

    public static function bootHasApproval(): void
    {                          
        static::creating(function ($model) {            
            return $model->beforeCreate();
        });
        static::created(function ($model) {
            return $model->afterCreate();
        });
        static::updating(function ($model) {         
            return $model->beforeUpdate();
        });
        static::addGlobalScope(new PendingUpdateScope);
    }        
    public function approvals()
    {
        return $this->morphMany('NocturnalSm\Approval\Entities\Approval','model');
    }    
    public function cancelApprovals()
    {
        $this->approvals()->pending()->update(["status" => ApprovalDB::STATUS_CANCELLED]);
    }        
    protected function beforeCreate()
    {
        $policy = $this->getPolicy();
        $approval = isset($policy["approvals"]["create"]) ? $policy["approvals"]["create"] : false;
        if ($approval){
            $enabled = isset($approval["enabled"]) ? $approval["enabled"] : true;
            if ($enabled){
                $statusField = $policy["statusField"];
                $this->$statusField = $approval["pendingState"];
            }    
        }
    }
    protected function afterCreate()
    {
        $policy = $this->getPolicy();
        $approval = isset($policy["approvals"]["create"]) ? $policy["approvals"]["create"] : false;
        if ($approval){
            $enabled = isset($approval["enabled"]) ? $approval["enabled"] : true;
            if ($enabled){
                $approval = new ApprovalDB(["approval" => "create",
                                            "user_id" => auth()->user()->id,
                                            "status" => ApprovalDB::STATUS_PENDING]);
                $this->approvals()->save($approval);
                //$approvers = $app["approvers"];
                //$approval->responses()->createMany($approvers);
            }
        }
    }    
    protected function beforeUpdate()
    {
        $policy = $this->getPolicy();
        $approval = isset($policy["approvals"]["update"]) ? $policy["approvals"]["update"] : false;
        if ($approval){
            $enabled = isset($approval["enabled"]) ? $approval["enabled"] : true;
            if ($enabled){
                $statusField = $policy["statusField"];
                $checkState = true;
                if (isset($approval['state'])){
                    if (is_string($approval['state'])){
                        $checkState = $approval['state'] == $this->$statusField;
                    }
                    if (is_array($approval["state"])){
                        $checkState = in_array($this->$statusField, $approval['state']); 
                    }
                }
                if ($checkState || $this->$statusField == $approval["pendingState"]){
                    $this->cancelApprovals();
                    $this->pendingUpdate();      
                    $checkState = true;
                    if (isset($approval['state'])){
                        if (is_string($approval['state'])){
                            $checkState = $approval['state'] == $this->$statusField;
                        }
                        if (is_array($approval["state"])){
                            $checkState = in_array($this->$statusField, $approval['state']); 
                        }
                    }              
                    if ($checkState){
                        $this->$statusField = $approval["pendingState"];
                        $approval = new ApprovalDB(["approval" => "update",
                                                "user_id" => auth()->user()->id,
                                                "status" => ApprovalDB::STATUS_PENDING]);
                        $this->approvals()->save($approval);
                        //$approvers = $app["approvers"];
                        //$approval->responses()->createMany($approvers);
                    }                        
                }
            }
        }
    }         
    public function delete()
    {
        $policy = $this->getPolicy();
        $approval = isset($policy["approvals"]["delete"]) ? $policy["approvals"]["delete"] : false;
        if ($approval != false){
            $enabled = isset($approval["enabled"]) ? $approval["enabled"] : true;                
            $statusField = $policy["statusField"];
            $checkState = true;
            if (isset($approval['state'])){
                if (is_string($approval['state'])){
                    $checkState = $approval['state'] == $this->$statusField;
                }
                if (is_array($approval["state"])){
                    $checkState = in_array($this->$statusField, $approval['state']); 
                }
            }
            if ($enabled && $checkState){                    
                $this->cancelUpdate();
                $this->cancelApprovals();
                $this->$statusField = $approval["pendingState"];
                $this->save();
                $approval = new ApprovalDB(["approval" => "delete",
                                            "user_id" => auth()->user()->id,
                                            "status" => ApprovalDB::STATUS_PENDING]);
                $this->approvals()->save($approval);
                return true;
            }
        }
        parent::delete();
    }
    public function canApprove()
    {
        $policy = $this->getPolicy();                
        $pendingStates = $policy["pendingStates"];                
        return in_array($this->last_state, $pendingStates);
    }
    public function approvable($approval)
    {
        $policy = $this->getPolicy();
        $enabled = false;
        $approval = isset($policy["approvals"][$approval]) ? $policy["approvals"][$approval] : false;        
        if ($approval){
            $enabled = isset($approval["enabled"]) ? $approval["enabled"] : true;   
        }
        return $enabled;
    }    
}