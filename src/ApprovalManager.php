<?php

namespace NocturnalSm\Approval;

use NocturnalSm\Approval\Entities\ApprovalPolicy;

class ApprovalManager
{
    public function getPolicy($model)
    {
        $policies = config('approval.policies');         
        $data = $this->getApproval($model);        
        if ($data){
            $policy = $policies[$data["class"]];
            foreach ($data["approvals"] as $key=>$appr){
                $policy["approvals"][$key]["enabled"] = $appr == "Y";
            }
            return $policy;
        }        
    }
    private function getApproval($model, $approval = "")
    {        
        $company = company();
        $cache = cache("company." .$company->id .".policies","");
        if ($cache != ""){
            if (isset($cache[$model])){
                if ($approval == ""){
                    return $cache[$model];
                }
                else {
                    if (isset($cache[$model]["approvals"][$approval])){
                        return $cache[$model]["approvals"][$approval];
                    }
                }
            }
        }
        $class = ApprovalPolicy::where("model", $model)->value("class");
        if (!$class){
            $class = "default";
        }
        $data =  $company->policies()->select('approval','enabled')
                        ->where("model", $model)->get()
                        ->pluck("enabled","approval");               
        if ($data){
            $cache = array_merge(is_array($cache) ? $cache : [], [$model => ["approvals" => $data, "class" => $class]]);
            cache(["company." .$company->id .".policies" => $cache], 60);
            if ($approval == ""){
                return ["approvals" => $data, "class" => $class];
            }
            else {
                return isset($data[$approval]) ? $data[$approval] : false;
            }
        }       
    }    
}