<?php

namespace NocturnalSm\Approval\Commands;

use Illuminate\Console\Command;
use NocturnalSm\Approval\Entities\Policy;
use NocturnalSm\Approval\Entities\PolicyApproval;

class CreatePolicy extends Command
{
    protected $signature = 'approval:create-policy 
                {name : The name of the policy} 
                {class : The name of the class}
                {approval? : The approval types}';
                

    protected $description = 'Create a policy';

    public function handle()
    {
        $policyClass = app(Policy::class);

        $policy = $policyClass::firstOrCreate(['name' => $this->argument('name'), 'class' => $this->argument('class')]);
        $argument = ['create','update','delete'];
        if ($this->argument('approval')){
            $argument = explode("|", $this->argument('approval'));
        }
        if (in_array('create', $argument)){
            $approvals[] = [
                "policy_id" => $policy->id,
                "approval" => 'create',
                "enabled" => 'Y'
            ];
        }
        if (in_array('update', $argument)){
            $approvals[] = [
                "policy_id" => $policy->id,
                "approval" => 'update',
                "enabled" => 'Y'
            ];
        }
        if (in_array('delete', $argument)){
            $approvals[] = [
                "policy_id" => $policy->id,
                "approval" => 'delete',
                "enabled" => 'Y'
            ];
        }
        $approvals = PolicyApproval::create($approvals);
        $this->info("Policy `{$policy->name}` created");
    }
}
