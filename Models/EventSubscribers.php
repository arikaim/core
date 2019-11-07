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

use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Traits\Db\Status;

/**
 * EventSubscribers database model
 */
class EventSubscribers extends Model  
{
    use Uuid,
        Find,
        Status;

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'handler_class',
        'handler_method',
        'extension_name',
        'priority'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Get all extension subscribers.
     *
     * @param string $extension
     * @param integer $status
     * @param string|null $eventName
     * @return array
     */
    public function getExtensionSubscribers($extension, $status = null, $eventName = null) 
    {           
        $model = $this->where('extension_name','=',$extension);
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        if ($eventName != null) {
            $model = $model->where('name','=',$eventName);
        }

        $model = $model->orderBy('priority')->get();
        return (is_object($model) == true) ? $model->toArray() : [];
    }

    /**
     * Get subscribers
     *
     * @param string $eventName
     * @param integer $status
     * @return array
     */
    public function getSubscribers($eventName, $status = null) 
    {           
        $model = $this->where('name','=',$eventName);
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        $model = $model->orderBy('priority')->get();
        return (is_object($model) == true) ? $model->toArray() : [];           
    }

    /**
     * Return true if event have subscriber(s)
     *
     * @param string $eventName
     * @param string $extension
     * @return boolean
     */
    public function hasSubscriber($eventName, $extension)
    {
        $model = $this->getSubscribersQuery($eventName,$extension);
        $model = $model->get();
        return ($model->isEmpty() == true) ? false : true;           
    }

    /**
     * Return subscribers query builder
     *
     * @param string $eventName
     * @param string $extension
     * @return Builder
     */
    public function getSubscribersQuery($eventName, $extension)
    {
        $model = $this->where('name','=',$eventName);
        return $model->where('extension_name','=',$extension);       
    }

    /**
     * Delete extension subscribers.
     *
     * @param string $extension
     * @return bool
     */
    public function deleteExtensionSubscribers($extension)
    {
        if (empty($extension) == true) {
            return false;
        }
        $model = $this->where('extension_name','=',$extension);
        return (is_object($model) == true) ? $model->delete() : true;         
    }

    /**
     * Delete all subscribers for event
     *
     * @param string $extension
     * @param string $eventName
     * @return bool
     */
    public function deleteSubscribers($extension, $eventName)
    {
        $model = $this->getSubscribersQuery($eventName,$extension);
        return (is_object($model) == false) ? true : $model->delete();         
    }

    /**
     * Add subscriber
     *
     * @param array $subscriber
     * @return bool
     */
    public function add(array $subscriber)
    {
        if (empty($subscriber['name']) == true) {
            return false;
        }
        if ($this->hasSubscriber($subscriber['name'],$subscriber['extension_name']) == true) {
            return false;
        }
        $subscriber['priority'] = (empty($subscriber['priority']) == true) ? 0 : $subscriber['priority']; 

        return $this->create($subscriber);       
    }   

    /**
     * Disable extension subscribers.
     *
     * @param string $extension
     * @return bool
     */
    public function disableExtensionSubscribers($extension) 
    {  
        return $this->changeStatus(null,$extension,Status::$DISABLED);
    }

    /**
     * Enable extension subscribers.
     *
     * @param string $extension
     * @return bool
     */
    public function enableExtensionSubscribers($extension) 
    {  
       return $this->changeStatus(null,$extension,Status::$ACTIVE);
    }

    /**
     * Enable all subscribers for event.
     *
     * @param string $eventName
     * @return bool
     */
    public function enableSubscribers($eventName)
    {
        return $this->changeStatus($eventName,null,Status::$ACTIVE);
    }

    /**
     * Disable all subscribers for event.
     *
     * @param string $eventName
     * @return bool
     */
    public function disableSubscribers($eventName)
    {
        return $this->changeStatus($eventName,null,Status::$DISABLED);
    }

    /**
     * Change subscriber status
     *
     * @param string $eventName
     * @param string $extension
     * @param string $status
     * @return bool
     */
    private function changeStatus($eventName = null, $extension = null, $status) 
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
