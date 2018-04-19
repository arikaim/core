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

/**
 * Permissions database model
 */
class PermissionsList extends Model  
{
    protected $fillable = [
        'id',
        'parent_id',
        'name',
        'title',
        'description',
        'extension_name',
        'uuid'];
        
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
        $this->uuid = Utils::getUUID();

        return $this->save();
    }

    public function has($name)
    {
        $model = $this->where('name','=',$name)->first();
        if (is_object($model) == false) {
            return false;
        }
        return true;
    }
}
