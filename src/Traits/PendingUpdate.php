<?php

namespace  NocturnalSm\Approval\Traits;

use NocturnalSm\Approval\Entities\Update;

trait PendingUpdate
{    
    protected $pendingDataStore;
    
    protected static function bootPendingUpdate()
    {        
        static::retrieved(function ($model) {            
            return $model->append('pendingData');
        });        
    }
    public function updates()
    {
        return $this->morphMany('NocturnalSm\Approval\Entities\Update','model');
    }        
    public function cancelPending()
    {
        $this->updates()->pending()->update(["status" => Update::STATUS_CANCELLED]);
    }  
    public function pendingUpdate()
    {                
        $original = $this->getOriginal();
        $dirty = $this->getDirty();
        $changes = [];
        $excludedFields = ["id","created_at","updated_at"];
        if (isset($this->excludedFields)){
            $excludedFields = array_merge($excludedFields, $this->excludedFields);
        }
        foreach ($dirty as $key=>$value){
            if (!in_array($key, $excludedFields)){						
                $changes[$key] = [
                    "original" => $original[$key],
                    "changes" => $value
                ];
                $this->$key = $original[$key];
            }
        }
        $this->cancelPending();
        $update = new Update;
        $update->status = Update::STATUS_PENDING;
        $update->data = json_encode($changes);        
        $this->updates()->save($update);
    }
    public function applyUpdate()
    {
        $modified = $this->pendingValues;
        foreach ($modified as $key=>$mod){
            $model->$key = $mod->changes;
        }					
        $this->updates()->pending()->update(["status" => Update::STATUS_APPLIED]);
    } 
    public function cancelUpdate()
    {
        $this->updates()->pending()->update(["status" => Update::STATUS_CANCELLED]);
    }
    public function getPendingDataAttribute()
    {        
        if (!isset($this->pendingDataStore)){            
            $pending = $this->updates()->pending()->latest()->first();            
            if (isset($pending)){                                
                $pendingData = json_decode($pending->data);
            }
            else {                                
                $pendingData = json_decode("{}");
            }                    
            $this->pendingDataStore = $pendingData;            
        }
        return $this->pendingDataStore;
    }
    public function hasPendingValue($key)
    {
        return isset($key) 
                && isset($this->pendingData->$key);
    }
    public function getPendingValue($key)
    {
        return isset($key) 
                && isset($this->pendingData->$key) ? $this->pendingData->$key->changes : false;
    }
    public function usePending()
    {
        $pending = $this->pendingData;        
        if ($pending){
            foreach ($pending as $key=>$values){
                $this->$key = $values->changes;
            }
        }        
        return $this;
    }
}