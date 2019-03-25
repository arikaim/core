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
use Arikaim\Core\Traits\Db\Uuid;;
use Arikaim\Core\Traits\Db\Find;;
use Arikaim\Core\Traits\Db\Tree;;


/**
 * Permissions database model
 */
class PermissionsList extends Model  
{
    use Uuid,
        Find,
        Tree;

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
        $info['name'] = $name;
        $info['extension_name'] = $extension_name;
        $info['title'] = $title;
        $info['parent_id'] = $parent_id;
        $info['description'] = $description;        
        return $this->create($info);
    }

    public function has($name)
    {
        $model = $this->where('name','=',$name)->first();
        return (is_object($model) == false) ? false : true;            
    }
}
