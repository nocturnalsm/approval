<?php

namespace NocturnalSm\Approval\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use NocturnalSm\Approval\Entities\PolicyApprover;

class CreateApprover extends Command
{
    protected $signature = 'approval:create-approver
        {name : The name of the policy}
        {approver : The name of the approver}
        {approval? : The name of the approval}
        {level? : approval level}';

    protected $description = 'Create an approver';

    public function handle()
    {
        $approverModel = config('approval.approver_model','App\User');
        try {        
            $approver = $approverModel::where("name", $this->argument('approver'))->firstOrFail();
            $policy = Policy::where("name", $this->argument('name'))->firstOrFail();
            $policyApproval = PolicyApproval::where("policy_id", $policy->id);
            $approval = $this->argument('approval') ?? '';
            if ($approval != ""){
                $policyApproval = $policyApproval->whereIn("approval", explode("|", $approval));
            }
            foreach($policyApproval->get() as $item){
                $new = PolicyApprover;
                $new->policy_id = $policyApproval->id;
                $new->model_type = $approverModel;
                $new->model_id = $approver->id;
                if ($this->argument('level')){
                    $new->level = $this->argument('level');
                }
                $new->save();
            }
            $this->info("Approver `" .$this->argument('approver') ."` created");
        }
        catch (ModelNotFoundException $e){
            throw new Exception('Object not found: ' .$e->getMessage());
        }
        catch (\Exception $e){
            $this->info($e->getMessage());
        }
    }
}
