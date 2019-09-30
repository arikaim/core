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
 * Manage models with parent - child relations.
 *  Change parrent id column name in model:
 *       protected $parent_attribute = "column name";
*/
trait Tree 
{       
    /**
     * Gte model tree
     *
     * @param Moldel $model
     * @return void
     */
    public function getModelPath($model)
    {
        $result = [];
        array_unshift($result,$model->toArray());
      
        while ($model != false) {
            $parent_id = $model->attributes[$this->getParentAttributeName()];
            $model = parent::where('id','=',$parent_id)->first();
            if (is_object($model) == true) {
                array_unshift($result,$model->toArray());
            }
        }
        return $result;
    }

    /**
     * Get parent id attribute name default: parent_id
     *
     * @return string
     */
    public function getParentAttributeName()
    {
        return (isset($this->parent_attribute) == true) ? $this->parent_attribute : 'parent_id';
    }

    /**
     * Gte model tree for current model
     *
     * @return void
     */
    public function getTreePath()
    {      
        return $this->getModelPath($this);
    }
}
