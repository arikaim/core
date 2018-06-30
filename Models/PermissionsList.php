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
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Uuid;


/**
 * Permissions database model
 */
class PermissionsList extends Model  
{
    use Uuid;

    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'description',
        'extension_name'];
        
    public $timestamps = false;
    protected $table = 'permissions_list';

    public function add($name, $extension_name = null, $title = null, $description = null, $parent_id = 0)
    {
        if ($this->has($name) == true) {
            return false;
        }
        $this->name = $name;
        $this->extension_name = $extension_name;
        $this->title = $title;
        $this->parent_id = $parent_id;
        $this->description = $description;        
        return $this->create();
    }

    public function has($name)
    {
        $model = $this->where('name','=',$name)->first();
        return (is_object($model) == false) ? false : true;            
    }
}
