<?php

namespace NocturnalSm\Approval;

use NocturnalSm\Approval\Entities\Policy;
use NocturnalSm\Approval\Entities\PolicyApprover;
use NocturnalSm\Approval\Entities\Approval;
use NocturnalSm\Approval\Entities\ApprovalResponse;

class ApprovalManager
{    
    public function getPolicy($name, $approval = "")
    {
        $policies = config('approval.policies');         
        $data = $this->getData($name);                
        if ($data){
            $policy = $policies[$data["class"]];
            foreach ($data["approvals"] as $key=>$appr){
                $policy["approvals"][$key]["enabled"] = $appr == "Y";
            }
            if ($approval == ""){
                return $policy;
            }
            else {
                return isset($policy["approvals"][$approval]) ?
                            $policy["approvals"][$approval] : false;
            }            
        }        
    }
    public function make($model, $name, $approval, $requester, $params = Array())
    {        
        $data = Policy::where("name", $name)->first();
        $policy = $data->approvals()
                       ->where("approval",$approval)->first();                
        $new = new Approval(["status" => Approval::STATUS_PENDING]);                                                        
        $new->policy()->associate($policy);
        $new->requester()->associate($requester);

        if (isset($policy["onSubmitApproval"])
            && is_callable($policy["onSubmitApproval"])){
            $onSubmit = $policy["onSubmitApproval"]($new, $params);
        }
        else {
            $onSubmit = $model->onSubmitApproval($new, $params);
        }
        if ($onSubmit 
            && get_class($onSubmit) == 'NocturnalSm\Approval\Entities\Approval'){
            $new = $onSubmit;
        }
        $model->approvals()->pending()->update(["status" => Approval::STATUS_CANCELLED]);        
        $model->approvals()->save($new);

        if (isset($policy["afterSubmitApproval"])
            && is_callable($policy["afterSubmitApproval"])){
            $afterSubmit = $policy["afterSubmitApproval"]($new, $params);
        }
        else {
            $afterSubmit = $model->afterSubmitApproval($new, $params);
        }
        if ($afterSubmit){
            return $afterSubmit;
        }
    }
    public function respond($approval, $approver, $status, $params = Array())
    {
        // save response
        $response = new ApprovalResponse;
        $assigned = $approver->approvalAssigned()
                             ->where("policyapproval_id", $approval->policy_id)
                             ->first();
        $response->approver_id = $assigned->id;
        $response->response = $status;
        $response->approver()->associate($approver,"model");
        
        $policyApproval = $approval->policy()->select("policy_id","approval")->first();        
        $data = Policy::find($policyApproval->policy_id);      
        $policy = $this->getPolicy($data->name, $policyApproval->approval);
        
        $model = $approval->model()->first();
        if (isset($policy["onResponded"])
            && is_callable($policy["onResponded"])){
            $onResponded = $policy["onResponded"]($response, $params);
        }
        else {
            $onResponded = $model->onApprovalResponded($response, $params);
        }
        if ($onResponded 
            && get_class($onResponded) == 'NocturnalSm\Approval\Entities\ApprovalResponse'){
            $response = $onResponded;
        }
        $approval->responses()->save($response);

        if (isset($policy["afterResponded"])
            && is_callable($policy["afterResponded"])){
            $afterResponded = $policy["afterResponded"]($response);
        }
        else {
            $afterResponded = $model->afterApprovalResponded($response);
        }
        if ($afterResponded){
            return $afterResponded;
        }

        // check approval completion
        if (isset($policy["customCheckApproval"])
            && is_callable($policy["customCheckApproval"])){
            $check = $policy["customCheckApproval"]($approval, $response, $policy);
        }
        else {
            $check = $this->checkApproval($approval, $response, $policy);                        
        }
        
        if ($check){
            $approval->update(["status" => $check]);            
            $this->updateModel($approval->model()->first(), $policy, $check);
            if (isset($policy["onApprovalComplete"])
                && is_callable($policy["onApprovalComplete"])){
                $policy["onApprovalComplete"]($approval);
            }
            else {
                $model->onApprovalComplete($approval);
            }
        }        
    }
    private function checkApproval($approval, $response, $policy)
    {
        if ($response->response == ApprovalResponse::STATUS_REJECT){
            return Approval::STATUS_REJECTED;
        }
        else if ($response->response == ApprovalResponse::STATUS_APPROVE){
            $approver = PolicyApprover::find($response->approver_id);
            $check = PolicyApprover::select("policy_approvers.id","responses.response")
                            ->leftJoinSub(Approval::join("approval_responses","approvals.id","=","approval_responses.approval_id")
                                                 ->where("approvals.id", $approval->id)
                                                 ->select("approver_id","response"),
                                        "responses",
                                        function($join){
                                            $join->on("responses.approver_id", "=","policy_approvers.id");               
                                        })
                            ->where("policyapproval_id", $approval->policy_id)
                            ->where("responses.response", NULL);
            if ($approver->level){
                $check = $check->where("level", $approver->level);
            }
            if ($check->count() == 0){                
                if ($approver->level){
                    $nextLevel = PolicyApprover::where("level", intval($approver->level) + 1)->count();
                    if ($nextLevel == 0){
                        return Approval::STATUS_APPROVED;
                    }
                    else if ($nextLevel > 0){
                        if (isset($policy["onLevelComplete"])
                            && is_callable($policy["onLevelComplete"])){
                            $policy["onLevelComplete"]($response, $approver->level);
                        }
                        else {
                            $model = $approval->model()->first();
                            $model->onLevelComplete($response, $approver->level);
                        }
                    }
                }
                else {
                    return Approval::STATUS_APPROVED;
                }
            }
        }
        return false;
    }
    private function updateModel($model, $policy, $status)
    {
        $statusField = config('approval.status_field');
        $model->setApprovable(false);
        if ($model->$statusField == $policy["pendingState"]){
            if ($status == Approval::STATUS_APPROVED){
                if (isset($policy["onApproved"])){
                    if (is_callable($policy["onApproved"])){
                        $policy["onApproved"]($model);
                    }
                }
                if (isset($policy['approvedState'])){
                    $model->$statusField = $policy["approvedState"];
                    $model->save();
                }
                if (isset($policy["afterApproved"])){
                    if (is_callable($policy["afterApproved"])){
                        $policy["afterApproved"]($model);
                    }
                }                
            }
            else if ($status == Approval::STATUS_REJECTED){
                if (isset($policy["onRejected"])){
                    if (is_callable($policy["onRejected"])){
                        $policy["onRejected"]($model);
                    }
                }
                if (isset($policy['rejectedState'])){
                    $model->$statusField = $policy["rejectedState"];
                    $model->save();
                }
                if (isset($policy["afterRejected"])){
                    if (is_callable($policy["afterRejected"])){
                        $policy["afterRejected"]($model);
                    }
                }                
            }            
        }

    }
    private function getData($name)
    {        
        $cache = cache("approval." .config('approval.policy_cache_name',"policies"),"");
        if ($cache != ""
            && isset($cache[$name])){
                return $cache[$name];            
        }
        $policy = Policy::where("name", $name)->first();
        if ($policy){
            $class = $policy->value("class");
            $data =  $policy->approvals()->pluck("enabled","approval");         
            if ($data){
                $cache = array_merge(is_array($cache) ? $cache : [], [$name => ["approvals" => $data, "class" => $class]]);
                cache(["approval." .config('policy_cache_name',"policies") => $cache], config('policy_cache_expired_time', 60));
                return ["approvals" => $data, "class" => $class];
            }
        }
        return false;       
    }        
}