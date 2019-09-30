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
     * @param string $extension_name
     * @param integer $status
     * @return array
     */
    public function getExtensionSubscribers($extension_name, $status = null) 
    {           
        $model = $this->where('extension_name','=',$extension_name);
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        $model = $model->orderBy('priority')->get();
        return (is_object($model) == true) ? $model->toArray() : [];
    }

    /**
     * Get subscribers
     *
     * @param string $event_name
     * @param integer $status
     * @return array
     */
    public function getSubscribers($event_name, $status = null) 
    {           
        $model = $this->where('name','=',$event_name);
        if ($status != null) {
            $model = $model->where('status','=',$status);
        }
        $model = $model->orderBy('priority')->get();
        return (is_object($model) == true) ? $model->toArray() : [];           
    }

    /**
     * Return true if event have subscriber(s)
     *
     * @param string $event_name
     * @param string $extension_name
     * @return boolean
     */
    public function hasSubscriber($event_name, $extension_name)
    {
        $model = $this->getSubscribersQuery($event_name,$extension_name);
        $model = $model->get();
        return ($model->isEmpty() == true) ? false : true;           
    }

    /**
     * Return subscribers query builder
     *
     * @param string $event_name
     * @param string $extension_name
     * @return Builder
     */
    public function getSubscribersQuery($event_name, $extension_name)
    {
        $model = $this->where('name','=',$event_name);
        return $model->where('extension_name','=',$extension_name);       
    }

    /**
     * Delete extension subscribers.
     *
     * @param string $extension_name
     * @return bool
     */
    public function deleteExtensionSubscribers($extension_name)
    {
        if (empty($extension_name) == true) {
            return false;
        }
        $model = $this->where('extension_name','=',$extension_name);
        return (is_object($model) == true) ? $model->delete() : true;         
    }

    /**
     * Delete all subscribers for event
     *
     * @param string $extension_name
     * @param string $event_name
     * @return bool
     */
    public function deleteSubscribers($extension_name, $event_name)
    {
        $model = $this->getSubscribersQuery($event_name,$extension_name);
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
     * @param string $extension_name
     * @return bool
     */
    public function disableExtensionSubscribers($extension_name) 
    {  
        return $this->changeStatus(null,$extension_name,Status::$DISABLED);
    }

    /**
     * Enable extension subscribers.
     *
     * @param string $extension_name
     * @return bool
     */
    public function enableExtensionSubscribers($extension_name) 
    {  
       return $this->changeStatus(null,$extension_name,Status::$ACTIVE);
    }

    /**
     * Enable all subscribers for event.
     *
     * @param string $event_name
     * @return bool
     */
    public function enableSubscribers($event_name)
    {
        return $this->changeStatus($event_name,null,Status::$ACTIVE);
    }

    /**
     * Disable all subscribers for event.
     *
     * @param string $event_name
     * @return bool
     */
    public function disableSubscribers($event_name)
    {
        return $this->changeStatus($event_name,null,Status::$DISABLED);
    }

    /**
     * Change subscriber status
     *
     * @param string $event_name
     * @param string $extension_name
     * @param string $status
     * @return bool
     */
    private function changeStatus($event_name = null, $extension_name = null, $status) 
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
