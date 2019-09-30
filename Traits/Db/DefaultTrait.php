<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

/**
 * Default column trait
*/
trait DefaultTrait 
{        
    /**
     * Mutator (get) for default attribute.
     *
     * @return array
     */
    public function getDefaultAttribute()
    {       
        return (empty($this->attributes['default']) == true) ? false : true;
    }

    /**
     * Set model as default
     *
     * @param integer|string|null $id
     * @return bool
     */
    public function setDefault($id = null)
    {
        $id = (empty($id) == true) ? $this->id : $id;
        $models = $this->where('id','<>',$id);    
        $models->update(['default' => null]);

        $model = $this->findById($id);
        $model->default = 1;

        return $model->save();               
    }

    /**
     * Get default model
     *
     * @return Model|false
     */
    public function getDefault()
    {
        $model = $this->where('default','=','1')->first();
        return (is_object($model) == true) ? $model : false; 
    }
}
