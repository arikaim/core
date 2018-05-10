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
use Arikaim\Core\Db\UUIDAttribute;
use Arikaim\Core\Db\ToggleValue;

/**
 * Extensions database model
 */
class Extensions extends Model  
{
    const USER = 0;
    const SYSTEM = 1;
    const TYPE_NAME = ['user','system'];

    use UUIDAttribute,ToggleValue;

    protected $fillable = [
        'name',
        'title',
        'description',
        'short_description',
        'class',
        'type',
        'uuid',
        'version',
        'status',
        'admin_link_position',
        'admin_link_title',
        'admin_link_icon',
        'admin_link_sub_title',
        'admin_link_component',
        'license_key'];
    
    public $timestamps = false;
   
    public function isInstalled($extension_name)
    {
        $model = $this->where('name','=',$extension_name)->first();       
        return (is_object($model) == true) ? true : false;   
    }

    public function getStatus($extension_name)
    {
        $model = $this->where('name','=',$extension_name)->first();       
        return (is_object($model) == false) ? 0 : $model->status;            
    }

    public function getConstant($constant_name)
    {        
        return constant("Self::$constant_name");
    }

    public function getTypeId($type_name)
    {
        $key = array_search($type_name,Self::TYPE_NAME);
        return $key;
    }
}
