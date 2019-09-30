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
 *  Relations (Many To Many) trait      
*/
trait Relations 
{           
    /**
     * Get relations target refernce attribute name 
     *
     * @return string
     */
    public function getRelationsTargetAttributeName()
    {
        return (isset($this->relation_target_attribute) == true) ? $this->relation_target_attribute : null;
    }

    /**
     * Get relations source refernce attribute name 
     *
     * @return string
     */
    public function getRelationsSourceAttributeName()
    {
        return (isset($this->relation_source_attribute) == true) ? $this->relation_source_attribute : null;
    }

    /**
     * Get single relation model
     *
     * @param integer $id
     * @return Model|false
     */
    public function relation($id)
    {
        $target_attribute = $this->getRelationsTargetAttributeName();
        $model = $this->getQuery()->where($target_attribute,'=',$id)->first();  

        return (is_object($model) == false) ? false : $model;
    }

    /**
     * Add relation
     *
     * @param [type] $id
     * @param array $data
     * 
     * @return Model|false
     */
    public function addRelation($target_id, $data = [])
    {        
        $model = $this->relation($target_id);

        if (is_object($model) == false) {
            $target_attribute = $this->getRelationsTargetAttributeName();
            $source_attribute = $this->getRelationsSourceAttributeName();

            $data[$target_attribute] = $target_id;     
            $data[$source_attribute] = $this->$source_attribute;

            return $this->create($data);
        }
    
        return false;
    }

    /**
     * Add relations 
     *
     * @param array $items
     * 
     * @return void
     */
    public function addRelations(array $items)
    {        
        foreach ($items as $key => $id) {
            $this->addRelation($id);
        }   
    }

    /**
     * Return true if relation to target id extist
     *
     * @param integer $target_id
     * @return boolean
     */
    public function hasRelation($target_id)
    {
        return is_object($this->relation($target_id));
    }

    /**
     * Delete translation
     *
     * @param integer $id
     * @param string $language
     * @return boolean
     */
    public function removeRelation($id)
    {        
        $model = $this->relation($id);
        return (is_object($model) == true) ? $model->delete() : false;
    }

    /**
     * Delete all relations
     *
     * @return boolean
     */
    public function removeRelations()
    {
        return $this->delete();      
    }
}
