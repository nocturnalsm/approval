<?php

return [
    'policy_cache_name' => 'policies',
    'policy_cache_expired_time' => 60,
    'status_field' => 'last_state',
    'approver_model' => "App\User",
    'policy_classes_path' => app_path() ."ApprovalPolicies"
];
