@if($status == 'new')
    <span class="badge badge-success">@lang('status.new')</span>
@elseif ($status == 'active')
    <span class="badge badge-primary">@lang('status.active')</span>
@elseif ($status == 'updated')
    <span class="badge badge-warning">@lang('status.updated')</span>
@elseif ($status == 'deleted')
    <span class="badge badge-danger">@lang('status.deleted')</span>
@elseif ($status == 'approved')
    <span class="badge badge-primary">@lang('status.approved')</span>
@elseif ($status == 'completed')
    <span class="badge badge-success">@lang('status.completed')</span>
@endif