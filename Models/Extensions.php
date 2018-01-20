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
use Arikaim\Core\Db\ToggleValue;

class Extensions extends Model  
{
    use UUIDAttribute,ToggleValue;

    protected $fillable = [
        'name',
        'title',
        'description',
        'short_description',
        'class',
        'uuid',
        'version',
        'status',
        'admin_link_title',
        'admin_link_icon',
        'admin_link_sub_title',
        'admin_link_component'];
    
    public $timestamps = false;
   
    public function isInstalled($extension_name)
    {
        $model = $this->where('name','=',$extension_name)->first();       
        if ( is_object($model) == true ) return true;
        return false;       
    }

    public function getStatus($extension_name)
    {
        $model = $this->where('name','=',$extension_name)->first();       
        if ( is_object($model) == false ) return 0;
        return $model->status;       
    }

}
