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
 *  Polymorphic Relations (Many To Many) trait      
*/
trait PolymorphicRelations 
{           
    /**
     * Get relation model class
     *
     * @return string
     */
    public function getRelationModelClass()
    {
        return (isset($this->relation_model_class) == true) ? $this->relation_model_class : null;
    }

    /**
     * Get relation attribute name
     *
     * @return string
     */
    public function getRelationAttributeName()
    {
        return (isset($this->relation_attribute_name) == true) ? $this->relation_attribute_name : null;
    }

    /**
     * Morphed model
     *
     * @return void
     */
    public function related()
    {
        return $this->morphTo('relation');
    }

    /**
     * Relations
     *
     * @return void
     */
    public function relations()
    {    
        return $this->morphToMany($this->getRelationModelClass(),'relation');
    }

    /**
     * Get relations
     *
     * @param integer $id
     * @param string|null $type
     * @return Model
     */
    public function getRows($id, $type = null) 
    {
        $relation_field = $this->getRelationAttributeName();
        $model = (empty($id) == false) ? $this->where($relation_field,'=',$id) : $this;

        if (empty($type) == false) {
            $model = $model->where('relation_type','=',$type);
        }
        return $model;
    }

    /**
     *  Delete relation
     *
     * @param integer|string|null $id
     * @return boolean
     */
    public function deleteRelation($id)
    {
        $model = (empty($id) == true) ? $this->findByid($id) : $this;

        return (is_obejct($model) == true) ? $model->delete() : false;
    }

    /**
     * Delete relations
     *
     * @param integer $id
     * @param string|null $type
     * @param integer|null $type_id
     * @return void
     */
    public function deleteRelations($id, $type = null, $type_id = null)
    {
        $relation_field = $this->getRelationAttributeName();
        $model = $this->where($relation_field,'=',$id);

        if (empty($type) == false) {
            $model = $model->where('relation_type','=',$type);
        }
        if (empty($type_id) == false) {
            $model = $model->where('relation_id','=',$type_id);
        }
    
        return $model->delete();
    }

    /**
     * Save relation
     *
     * @param integer $id
     * @param string $type
     * @param integer $type_id
     * @return void
     */
    public function saveRelation($id, $type, $type_id)
    {
        $relation_field = $this->getRelationAttributeName();
        $data = [
            $relation_field => $id,
            'relation_id'   => $type_id,
            'relation_type' => "$type",
        ];       
        return ($this->hasRelation($id,$type,$type_id) == false) ? $this->create($data) : false;       
    }

    /**
     * Return true if relation exist
     *
     * @param integer $id
     * @param string $type
     * @param integer $type_id
     * @return boolean
     */
    public function hasRelation($id, $type, $type_id)
    {
        $relation_field = $this->getRelationAttributeName();
        $model = $this
            ->where($relation_field,'=',$id)
            ->where('relation_type','=',$type)
            ->where('relation_id','=',$type_id)->first();
        
        return is_object($model);
    }

    /**
     * Save relations
     *
     * @param integer $id
     * @param string $type
     * @param integer $type_id
     * @return array
     */
    public function saveRelations(array $items, $type, $type_id)
    {
        $added = [];
        foreach ($items as $item) {
            $result = $this->saveRelation($item,$type,$type_id);
            if ($result !== false) {
               $added[] = $item;
            }
        }

        return $added;
    }
}
