<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Interfaces\Events\EventRegistryInterface;
use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Core\Utils\Uuid as UuidFactory;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\Find;

/**
 * Events database model
 */
class Events extends Model implements EventRegistryInterface
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
        'uuid',
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
     * Deleet event
     *
     * @param string $name
     * @return bool
     */
    public function deleteEvent(string $name): bool 
    {           
        $model = $this->where('name','=',$name);

        return ($model->isEmpty() == false) ? (bool)$model->delete() : true;           
    }

    /**
     * Get event subscribers
     *
     * @param string|null $name
     * @return object
     */
    public function subscribers(?string $name = null)
    {
        $name = $name ?? $this->name;

        $model = DbModel::create('EventSubscribers');
        $items = $model->where('name','=',$name)->orWhere('name','=','*')->get();
        
        return (\is_object($model) == true) ? $items : $model;
    }

    /**
     * Delete events.
     *
     * @param array $filter
     * @return bool
     */
    public function deleteEvents(array $filter): bool
    {
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);
        }

        return (\is_object($model) == true) ? (bool)$model->delete() : false;             
    }

    /**
     * Return true if event exist
     *
     * @param string $name
     * @return boolean
     */
    public function hasEvent(string $name): bool
    {
        return ($this->where('name','=',$name)->first() !== null);      
    }

    /**
     * Add or update event to events db table.
     *
     * @param string $name
     * @param string $title
     * @param string|null $extension
     * @param string|null $description
     * @return bool
     */
    public function registerEvent(string $name, string $title, ?string $extension = null, ?string $description = null): bool
    {
        $info = [
            'uuid'           => UuidFactory::create(),
            'name'           => $name,
            'extension_name' => $extension,
            'title'          => $title,
            'description'    => $description
        ];

        if ($this->hasEvent($name) == true) {
            $this->update($info);
            return true;
        } 
      
        $model = $this->create($info);
         
        return \is_object($model);
    }   

    /**
     * Get events list
     *
     * @param array $filter
     * @return array
     */
    public function getEvents(array $filter = []): array
    {
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);
        }
        $model = $model->get();

        return (\is_object($model) == true) ? $model->toArray() : [];
    }

    /**
     * Set events status
     *
     * @param array $filter
     * @param integer $status
     * @return boolean
     */
    public function setEventsStatus(array $filter = [], int $status): bool
    {
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);
        }

        return (\is_object($model) == true) ? (bool)$model->update(['status' => $status]) : false;
    }
}
