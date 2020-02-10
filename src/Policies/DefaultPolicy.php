<?php

namespace NocturnalSm\Approval\Policies;

use NocturnalSm\Approval\Contracts\ApprovalPolicy;

class DefaultPolicy implements ApprovalPolicy
{    
    public static function getConfig()
    {        
        return [
            'default' => 
                ['statusField' => 'last_state',      
                'pendingStates' => ["new","updated","deleted"],
                'approvals' => [
                    'create' => [
                        'pendingState' => 'new',                        
                        'approvedState' => 'active',
                        'onRejected' => function($model){                            
                            $model->delete();
                        }
                    ],
                    'update' => [
                        'state' => ['active','updated'],
                        'pendingState' => 'updated', 
                        'approvedState' => 'active',
                        'rejectedState' => 'active'
                    ],
                    'delete' => [           
                        'state' => ['active','updated'],
                        'pendingState' => 'deleted',
                        'rejectedState' => 'active'
                    ]
                ]
            ]
        ];
    }
}