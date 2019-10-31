<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
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
     * Db table name
     *
     * @var string
     */
    protected $table = 'events';

    /**
     * Timestamps disabled
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Return extension events list.
     *
     * @param string $extension
     * @return array
     */
    public function getEvents($extension)
    {
        $model = $this->where('extension_name','=',$extension)->get();
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
     * @param string $extension
     * @return bool
     */
    public function deleteEvents($extension)
    {
        $model = $this->where('extension_name','=',$extension);

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

        return !($model->isEmpty() == true);    
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
     * @param string $extension
     * @return bool
     */
    public function disableExtensionEvents($extension) 
    {  
        return $this->changeEventStatus(null,$extension,Status::$DISABLED);
    }

    /**
     * Enable all extension events.
     *
     * @param string $extension
     * @return bool
     */
    public function enableExtensionEvents($extension) 
    {  
       return $this->changeEventStatus(null,$extension,Status::$ACTIVE);
    }

    /**
     * Enable event
     *
     * @param string $eventName
     * @return bool
     */
    public function enableEvent($eventName)
    {
        return $this->changeEventStatus($eventName,null,Status::$ACTIVE);
    }

    /**
     * Disable event
     *
     * @param string $eventName
     * @return bool
     */
    public function disableEvent($eventName)
    {
        return $this->changeEventStatus($eventName,null,Status::$DISABLED);
    }

    /**
     * Change event status
     *
     * @param string $eventName
     * @param string $extension
     * @param integer $status
     * @return bool
     */
    private function changeEventStatus($eventName = null, $extension = null, $status) 
    {
        if ($eventName != null) {
            $this->where('name','=',$eventName);
        }
        if ($extension != null) {
            $this->where('extension_name','=',$extension);
        }
        return $this->update(['status' => $status]);       
    }
}
