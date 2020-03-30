<?php

namespace  NocturnalSm\Approval\Traits;

use DB;
use Approval;

trait HasApproval
{        
    public function approvalRequested()
    {
        return $this->morphMany('NocturnalSm\Approval\Entities\Approval','requester');
    }
    public function approvalResponded()
    {
        return $this->morphMany('NocturnalSm\Approval\Entities\ApprovalResponse','model');
    }
    public function approvalAssigned()
    {
        return $this->morphMany('NocturnalSm\Approval\Entities\PolicyApprover','model');
    }
    public function canApprove($model)
    {
        $approval = $model->approvals()->pending()->first();
        if ($approval){
            $policy = $approval->policy_id;
            // get approvers assigned for this model and type of approval
            $assigned = $this->approvalAssigned()
                             ->select("id","level")
                             ->where("policyapproval_id", $policy)
                             ->orderBy("level","id")
                             ->get();
            if ($assigned){
                // loop each approver, if no responses have been made return true;
                foreach($assigned as $assign){
                    $level = $assign->level;
                    $responses = $assign->responses()
                                        ->where("approval_id", $approval->id);
                    if (!$level){
                        if ($responses->count() == 0){
                            return true;
                        }
                    }    
                    else {
                        $level = intval($level);                        
                        if ($level == 1){
                            if ($responses->count() == 0){
                                return true;
                            }
                        }
                        else if ($level > 1){
                            // check if all response for previous level has been made
                            $prevLevel = DB::table("policy_approvers")->select("policy_approvers.id","responses.response")
                                                        ->leftJoinSub(DB::table("approvals")->join("approval_responses","approvals.id","=","approval_responses.approval_id")
                                                                            ->where("approvals.id", $approval->id)
                                                                            ->select("approver_id","response"),
                                                                    "responses",
                                                                    function($join){
                                                                        $join->on("responses.approver_id", "=","policy_approvers.id");               
                                                                    })
                                                        ->where("policyapproval_id", $policy)
                                                        ->where("level", $level - 1)
                                                        ->where("responses.response", NULL);
                            return $prevLevel->count() == 0;
                        }                        
                    }
                }
            }   
        }
        return false;
    }
    public function respondApproval($model, $status, $params = Array())
    {
        if ($this->canApprove($model)){
            $approval = $model->approvals()->pending()->first();
            return Approval::respond($approval,$this,$status, $params);
        }
        return false;
    }
}