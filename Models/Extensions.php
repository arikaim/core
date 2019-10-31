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
use Arikaim\Core\Traits\Db\Position;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\Find;

/**
 * Extensions database model
 */
class Extensions extends Model  
{
    use Uuid,
        Find,
        Position,
        Status;

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'description',
        'short_description',
        'class',
        'type',
        'position',
        'version',       
        'admin_menu',
        'console_commands',
        'license_key'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
    
    /**
     * Mutator (set) for admin_menu attribute.
     *
     * @param array|null $value
     * @return void
     */
    public function setAdminMenuAttribute($value)
    {
        $value = (is_array($value) == true) ? $value : [];         
        $this->attributes['admin_menu'] = json_encode($value);
    }

    /**
     * Mutator (set) for console_commands attribute.
     *
     * @param array:null $value
     * @return void
     */
    public function setConsoleCommandsAttribute($value)
    {
        $value = (is_array($value) == true) ? $value : [];    
        $this->attributes['console_commands'] = json_encode($value);
    }

    /**
     * Mutator (get) for console_commands attribute.
     *
     * @return array
     */
    public function getConsoleCommandsAttribute()
    {
        return json_decode($this->attributes['console_commands'],true);
    }

    /**
     * Mutator (get) for admin_menu attribute.
     *
     * @return string|null
     */
    public function getAdminMenuAttribute()
    {
        return json_decode($this->attributes['admin_menu'],true);
    }

    /**
     * Return true if extension record exist.
     *
     * @param string $extension
     * @return boolean
     */
    public function isInstalled($extension)
    {
        $model = $this->where('name','=',$extension)->first();       
        return (is_object($model) == true) ? true : false;   
    }

    /**
     * Return extension status (0 - disabled, 1 - enabled)
     *
     * @param string $extension
     * @return integer
     */
    public function getStatus($extension)
    {
        $model = $this->where('name','=',$extension)->first();       
        return (is_object($model) == false) ? 0 : $model->status;            
    }

    /**
     * Disable extension
     *
     * @param string $extension
     * @return bool
     */
    public function disable($extension)
    {        
        $model = $this->findByColumn($extension,'name');       
        return (is_object($model) == true) ? $model->setStatus(Status::$DISABLED) : false;     
    }

    /**
     * Enable extension
     *
     * @param string $extension
     * @return bool
     */
    public function enable($extension)
    {
        $model = $this->findByColumn($extension,'name');
        return (is_object($model) == true) ? $model->setStatus(Status::$ACTIVE) : false;     
    }
}
