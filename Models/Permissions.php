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

use Arikaim\Core\Access\Access;
use Arikaim\Core\Utils\Utils;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Slug;

/**
 * Permissions database model
 */
class Permissions extends Model  
{
    use Uuid,
        Slug,
        Find;

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'name',
        'slug',
        'editable',
        'title',
        'description',
        'extension_name',
        'validator_class'
    ];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * Slug source column
     *
     * @var string
    */
    protected $slugSourceColumn = 'name';

    /**
     * Mutator (get) for title attribute.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return (empty($this->title) == true) ? $this->name : $this->title;
    }

    /**
     * Find permission model by id, uuid, slug, name
     *
     * @param mixed $value
     * @return Model|false
     */
    public function findPermission($value)
    {
        return $this->findByColumn($value,['id','uuid','slug','name']);
    }

    /**
     * Return true if permission item exist.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        $model = $this->where('name','=',$name)->first();

        return \is_object($model);           
    }

    /**
     * Create permission model
     *
     * @param string $name
     * @param string $title
     * @param string|null $description
     * @return Model|false
     */
    public function createPermission($name, $title = '', $description = null)
    {
        $model = $this->findByColumn($name,'name');
        if (\is_object($model) == true) {
            return false;
        }
       
        return $this->create([
            'name'        => $name,
            'title'       => $title,
            'slug'        => Utils::slug($title),
            'description' => $description,
            'editable'    => true
        ]);
    }

    /**
     * Get permission id 
     *
     * @param string $name  Name or Slug
     * @return integer|false
     */
    public function getId($name)
    {
        // find with slug
        $model = $this->where('slug','=',$name)->first();
        if (\is_object($model) == true) {
            return $model->id;
        }
        // find by name
        $model = $this->where('name','=',$name)->first();

        return (\is_object($model) == true) ? $model->id : false;    
    }

    /**
     * Get permisisons list query
     *
     * @return Builder
     */
    public function getListQuery()
    {
        return $this->where('name','<>',Access::CONTROL_PANEL)->orderBy('name');
    }
}
