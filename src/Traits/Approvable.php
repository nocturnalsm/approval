<?php

namespace  NocturnalSm\Approval\Traits;

use NocturnalSm\PendingUpdate\PendingUpdate;
use NocturnalSm\Approval\Entities\PendingUpdateScope;
use Approval;

trait Approvable
{        
    use PendingUpdate;

    private $approvable;
    
    public static function bootApprovable(): void
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
    public function getPolicyName()
    {
        return get_class($this);
    }
    public function getRequester()
    {
        if (auth()){
            return auth()->user();
        }
    }
    public function setApprovable($value = true)
    {
        $this->approvable = $value;
    }
    public function approvable()
    {
        if (!isset($this->approvable)){
            $this->setApprovable();
        }
        return $this->approvable;
    }
    protected function beforeCreate()
    {                
        if ($this->approvable()){            
            $data = Approval::getPolicy($this->getPolicyName(),"create");            
            if ($data["enabled"]){
                $statusField = config('approval.status_field');
                $this->$statusField = $data["pendingState"];
            }
        }
    }
    protected function afterCreate()
    {
        if ($this->approvable()){
            $data = Approval::getPolicy($this->getPolicyName(),"create");
            if ($data["enabled"]){            
                $approval = Approval::make($this, $this->getPolicyName(), "create", $this->getRequester());        
            }
        }
    }    
    protected function beforeUpdate()
    {                
        if ($this->approvable()){
            $data = Approval::getPolicy($this->getPolicyName(),"update");                        
            if ($data["enabled"]){
                $statusField = config('approval.status_field');
                $checkState = true;
                if (isset($data['state'])){
                    if (is_string($data['state'])){
                        $checkState = $data['state'] == $this->$statusField;
                    }
                    if (is_array($data["state"])){
                        $checkState = in_array($this->$statusField, $data['state']); 
                    }
                }
                if ($checkState || $this->$statusField == $data["pendingState"]){                    
                    $this->excludedFields = config('approval.status_field'); 
                    $this->pendingUpdate();      
                    $checkState = true;
                    if (isset($data['state'])){
                        if (is_string($data['state'])){
                            $checkState = $data['state'] == $this->$statusField;
                        }
                        if (is_array($data["state"])){
                            $checkState = in_array($this->$statusField, $data['state']); 
                        }
                    }              
                    if ($checkState){
                        $this->$statusField = $data["pendingState"];
                        $approval = Approval::make($this, $this->getPolicyName(), "update", $this->getRequester());
                    }                        
                }
            }
        }
    }         
    public function delete()
    {        
        if ($this->approvable()){
            $data = Approval::getPolicy($this->getPolicyName(),"delete");
            $statusField = config('approval.status_field');
            $checkState = true;
            if (isset($data['state'])){
                if (is_string($data['state'])){
                    $checkState = $data['state'] == $this->$statusField;
                }
                if (is_array($data["state"])){
                    $checkState = in_array($this->$statusField, $data['state']); 
                }
            }
            if ($data["enabled"] && $checkState){                    
                $this->cancelUpdate();
                $this->$statusField = $data["pendingState"];
                $this->save();
                $approval = Approval::make($this, $this->getPolicyName(), "delete", $this->getRequester());
                return true;
            }
        }
        parent::delete();
    }
    public function needApproval()
    {
        $policy = Approval::getPolicy($this->getPolicyName());  
        if ($policy){              
            $pendingStates = $policy["pendingStates"];
            $statusField = config('approval.status_field');                
            return in_array($this->$statusField, $pendingStates);
        }
        return false;
    }    
    public function onSubmitApproval($approval, $params = Array())          
    {
        return $approval;
    }
    public function afterSubmitApproval($approval, $params = Array())          
    {
        return $approval;
    }
    public function onApprovalResponded($response, $params = Array())
    {
        return $response;
    }
    public function afterApprovalResponded($response, $params = Array())
    {
        return $response;
    }
    public function onLevelComplete($response, Integer $level)
    {
        return $response;
    }
}