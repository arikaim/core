<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\Find;

/**
 * Events database model
 */
class Events extends Model  
{
    use Uuid,
        Status,
        Find;
    
    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'extension_name',
        'description'
    ];
    
    /**
     * Timestamps disabled
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Return extension events list.
     *
     * @param string $extension_name
     * @return array
     */
    public function getEvents($extension_name)
    {
        $model = $this->where('extension_name','=',$extension_name)->get();
        return (is_object($model) == true) ? $model->toArray() : [];        
    }

    /**
     * Deleet event
     *
     * @param string $name
     * @return bool
     */
    public function deleteEvent($name) 
    {           
        $model = $this->where('name','=',$name);
        return ($model->isEmpty() == false) ? $model->delete() : true;           
    }

    /**
     * Get event subscribers
     *
     * @return object
     */
    public function subscribers()
    {
        $model = DbModel::create('EventSubscribers');
        $items = $model->where('name','=',$this->name)->get();
        return (is_object($model) == true) ? $items : $model;
    }

    /**
     * Delete extensions event.
     *
     * @param string $extension_name
     * @return bool
     */
    public function deleteEvents($extension_name)
    {
        $model = $this->where('extension_name','=',$extension_name);
        return (is_object($model) == true) ? $model->delete() : true;       
    }

    /**
     * Return true if event exist
     *
     * @param string $name
     * @param integer $status
     * @return boolean
     */
    public function hasEvent($name, $status = null)
    {
        $model = $this->where('name','=',$name);
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        $model = $model->get();
        return ($model->isEmpty() == true) ? false : true;        
    }

    /**
     * Add event.
     *
     * @param array $event
     * @return bool
     */
    public function addEvent(array $event)
    {
        return ($this->hasEvent($event['name']) == true) ? false : $this->create($event);          
    }   

    /**
     * Disable all extension events. 
     *
     * @param string $extension_name
     * @return bool
     */
    public function disableExtensionEvents($extension_name) 
    {  
        return $this->changeEventStatus(null,$extension_name,Status::$DISABLED);
    }

    /**
     * Enable all extension events.
     *
     * @param string $extension_name
     * @return bool
     */
    public function enableExtensionEvents($extension_name) 
    {  
       return $this->changeEventStatus(null,$extension_name,Status::$ACTIVE);
    }

    /**
     * Enable event
     *
     * @param string $event_name
     * @return bool
     */
    public function enableEvent($event_name)
    {
        return $this->changeEventStatus($event_name,null,Status::$ACTIVE);
    }

    /**
     * Disable event
     *
     * @param string $event_name
     * @return bool
     */
    public function disableEvent($event_name)
    {
        return $this->changeEventStatus($event_name,null,Status::$DISABLED);
    }

    /**
     * Change event status
     *
     * @param string $event_name
     * @param string $extension_name
     * @param integer $status
     * @return bool
     */
    private function changeEventStatus($event_name = null, $extension_name = null, $status) 
    {
        if ($event_name != null) {
            $this->where('name','=',$event_name);
        }
        if ($extension_name != null) {
            $this->where('extension_name','=',$extension_name);
        }
        return $this->update(['status' => $status]);       
    }
}
