<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Db\UUIDAttribute;
use Arikaim\Core\Utils\Number;

class Events extends Model  
{
    use UUIDAttribute;

    const DISABLED = 0;
    const ACTIVE = 1;

    protected $fillable = ['name','title','extension_name','uuid','description'];
    public $timestamps = false;

    public function getEvents($extension_name)
    {
        $model = $this->where('extension_name','=',$extension_name)->get();
        if (is_object($model) == true) {
            return $model->toArray();
        }
        return [];
    }

    public function deleteEvent($name) 
    {           
        $model = $this->where('name','=',$name);
        if ($model->isEmpty() == false) {
            return $model->delete();
        }
        return false;
    }

    public function deleteEvents($extension_name)
    {
        $model = $this->where('extension_name','=',$extension_name);
        if (is_object($model) == true) {
            return $model->delete();
        }
        return false;
    }

    public function hasEvent($name, $status = null)
    {
        $model = $this->where('name','=',$name);
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        $model = $model->get();
        if ($model->isEmpty() == true) {
            return false;
        }
        return true;
    }

    public function addEvent(array $event)
    {
        if ($this->hasEvent($event['name']) == true) {
            return false;
        }
        $this->fill($event);
        $result = $this->save();
        return $result;
    }   

    public function disableExtensionEvents($extension_name) 
    {  
        $this->changeEventStatus(null,$extension_name,0);
    }

    public function enableExtensionEvents($extension_name) 
    {  
       $this->changeEventStatus(null,$extension_name,1);
    }

    public function enableEvent($event_name)
    {
        $this->changeEventStatus($event_name,null,1);
    }

    public function disableEvent($event_name)
    {
        $this->changeEventStatus($event_name,null,0);
    }

    private function changeEventStatus($event_name = null, $extension_name = null, $status) 
    {
        if ($event_name != null) {
            $this->where('name','=',$event_name);
        }
        if ($extension_name != null) {
            $this->where('extension_name','=',$extension_name);
        }
        $events = $this->get();
        foreach ($events as $event) {
            $event->status = Number::getInteger($status);
            $event->update();
        }
    }
}
