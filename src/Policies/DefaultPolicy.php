<?php

namespace NocturnalSm\Approval\Policies;

use NocturnalSm\Approval\Contracts\ApprovalPolicy;

class DefaultPolicy implements ApprovalPolicy
{    
    public static function getConfig()
    {        
        return [
            'default' => 
                ['pendingStates' => ["new","updated","deleted"],
                'approvals' => [
                    'create' => [
                        'pendingState' => 'new',                        
                        'approvedState' => 'active',
                        'afterRejected' => function($model){
                            $model->setApprovable(false);                            
                            $model->delete();
                        }
                    ],
                    'update' => [
                        'state' => ['active','updated'],
                        'pendingState' => 'updated', 
                        'approvedState' => 'active',
                        'onApproved' => function($model){                            
                            $model->applyUpdate();
                        },
                        'rejectedState' => 'active',
                        'afterRejected' => function($model){
                            $model->cancelUpdate();
                        }
                    ],
                    'delete' => [           
                        'state' => ['active','updated'],
                        'pendingState' => 'deleted',
                        'rejectedState' => 'active',
                        'afterApproved' => function($model){
                            $model->setApprovable(false);                            
                            $model->delete();
                        }
                    ]
                ]
            ]
        ];
    }
}