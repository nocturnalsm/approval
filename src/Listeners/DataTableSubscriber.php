<?php

namespace NocturnalSm\Approval\Listeners;
use Approval;

class DataTableSubscriber
{    
    /**
     * Handle datasource get events
     */
    public function getDataSource($event) {
        $enableApproval = Approval::isEnabled($event->module);
        $policy = Approval::getPolicy($event->module);
        $statusField = isset($policy["statusField"]) ? $policy["statusField"] : "last_state";
        if ($enableApproval){
            $event->dataSource->addSelect($statusField);
        }         
    }
    /**
     * Handle build data events.
     */
    public function buildData($event) {
        $approvalEnabled = Approval::isEnabled($event->module);
        $policy = Approval::getPolicy($event->module);
        $statusField = isset($policy["statusField"]) ? $policy["statusField"] : "last_state";
        if ($approvalEnabled){
            $raw = config('datatables.columns.raw');
            config(['datatables.columns.raw' => array_merge([$statusField], $raw)]);            
            $event->dataTable->editColumn($statusField, function($data) use ($statusField){
                return view('approval::components.status', ["status" => $data->$statusField]);
            });
        }
    }
    /**
     * Handle get columns events.
     */
    public function getColumns($event) {
        $approvalEnabled = Approval::isEnabled($event->module);
        $policy = Approval::getPolicy($event->module);
        $statusField = isset($policy["statusField"]) ? $policy["statusField"] : "last_state";
        if ($approvalEnabled){
           $event->columns = array_merge([['data' => $statusField, 
                                            'name' => $statusField, 
                                            'title' => __('titles.status'), 
                                            'attributes' => 
                                                ['width' => '8%','data-title' => __('titles.status')]
                                            ]
                                          ], 
                                          $event->columns);                          
        }
    }
    /**
     * Handle build html events.
     */
    public function buildHtml($event) {
        
    }
    /**
     * Handle build search events.
     */
    public function buildSearch($event) {
        $approvalEnabled = Approval::isEnabled($event->module);
        $policy = Approval::getPolicy($event->module);
        $statusField = isset($policy["statusField"]) ? $policy["statusField"] : "last_state";
        if ($approvalEnabled){
            $event->options[] = [
                        "searchStatus" => [
                            "enabled" => $approvalEnabled,
                            "options" => ["" => "All", "new" => __('status.new'), "active" => __('status.active'), 
                                        "updated" => __('status.updated'), "deleted" => __('status.deleted')]
                        ]
            ];
            $event->view = "approval::components.search";
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {        
        $events->listen(
            'App\Winz\Events\DataTable\EventGetDataSource',
            'NocturnalSm\Approval\Listeners\DataTableSubscriber@getDataSource'
        );
        $events->listen(
            'App\Winz\Events\DataTable\EventBuildData',
            'NocturnalSm\Approval\Listeners\DataTableSubscriber@buildData'
        );
        $events->listen(
            'App\Winz\Events\DataTable\EventGetColumns',
            'NocturnalSm\Approval\Listeners\DataTableSubscriber@getColumns'
        );
        $events->listen(
            'App\Winz\Events\DataTable\EventBuildHtml',
            'NocturnalSm\Approval\Listeners\DataTableSubscriber@buildHtml'
        );
        $events->listen(
            'App\Winz\Events\DataTable\EventBuildSearch',
            'NocturnalSm\Approval\Listeners\DataTableSubscriber@buildSearch'
        );
    }
}