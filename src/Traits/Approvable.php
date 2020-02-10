<?php

namespace  NocturnalSm\Approval\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Approval;

trait Approvable
{            
    private $excludedFields = Array();    
    private $policy;
  
    public function getPolicy()
    {        
        if (!isset($this->policy)){
            $this->policy = Approval::getPolicy(get_class($this));
        }
        return $this->policy;
    }
    public function enableApproval($approval)
    {        
        if (isset($this->policy["approvals"][$approval])){
            $this->policy["approvals"][$approval]["enabled"] = true;
        }
        else {
            throw new \Exception('Approval not found');
        }
    }
    public function disableApproval($approval)
    {
        if (isset($this->policy["approvals"][$approval])){
            $this->policy["approvals"][$approval]["enabled"] = false;
        }
        else {
            throw new \Exception('Approval not found');
        }
    }        
}