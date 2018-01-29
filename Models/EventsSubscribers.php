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

class EventsSubscribers extends Model  
{
    use UUIDAttribute;

    const DISABLED = 0;
    const ACTIVE = 1;

    protected $fillable = ['name','handler_class','extension_name','uuid','priority'];
    public $timestamps = false;

    public function getSubscribers($event_name, $status = null) 
    {           
        $model = $this->where('name','=',$event_name);
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        $model = $model->orderBy('priority')->get();
        if (is_object($model) == true) {
            return $model->toArray();
        } 
        return [];
    }

    public function hasSubscriber($event_name, $extension_name)
    {
        $model = $this->where('name','=',$event_name);
        $model = $model->where('extension_name','=',$extension_name)->get();
        if ($model->isEmpty() == true) {
            return false;
        }
        return true;
    }

    public function getSubscriber($event_name, $extension_name)
    {
        $model = $this->where('name','=',$event_name);
        $model = $model->where('extension_name','=',$extension_name);
        return $model;
    }

    public function deleteSubscribers($extension_name)
    {
        if (empty($extension_name) == true) {
            return false;
        }
        $model = $this->where('extension_name','=',$extension_name);
        if (is_object($model) == true) {
            return $model->delete();
        }
        return false;
    }

    public function deleteSubscriber($event_name, $extension_name)
    {
        $model = $this->getSubscriber($event_name,$extension_name);
        if (is_object($model) == false) {
            return false;
        }
        return $model->delete();
    }

    public function add(array $subscriber)
    {
        if (empty($subscriber['name']) == true) {
            return false;
        }
        if ($this->hasSubscriber($subscriber['name'],$subscriber['extension_name']) == true) {
            return false;
        }
        if (empty($subscriber['priority']) == true) {
            $subscriber['priority'] = 0;
        }
        $this->fill($subscriber);
        $result = $this->save();
        return $result;
    }   

    public function disableExtensionSubscribers($extension_name) 
    {  
        $this->changeStatus(null,$extension_name,0);
    }

    public function enableExtensionSubscribers($extension_name) 
    {  
       $this->changeStatus(null,$extension_name,1);
    }

    public function enable($event_name)
    {
        $this->changeEventStatus($event_name,null,1);
    }

    public function disable($event_name)
    {
        $this->changeStatus($event_name,null,0);
    }

    private function changeStatus($event_name = null, $extension_name = null, $status) 
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
